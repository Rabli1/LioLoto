<?php

namespace Tests\Feature;

use App\Mail\AccountConfirmationMail;
use App\Models\GameRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('t', 32))]);
    }

    private function signIn(User $user): static
    {
        return $this->withSession(['user_id' => $user->id, 'credential_version' => hash('sha256', $user->password)]);
    }

    public function test_admin_mutations_require_an_authenticated_administrator(): void
    {
        $target = User::factory()->create();
        foreach (['fixPoints', 'toggleBan', 'toggleAdmin', 'deleteUser', 'killPoker'] as $action) {
            $this->postJson('/admin/'.$action, ['userId' => $target->id, 'points' => 900000, 'action' => 'admin'])->assertUnauthorized();
        }
        $this->signIn($target)->postJson('/admin/toggleAdmin', ['userId' => $target->id, 'action' => 'admin'])->assertForbidden();
        $this->assertFalse($target->fresh()->admin);
        $this->assertSame(1000, $target->fresh()->points);
    }

    public function test_admin_privileges_and_bans_are_rechecked_from_the_database(): void
    {
        $admin = User::factory()->create(['admin' => true]);
        $target = User::factory()->create();
        $this->signIn($admin);
        $admin->forceFill(['admin' => false])->save();
        $this->postJson('/admin/fixPoints', ['userId' => $target->id, 'points' => 1])->assertForbidden();
        $admin->forceFill(['banned' => true])->save();
        $this->postJson('/game/rounds', ['game' => 'coinflip', 'stake' => 10])->assertUnauthorized();
        $this->assertSame(1000, $target->fresh()->points);
    }

    public function test_admin_can_change_points_but_cannot_remove_their_own_account(): void
    {
        $admin = User::factory()->create(['admin' => true]);
        $target = User::factory()->create();
        $this->signIn($admin)->post('/admin/fixPoints', ['userId' => $target->id, 'points' => 500])->assertRedirect('/admin/dashboard');
        $this->assertSame(500, $target->fresh()->points);
        $this->postJson('/admin/deleteUser', ['userId' => $admin->id])->assertUnprocessable();
        $this->postJson('/admin/toggleAdmin', ['userId' => $admin->id, 'action' => 'user'])->assertUnprocessable();
        $this->assertNotNull($admin->fresh());
        $this->assertTrue($admin->fresh()->admin);
    }

    public function test_arbitrary_balance_and_legacy_poker_requests_cannot_mutate_users(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->signIn($user);
        foreach (['balance', 'initRound', 'placeBet', 'quitPoker', 'updateEtag', 'joinPoker'] as $route) {
            $this->postJson('/game/'.$route, ['balance' => 999999999, 'amount' => -100000, 'playerId' => $other->id, 'force' => true])->assertStatus(410);
        }
        $this->assertSame(1000, $user->fresh()->points);
        $this->assertSame(1000, $other->fresh()->points);
    }

    public function test_wager_validation_and_insufficient_balance_are_enforced(): void
    {
        $user = User::factory()->create(['points' => 50]);
        $this->signIn($user);
        foreach ([-1, 0, 10001, 1.2, 'NaN'] as $stake) {
            $this->postJson('/game/rounds', ['game' => 'mines', 'stake' => $stake])->assertUnprocessable();
        }
        $this->postJson('/game/rounds', ['game' => 'mines', 'stake' => 51])->assertUnprocessable();
        $this->assertSame(50, $user->fresh()->points);
        $this->assertDatabaseCount('game_rounds', 0);
    }

    public function test_instant_games_settle_only_the_server_result(): void
    {
        $user = User::factory()->create();
        $this->signIn($user);
        foreach (['coinflip', 'roulette', 'plinko', 'slot-machine'] as $game) {
            $before = $user->fresh()->points;
            $response = $this->postJson('/game/rounds', [
                'game' => $game, 'stake' => 10, 'balance' => 999999999, 'payout' => 999999,
            ])->assertOk();
            $payout = $response->json('round.payout');
            $this->assertContains($response->json('round.status'), ['won', 'lost']);
            $this->assertSame($before - 10 + $payout, $user->fresh()->points);
            $this->postJson('/game/rounds/'.$response->json('round.id').'/actions', ['action' => 'cashout'])->assertConflict();
        }
    }

    public function test_chicken_cannot_cash_out_without_a_step_and_can_be_abandoned(): void
    {
        $user = User::factory()->create();
        $id = $this->signIn($user)->postJson('/game/rounds', ['game' => 'chicken-road', 'stake' => 25])->json('round.id');
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'cashout'])->assertUnprocessable();
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'forfeit'])
            ->assertOk()->assertJsonPath('round.status', 'lost')->assertJsonPath('round.payout', 0);
        $this->assertSame(975, $user->fresh()->points);
    }

    public function test_hidden_mines_and_round_ownership_are_protected(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $response = $this->signIn($user)->postJson('/game/rounds', ['game' => 'mines', 'stake' => 100, 'options' => ['mines' => 3]])->assertOk();
        $response->assertJsonMissingPath('round.minesPositions')->assertJsonMissingPath('round.deck');
        $id = $response->json('round.id');
        $this->postJson('/game/rounds', ['game' => 'coinflip', 'stake' => 100])->assertConflict();
        $this->signIn($other)->postJson('/game/rounds/'.$id.'/actions', ['action' => 'cashout'])->assertNotFound();
        $this->assertSame(900, $user->fresh()->points);
        $this->assertSame(1000, $other->fresh()->points);
        $this->assertSame('active', GameRound::find($id)->status);
    }

    public function test_mines_are_revealed_server_side_and_a_settlement_cannot_be_replayed(): void
    {
        $user = User::factory()->create();
        $id = $this->signIn($user)->postJson('/game/rounds', ['game' => 'mines', 'stake' => 100])->json('round.id');
        $round = GameRound::findOrFail($id);
        $safe = array_values(array_diff(range(0, 24), $round->state['minesPositions']))[0];
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'cashout'])->assertUnprocessable();
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'reveal', 'cell' => 25])->assertUnprocessable();
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'reveal', 'cell' => $safe])->assertOk();
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'reveal', 'cell' => $safe])->assertUnprocessable();
        $response = $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'cashout', 'payout' => 9999999, 'multiplier' => 99999])->assertOk();
        $balance = $user->fresh()->points;
        $this->assertSame(900 + $response->json('round.payout'), $balance);
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'cashout'])->assertConflict();
        $this->assertSame($balance, $user->fresh()->points);
    }

    public function test_a_mine_ends_a_round_without_a_payout(): void
    {
        $user = User::factory()->create();
        $id = $this->signIn($user)->postJson('/game/rounds', ['game' => 'mines', 'stake' => 100])->json('round.id');
        $mine = GameRound::findOrFail($id)->state['minesPositions'][0];
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'reveal', 'cell' => $mine])
            ->assertOk()->assertJsonPath('round.status', 'lost')->assertJsonPath('round.payout', 0);
        $this->assertSame(900, $user->fresh()->points);
    }

    public function test_crash_cashout_uses_the_server_clock_and_hidden_outcome(): void
    {
        $user = User::factory()->create();
        $response = $this->signIn($user)->postJson('/game/rounds', ['game' => 'crash', 'stake' => 100])->assertOk();
        $response->assertJsonMissingPath('round.crashAt');
        $id = $response->json('round.id');
        $round = GameRound::findOrFail($id);
        $round->state = ['startedAt' => now()->valueOf(), 'crashAt' => 1.10];
        $round->save();
        $this->travel(2)->seconds();
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'cashout', 'multiplier' => 1000000])
            ->assertOk()->assertJsonPath('round.status', 'lost')->assertJsonPath('round.payout', 0);
        $this->assertSame(900, $user->fresh()->points);
    }

    public function test_blackjack_hides_the_dealer_and_deck_until_server_settlement(): void
    {
        $user = User::factory()->create();
        $response = $this->signIn($user)->postJson('/game/rounds', ['game' => 'blackjack', 'stake' => 100])->assertOk();
        $response->assertJsonMissingPath('round.deck');
        $id = $response->json('round.id');
        $round = GameRound::findOrFail($id);
        // A deterministic state exercises the ace rule and dealer draw without mocking the payout logic.
        $round->status = 'active';
        $round->state = [
            'playerCards' => [['rank' => 'A', 'suit' => 'C'], ['rank' => '9', 'suit' => 'D']],
            'dealerCards' => [['rank' => '10', 'suit' => 'S'], ['rank' => '6', 'suit' => 'H']],
            'deck' => [['rank' => 'K', 'suit' => 'D']],
        ];
        $round->save();
        $before = $user->fresh()->points;
        $this->getJson('/game/rounds/current')->assertOk()->assertJsonPath('round.dealerCards.1.hidden', true);
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'stand'])
            ->assertOk()->assertJsonPath('round.payout', 200)->assertJsonPath('round.dealerTotal', 26);
        $this->assertSame($before + 200, $user->fresh()->points);
    }

    public function test_poker_draw_uses_server_cards_and_validates_hold_indexes(): void
    {
        $user = User::factory()->create();
        $response = $this->signIn($user)->postJson('/game/rounds', ['game' => 'poker', 'stake' => 10])->assertOk();
        $response->assertJsonMissingPath('round.deck');
        $id = $response->json('round.id');
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'draw', 'hold' => [0, 0]])->assertUnprocessable();
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'draw', 'hold' => [5]])->assertUnprocessable();
        $round = GameRound::findOrFail($id);
        $state = $round->state;
        $state['cards'] = array_map(fn ($rank) => ['rank' => $rank, 'suit' => 'S'], ['10', 'J', 'Q', 'K', 'A']);
        $round->state = $state;
        $round->save();
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'draw', 'hold' => [0, 1, 2, 3, 4], 'cards' => []])
            ->assertOk()->assertJsonPath('round.payout', 2500);
        $this->assertSame(3490, $user->fresh()->points);
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'draw'])->assertConflict();
    }

    public function test_liotodle_is_once_daily_and_awards_points_only_for_server_verified_guesses(): void
    {
        $user = User::factory()->create();
        $response = $this->signIn($user)->postJson('/game/rounds', ['game' => 'liotodle', 'stake' => 0])->assertOk();
        $response->assertJsonMissingPath('round.secret')->assertJsonMissingPath('round.answer');
        $id = $response->json('round.id');
        $answer = GameRound::findOrFail($id)->state['secret'];
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'guess', 'word' => 'ZZZZZ'])->assertUnprocessable();
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'guess', 'word' => $answer])
            ->assertOk()->assertJsonPath('round.status', 'won')->assertJsonPath('round.payout', 300);
        $this->postJson('/game/rounds', ['game' => 'liotodle', 'stake' => 0])->assertConflict();
        $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'guess', 'word' => $answer])->assertConflict();
        $this->assertSame(1300, $user->fresh()->points);
    }

    public function test_registration_validates_duplicates_and_cannot_inject_privileges(): void
    {
        Mail::fake();
        $this->postJson('/user/addUser', ['username' => 'player', 'email' => 'bad', 'password' => 'short', 'confirmPassword' => 'different'])->assertUnprocessable();
        $input = ['username' => 'player', 'email' => 'PLAYER@example.com', 'password' => 'long-valid-password12', 'confirmPassword' => 'long-valid-password12', 'admin' => true, 'points' => 999999];
        $this->post('/user/addUser', $input)->assertRedirect('/user/connection');
        $user = User::where('name', 'player')->firstOrFail();
        $this->assertFalse($user->admin);
        $this->assertFalse($user->confirmed);
        $this->assertSame(1000, $user->points);
        $this->assertSame('player@example.com', $user->email);
        $this->assertTrue(Hash::check($input['password'], $user->password));
        $this->assertSame(64, strlen($user->confirmation_token));
        $this->postJson('/user/addUser', $input)->assertUnprocessable();
        Mail::assertSent(AccountConfirmationMail::class);
    }

    public function test_confirmation_requires_a_valid_signed_expiring_token_and_is_single_use(): void
    {
        $token = str_repeat('a', 64);
        $user = User::factory()->create(['confirmed' => false, 'confirmation_token' => hash('sha256', $token)]);
        $this->get('/user/confirm/'.$token)->assertForbidden();
        $expired = URL::temporarySignedRoute('user.confirm', now()->subMinute(), ['token' => $token]);
        $this->get($expired)->assertForbidden();
        $url = URL::temporarySignedRoute('user.confirm', now()->addHour(), ['token' => $token]);
        $this->get($url)->assertRedirect('/user/connection');
        $this->assertTrue($user->fresh()->confirmed);
        $this->assertNull($user->fresh()->confirmation_token);
        $this->get($url)->assertSessionHas('error');
    }

    public function test_login_uses_opaque_remember_tokens_and_excludes_passwords_from_session(): void
    {
        $user = User::factory()->create(['name' => 'player', 'password' => 'long-valid-password12']);
        $this->post('/user/connection', ['username' => 'player', 'password' => 'long-valid-password12', 'remember' => 'on'])
            ->assertRedirect('/')->assertSessionHas('user_id', $user->id);
        $sessionUser = session('user');
        $this->assertFalse(property_exists($sessionUser, 'password'));
        $this->assertNotEmpty($user->fresh()->remember_token);
        $this->assertNull(session('password'));
    }

    public function test_confirmation_can_be_renewed_only_with_valid_credentials(): void
    {
        Mail::fake();
        $user = User::factory()->create(['name' => 'pending', 'confirmed' => false,
            'password' => 'long-valid-password12', 'confirmation_token' => str_repeat('a', 64)]);
        $this->post('/user/resend-confirmation', ['username' => 'pending', 'password' => 'incorrect-password']);
        Mail::assertNothingSent();
        $this->assertSame(str_repeat('a', 64), $user->fresh()->confirmation_token);
        $this->post('/user/resend-confirmation', ['username' => 'pending', 'password' => 'long-valid-password12'])
            ->assertRedirect()->assertSessionHas('success');
        $newHash = $user->fresh()->confirmation_token;
        $this->assertNotSame(str_repeat('a', 64), $newHash);
        Mail::assertSent(AccountConfirmationMail::class, function (AccountConfirmationMail $mail) use ($newHash) {
            $token = basename(parse_url($mail->confirmationUrl, PHP_URL_PATH));
            $this->assertSame($newHash, hash('sha256', $token));
            $this->get($mail->confirmationUrl)->assertRedirect('/user/connection');
            return true;
        });
        $this->assertTrue($user->fresh()->confirmed);
    }

    public function test_registration_mail_failure_preserves_a_retryable_account(): void
    {
        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('SMTP unavailable'));
        $this->post('/user/addUser', ['username' => 'pending', 'email' => 'pending@example.test',
            'password' => 'long-valid-password12', 'confirmPassword' => 'long-valid-password12'])
            ->assertRedirect('/user/connection')->assertSessionHas('error');
        $user = User::where('name', 'pending')->firstOrFail();
        $this->assertFalse($user->confirmed);
        $this->assertTrue(Hash::check('long-valid-password12', $user->password));
    }

    public function test_password_change_invalidates_old_session_versions(): void
    {
        Mail::fake();
        $user = User::factory()->create(['password' => 'long-valid-password12']);
        $oldVersion = hash('sha256', $user->password);
        $this->signIn($user)->post('/user/changePassword', [
            'current_password' => 'long-valid-password12', 'new_password' => 'new-long-password123', 'new_password_confirmation' => 'new-long-password123',
        ])->assertRedirect('/user/changePassword');
        $this->assertTrue(Hash::check('new-long-password123', $user->fresh()->password));
        $this->withSession(['user_id' => $user->id, 'credential_version' => $oldVersion])
            ->postJson('/game/rounds', ['game' => 'coinflip', 'stake' => 10])->assertUnauthorized();
    }

    public function test_rate_limits_for_account_checks_login_games_and_password_changes_are_independent(): void
    {
        Mail::fake();
        $user = User::factory()->create(['name' => 'player', 'password' => 'long-valid-password12']);
        for ($i = 0; $i < 6; $i++) {
            $this->getJson('/check-username?username=player')->assertOk();
        }
        $this->post('/user/connection', ['username' => 'player', 'password' => 'long-valid-password12'])
            ->assertRedirect('/');
        $id = $this->postJson('/game/rounds', ['game' => 'mines', 'stake' => 10])->assertOk()->json('round.id');
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/game/rounds/'.$id.'/actions', ['action' => 'status'])->assertUnprocessable();
        }
        $this->post('/user/changePassword', ['current_password' => 'long-valid-password12',
            'new_password' => 'new-long-password123', 'new_password_confirmation' => 'new-long-password123'])
            ->assertRedirect('/user/changePassword')->assertSessionHas('success');
    }

    public function test_avatar_input_and_locked_cosmetics_are_validated_on_the_server(): void
    {
        $user = User::factory()->create(['lvl' => 1]);
        $this->signIn($user)->postJson('/user/updateAvatar', ['image' => 'fa-user" onclick="alert(1)', 'color' => 'red'])->assertUnprocessable();
        $this->postJson('/user/updateAvatar', ['image' => 'fa-user-ninja', 'color' => 'purple', 'userLvl' => 1000])->assertUnprocessable();
        $this->assertSame('fa-user', $user->fresh()->profileImage);
        $this->post('/user/updateAvatar', ['image' => 'fa-user-injured', 'color' => 'blue'])->assertRedirect('/user/profile?id='.$user->id);
        $this->assertSame('blue', $user->fresh()->profileColor);
    }

    public function test_every_mutation_including_poker_quit_requires_csrf(): void
    {
        $this->app['env'] = 'local'; // Laravel intentionally skips CSRF in its normal test environment.
        $this->postJson('/game/quitPoker', ['playerId' => 1])->assertStatus(419);
        $this->postJson('/admin/toggleAdmin', ['userId' => 1, 'action' => 'admin'])->assertStatus(419);
        $this->postJson('/user/connection', ['username' => 'x', 'password' => 'x'])->assertStatus(419);
        $this->get('/user/deconnection')->assertStatus(405);
    }
}
