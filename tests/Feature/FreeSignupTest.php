<?php

namespace Tests\Feature;

use App\Mail\AccountConfirmationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FreeSignupTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'test-only-long-password123';

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.require_email_confirmation' => false]);
        Mail::fake();
    }

    private function registration(string $username = 'freeplayer', array $extra = []): array
    {
        return array_merge([
            'username' => $username,
            'password' => self::PASSWORD,
            'confirmPassword' => self::PASSWORD,
        ], $extra);
    }

    public function test_a_user_can_register_without_email_and_play_without_an_email_provider(): void
    {
        $this->post('/user/addUser', $this->registration())->assertRedirect('/user/connection');
        $user = User::where('name', 'freeplayer')->firstOrFail();
        $this->assertNull($user->email);
        $this->assertTrue($user->confirmed);
        $this->assertNull($user->confirmation_token);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));

        $this->post('/user/connection', ['username' => $user->name, 'password' => self::PASSWORD])
            ->assertRedirect('/')->assertSessionHas('user_id', $user->id);
        $this->getJson('/game/rounds/current')->assertOk()->assertJsonPath('balance', 1000);
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_free_signup_keeps_null_emails_and_rejects_privilege_and_request_config_injection(): void
    {
        $this->post('/user/addUser', $this->registration('freeplayer', [
            'email' => '  ', 'admin' => true, 'points' => 999999, 'confirmed' => false,
            'confirmation_token' => 'attacker-token', 'AUTH_REQUIRE_EMAIL_CONFIRMATION' => true,
            'require_email_confirmation' => true,
        ]))->assertRedirect('/user/connection');
        $user = User::where('name', 'freeplayer')->firstOrFail();
        $this->assertNull($user->email);
        $this->assertNull($user->confirmation_token);
        $this->assertTrue($user->confirmed);
        $this->assertFalse($user->admin);
        $this->assertFalse($user->banned);
        $this->assertSame(1000, $user->points);
        $this->post('/user/addUser', $this->registration('secondplayer'))->assertRedirect('/user/connection');
        $this->assertDatabaseCount('users', 2);
        Mail::assertNothingSent();
    }

    public function test_optional_emails_are_normalized_and_still_validated_and_unique(): void
    {
        $this->post('/user/addUser', $this->registration('freeplayer', ['email' => 'PLAYER@Example.com ']))
            ->assertRedirect('/user/connection');
        $this->assertSame('player@example.com', User::where('name', 'freeplayer')->firstOrFail()->email);
        $this->postJson('/user/addUser', $this->registration('secondplayer', ['email' => 'player@example.com']))
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->postJson('/user/addUser', $this->registration('thirdplayer', ['email' => 'invalid']))
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->postJson('/user/addUser', $this->registration('fourthplayer', ['email' => ['invalid']]))
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertDatabaseCount('users', 1);
        Mail::assertNothingSent();
    }

    public function test_a_pending_legacy_account_is_confirmed_only_after_correct_credentials(): void
    {
        $token = str_repeat('a', 64);
        $user = User::factory()->create([
            'name' => 'pendingplayer', 'password' => self::PASSWORD,
            'email' => null, 'confirmed' => false, 'confirmation_token' => $token,
        ]);
        $this->post('/user/connection', ['username' => $user->name, 'password' => 'wrong-password123'])
            ->assertSessionHasErrors('username')->assertSessionMissing('user_id');
        $this->assertFalse($user->fresh()->confirmed);
        $this->assertSame($token, $user->fresh()->confirmation_token);
        $this->post('/user/connection', [
            'username' => $user->name, 'password' => self::PASSWORD,
            'admin' => true, 'points' => 999999, 'banned' => false,
        ])->assertRedirect('/')->assertSessionHas('user_id', $user->id);
        $this->assertTrue($user->fresh()->confirmed);
        $this->assertNull($user->fresh()->confirmation_token);
        $this->assertFalse($user->fresh()->admin);
        $this->assertSame(1000, $user->fresh()->points);
        Mail::assertNothingSent();
    }

    public function test_free_mode_does_not_confirm_or_authenticate_a_banned_account(): void
    {
        $token = str_repeat('b', 64);
        $user = User::factory()->create([
            'name' => 'bannedplayer', 'password' => self::PASSWORD,
            'confirmed' => false, 'banned' => true, 'confirmation_token' => $token,
        ]);
        $this->post('/user/connection', ['username' => $user->name, 'password' => self::PASSWORD, 'banned' => false])
            ->assertSessionHasErrors('username')->assertSessionMissing('user_id');
        $this->assertFalse($user->fresh()->confirmed);
        $this->assertTrue($user->fresh()->banned);
        $this->assertSame($token, $user->fresh()->confirmation_token);
        Mail::assertNothingSent();
    }

    public function test_resend_is_unavailable_without_creating_tokens_or_sending_email(): void
    {
        $token = str_repeat('c', 64);
        $user = User::factory()->create(['name' => 'pendingplayer', 'password' => self::PASSWORD,
            'confirmed' => false, 'confirmation_token' => $token]);
        $this->postJson('/user/resend-confirmation', ['username' => $user->name, 'password' => self::PASSWORD])->assertNotFound();
        $this->assertSame($token, $user->fresh()->confirmation_token);
        $this->assertFalse($user->fresh()->confirmed);
        Mail::assertNothingSent();
    }

    public function test_password_changes_in_free_mode_do_not_send_notifications_even_with_an_optional_email(): void
    {
        $user = User::factory()->create(['password' => self::PASSWORD, 'email' => 'optional@example.com']);
        $this->withSession(['user_id' => $user->id, 'credential_version' => hash('sha256', $user->password)])
            ->post('/user/changePassword', [
                'current_password' => self::PASSWORD, 'new_password' => 'new-test-password123',
                'new_password_confirmation' => 'new-test-password123',
            ])->assertRedirect('/user/changePassword');
        $this->assertTrue(Hash::check('new-test-password123', $user->fresh()->password));
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_verified_mode_requires_email_and_ignores_client_requests_to_disable_confirmation(): void
    {
        config(['auth.require_email_confirmation' => true]);
        $this->postJson('/user/addUser', $this->registration('missingemail', [
            'require_email_confirmation' => false, 'AUTH_REQUIRE_EMAIL_CONFIRMATION' => false,
        ]))->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->post('/user/addUser', $this->registration('verifiedplayer', [
            'email' => 'verified@example.com', 'confirmed' => true, 'require_email_confirmation' => false,
        ]))->assertRedirect('/user/connection');
        $user = User::where('name', 'verifiedplayer')->firstOrFail();
        $this->assertFalse($user->confirmed);
        $this->assertNotNull($user->confirmation_token);
        $this->post('/user/connection', ['username' => $user->name, 'password' => self::PASSWORD])
            ->assertSessionHasErrors('username')->assertSessionMissing('user_id');
        Mail::assertSent(AccountConfirmationMail::class, 1);
    }
}
