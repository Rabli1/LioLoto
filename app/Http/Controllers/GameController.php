<?php

namespace App\Http\Controllers;

use App\Services\UserServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Services\GameServices;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;


class GameController extends Controller
{
    private GameServices $gameServices;
    private UserServices $userServices;

    public function __construct(GameServices $gameServices, UserServices $userServices)
    {
        $this->gameServices = $gameServices;
        $this->userServices = $userServices;
    }
    private const USERS_PATH = 'database/json/users.json';
    private const POKER_STATE_PATH = 'database/json/pokerState.json';
    private const ETAG_PATH = 'database/json/Etag.json';
    private const SMALL_BLIND = 10;
    private function resolvePlayer(): ?User
    {
        $sessionUser = session('user');

        if (!$sessionUser) {
            return null;
        }

        if (!$sessionUser instanceof User) {
            if (is_array($sessionUser)) {
                $sessionUser = new User($sessionUser);
            } elseif (is_object($sessionUser)) {
                $sessionUser = new User((array) $sessionUser);
            } else {
                return null;
            }
        }

        $path = base_path(self::USERS_PATH);
        $users = json_decode(@file_get_contents($path), true);

        if (is_array($users)) {
            foreach ($users as $entry) {
                if (($entry['id'] ?? null) === $sessionUser->id) {
                    $freshUser = new User($entry);
                    session(['user' => $freshUser]);
                    return $freshUser;
                }
            }
        }

        session(['user' => $sessionUser]);

        return $sessionUser;
    }

    private function determineLiotodleAccess(?User $player): array
    {
        if (!$player) {
            return [
                'available' => false,
                'lockMessage' => 'Connecte-toi pour jouer au Liotodle du jour.'
            ];
        }

        $path = base_path(self::USERS_PATH);
        $users = json_decode(@file_get_contents($path), true);

        if (!is_array($users)) {
            return [
                'available' => false,
                'lockMessage' => 'Impossible de vérifier l’état du Liotodle. Réessaie plus tard.'
            ];
        }

        foreach ($users as $entry) {
            if (($entry['id'] ?? null) === $player->id) {
                $available = (bool) ($entry['daily'] ?? false);

                return [
                    'available' => $available,
                    'lockMessage' => $available
                        ? ''
                        : 'Vous avez déjà complété le Liotodle du jour. Reviens après le prochain reset !'
                ];
            }
        }

        return [
            'available' => false,
            'lockMessage' => 'Impossible de vérifier l’état du Liotodle. Réessaie plus tard.'
        ];
    }
    public function blackjack(): View
    {

        $player = $this->resolvePlayer();
        $balance = $player?->points ?? 0;

        return view('game.blackjack', [
            'playerBalance' => $balance,
        ]);
    }

    public function plinko(): View
    {

        $player = $this->resolvePlayer();
        $balance = $player?->points ?? 0;

        return view('game.plinko', [
            'playerBalance' => $balance,
        ]);
    }

    public function liotodle(): View
    {

        $player = $this->resolvePlayer();
        $balance = $player?->points ?? 0;
        $accessData = $this->determineLiotodleAccess($player);

        return view('game.liotodle', [
            'playerBalance' => $balance,
            'liotodleDailyAvailable' => $accessData['available'],
            'liotodleDailyLockMessage' => $accessData['lockMessage'],
        ]);
    }

    public function mines(): View
    {
        $player = $this->resolvePlayer();
        $balance = $player?->points ?? 0;

        return view('game.mines', [
            'playerBalance' => $balance,
        ]);
    }

    public function chickenRoad(): View
    {
        $player = $this->resolvePlayer();
        $balance = $player?->points ?? 0;

        return view('game.chicken-road', [
            'playerBalance' => $balance,
        ]);
    }

    public function roulette(): View
    {
        $player = $this->resolvePlayer();
        $balance = $player?->points ?? 0;

        return view('game.roulette', [
            'playerBalance' => $balance,
        ]);
    }

    public function slotMachine(): View
    {
        $player = $this->resolvePlayer();
        $balance = $player?->points ?? 0;

        return view('game.slot-machine', [
            'playerBalance' => $balance,
        ]);
    }
    public function crash(): View
    {
        $balance = 0;
        if (session()->has('user')) {
            $balance = session('user')->points;
        }
        return view('game.crash', [
            'playerBalance' => $balance
        ]);
    }
    public function poker(): View
    {
        $balance = 0;
        if (session()->has('user')) {
            $balance = session('user')->points;
        }
        return view('game.poker', [
            'playerBalance' => $balance
        ]);
    }

    public function getPokerState(): JsonResponse
    {
        $state = json_decode(@file_get_contents(base_path(self::POKER_STATE_PATH)), true) ?? [];
        $userId = session('user')->id;

        if (!empty($userId)) {
            foreach ($state['players'] as &$player) {
                if ($player['id'] === $userId && $state['roundStep'] !== 'showdown') {
                    $player['cards'] = array_map(function ($card) {
                        return $this->gameServices->Decrypt($card);
                    }, $player['cards']);
                }
            }
            unset($player);
        }

        return response()->json([
            'gameState' => $state
        ]);
    }
    public function joinPoker(): void
    {
        $user = session('user');
        if (!$user)
            return;

        $userPoints = is_object($user) ? ($user->points ?? 0) : ($user['points'] ?? 0);
        if ((int) $userPoints < 100) {
            return;
        }

        $path = base_path(self::POKER_STATE_PATH);
        $state = json_decode(@file_get_contents($path), true) ?? [];

        $players = $state['queue'] ?? [];
        $state = json_decode(@file_get_contents($path), true) ?? [];
        $state['players'] = $state['players'] ?? [];
        $state['queue'] = $state['queue'] ?? [];
        $state['roundStep'] = $state['roundStep'] ?? 'waiting';
        $playerInQueue = array_filter($players, function ($player) use ($user) {
            return $player['id'] == $user->id;
        });
        $playersInGame = array_filter($state['players'], function ($player) use ($user) {
            return $player['id'] == $user->id;
        });
        if (!empty($playerInQueue) || !empty($playersInGame)) {
            return;
        }
        if ($state['roundStep'] === 'waiting') {
            $state['players'][] = [
                'id' => $user->id,
                'name' => $user->name,
                'balance' => $user->points,
                'profileImage' => $user->profileImage,
                'profileColor' => $user->profileColor,
                'currentBet' => 0,
                'isAllIn' => false,
                'hasFolded' => false,
                'hasPlayed' => false,
                'hasWon' => false,
                'toKick' => false,
                'hand' => "",
                'cards' => [],
            ];

            if (count($state['players']) >= 2) {
                file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $this->initRound();
                return;
            }
        } else {
            $state['queue'][] = [
                'id' => $user->id,
                'name' => $user->name,
                'balance' => $user->points,
                'profileImage' => $user->profileImage,
                'profileColor' => $user->profileColor,
                'currentBet' => 0,
                'isAllIn' => false,
                'hasFolded' => false,
                'hasPlayed' => false,
                'hasWon' => false,
                'toKick' => false,
                'hand' => "",
                'cards' => [],
            ];
        }

        file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $Etag = ['Etag' => (string) Str::uuid()];
        file_put_contents(base_path(self::ETAG_PATH), json_encode($Etag, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    public function coinflip(): View
    {
        $player = $this->resolvePlayer();
        $balance = $player?->points ?? 0;

        return view('game.coinflip', [
            'playerBalance' => $balance,
        ]);
    }

    public function saveBalance(Request $request): JsonResponse
    {
        $user = $this->resolvePlayer();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'balance' => ['required', 'integer', 'min:0'],
            'game' => ['nullable', 'string'],
        ]);

        $path = base_path(self::USERS_PATH);
        $users = json_decode(@file_get_contents($path), true) ?? [];
        $updated = false;

        foreach ($users as &$entry) {
            if (($entry['id'] ?? null) === $user->id) {
                $difference = ($validated['balance'] - $entry['points']) / 2; // divisé par deux pcq il redonne la mise
                $mise = $validated['balance'] - $entry['points'];
                if ($difference > 0) {
                    $this->gameServices->addExp($difference, $entry);
                }
                if ($difference <= 0) {
                    $this->gameServices->addPointLost($mise, $entry);
                    $user->pointsLost = $entry['pointsLost'];
                }
                $entry['points'] = $validated['balance'];
                $entry['points'] = (int) $entry['points'];
                $user->points = (int) $entry['points'];
                $updated = true;
                break;
            }
        }
        unset($entry);

        if (!$updated) {
            return response()->json(['message' => 'User not found'], 404);
        }

        file_put_contents($path, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        session(['user' => $user]);

        return response()->json([
            'balance' => $user->points,
            'game' => $validated['game'] ?? null,
        ]);
    }
    public function initRound(): JsonResponse
    {
        $pokerPath = base_path(self::POKER_STATE_PATH);
        $state = json_decode(@file_get_contents($pokerPath), true) ?? [];
        $userPath = base_path(self::USERS_PATH);
        $users = json_decode(@file_get_contents($userPath), true) ?? [];

        // Kick players
        $state['players'] = array_filter($state['players'], function ($player) {
            return !($player['toKick'] ?? false);
        });
        $state['players'] = array_values($state['players']);

        // Pop Queue
        if (count($state['players']) < 6) {
            $spacesAvailable = 6 - count($state['players']);
            for ($i = 0; $i < $spacesAvailable; $i++) {
                if (empty($state['queue']))
                    break;
                $state['players'][] = array_shift($state['queue']);
            }
        }
        $playerCount = count($state['players']);
        if ($playerCount < 2) {
            $state['roundStep'] = 'waiting';
            file_put_contents($pokerPath, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return response()->json(['success' => false, 'message' => 'Not enough players to start the round.']);
        }

        //build deck
        $values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
        $suits = ['C', 'D', 'H', 'S'];
        $deck = [];

        foreach ($suits as $suit) {
            foreach ($values as $value) {
                $encyptedCard = $this->gameServices->Encrypt("{$value}-{$suit}");
                $deck[] = $encyptedCard;
            }
        }
        // Shuffle
        $deckSize = count($deck);
        for ($i = $deckSize - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$deck[$i], $deck[$j]] = [$deck[$j], $deck[$i]];
        }

        // Reset game state
        $state['roundStep'] = 'preFlop';
        $state['communityCards'] = [];
        $state['pot'] = 0;
        $state['requiredBet'] = self::SMALL_BLIND * 2;
        $state['smallBlind'] = ($state['smallBlind'] + 1) % $playerCount;
        $state['deck'] = $deck;

        // Reset player states and deal cards
        foreach ($state['players'] as &$player) {
            $player['hasFolded'] = false;
            $player['isAllIn'] = false;
            $player['currentBet'] = 0;
            $player['hasPlayed'] = false;
            $player['hasWon'] = false;
            $player['toKick'] = false;
            $player['hand'] = "";
            $player['cards'] = [array_pop($state['deck']), array_pop($state['deck'])];
        }

        //blind logic
        $smallBlindId = $state['players'][$state['smallBlind']]['id'];
        $bigBlindId = $state['players'][($state['smallBlind'] + 1) % $playerCount]['id'];
        foreach ($state['players'] as &$player) {
            if ($player['id'] === $smallBlindId) {
                $player['balance'] -= self::SMALL_BLIND;
                $player['currentBet'] += self::SMALL_BLIND;
                $state['pot'] += self::SMALL_BLIND;
                $player['hasPlayed'] = true;

            } elseif ($player['id'] === $bigBlindId) {
                $player['balance'] -= self::SMALL_BLIND * 2;
                $player['currentBet'] += self::SMALL_BLIND * 2;
                $state['pot'] += self::SMALL_BLIND * 2;
                $player['hasPlayed'] = true;
            }
        }
        unset($player);
        $state['playersTurn'] = ($state['smallBlind'] + 2) % $playerCount;

        // update users.json
        foreach ($users as &$user) {
            if ($smallBlindId === $user['id']) {
                $user['points'] -= self::SMALL_BLIND;
                $user['points'] = (int) $user['points'];
            }
            if ($bigBlindId === $user['id']) {
                $user['points'] -= self::SMALL_BLIND * 2;
                $user['points'] = (int) $user['points'];
            }
        }
        unset($user);

        file_put_contents($pokerPath, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents($userPath, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $Etag = ['Etag' => (string) Str::uuid()];
        file_put_contents(base_path(self::ETAG_PATH), json_encode($Etag, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return response()->json(['success' => true]);
    }

    public function placeBet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:-1',
        ]);
        $validatedAmount = (int) $validated['amount'];
        $pokerPath = base_path(self::POKER_STATE_PATH);
        $userPath = base_path(self::USERS_PATH);
        $state = json_decode(@file_get_contents($pokerPath), true) ?? [];
        $users = json_decode(@file_get_contents($userPath), true) ?? [];

        $userId = session('user')->id;
        $actingIndex = array_search($userId, array_column($state['players'], 'id'));

        $player = &$state['players'][$actingIndex];

        if ($validatedAmount === -1) {
            $player['hasFolded'] = true;

            $activePlayers = array_filter($state['players'], fn($p) => !($p['hasFolded'] ?? false));

            if (count($activePlayers) === 1) {
                $state['roundStep'] = 'winByFold';
                $winner = reset($activePlayers);
                $winnerId = $winner['id'];

                foreach ($state['players'] as &$p) {
                    if (($p['id'] ?? null) === $winnerId) {
                        $p['hasWon'] = true;
                        $p['balance'] = ($p['balance'] ?? 0) + ($state['pot'] ?? 0);
                        break;
                    }
                }
                unset($p);

                foreach ($users as &$u) {
                    if (($u['id'] ?? null) === $winnerId) {
                        $u['points'] = (int) (($u['points'] ?? 0) + ($state['pot'] ?? 0));
                        break;
                    }
                }
                unset($u);

                foreach ($state['players'] as &$p) {
                    $p['toKick'] = ((int) ($p['balance'] ?? 0) < 100);
                }
                unset($p);

                file_put_contents($pokerPath, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                file_put_contents($userPath, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $Etag = ['Etag' => (string) Str::uuid()];
                file_put_contents(base_path(self::ETAG_PATH), json_encode($Etag, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                return response()->json(['winner' => $winnerId]);
            }
        } else {
            $player['currentBet'] += $validatedAmount;
            $player['hasPlayed'] = true;
            $player['balance'] -= $validatedAmount;
            if ($player['balance'] < self::SMALL_BLIND * 2) {
                $player['isAllIn'] = true;
            }
            $state['requiredBet'] = max((int) $state['requiredBet'], (int) $player['currentBet']);
            $state['pot'] += $validatedAmount;

            $user = clone session('user');
            foreach ($users as &$entry) {
                if (($entry['id'] ?? null) === $user->id) {
                    if ($validatedAmount > 0) {
                        $this->gameServices->addExp($validatedAmount, $entry);
                    }
                    $entry['points'] -= $validatedAmount;
                    $entry['points'] = (int) $entry['points'];
                    $user->points = (int) $entry['points'];
                    break;
                }
            }
            session(['user' => $user]);
            file_put_contents($userPath, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            unset($entry);
        }

        unset($player);

        // Determine next player
        $playerCount = count($state['players']);
        $nextIndex = $actingIndex;
        for ($i = 0; $i < $playerCount; $i++) {
            $nextIndex = ($nextIndex + 1) % $playerCount;
            if (!$state['players'][$nextIndex]['hasFolded']) {
                $state['playersTurn'] = $nextIndex;
                break;
            }
        }

        $activePlayers = array_filter($state['players'], fn($p) => !$p['hasFolded']);
        $roundEnd = count(array_filter($activePlayers, fn($p) => $p['currentBet'] === $state['requiredBet'] && $p['hasPlayed'])) === count($activePlayers);
        $someoneAllIn = count(array_filter($activePlayers, fn($p) => $p['isAllIn'])) > 0;
        if ($roundEnd) {
            if ($someoneAllIn) {
                // If someone is all-in, skip to showdown
                $state['roundStep'] = 'showdown';
                foreach ($state['players'] as &$player) {
                    $player['cards'] = array_map(function ($card) {
                        return $this->gameServices->Decrypt($card);
                    }, $player['cards']);
                }
                $newCards = array_slice($state['deck'], 0, 5 - count($state['communityCards']));

                $newCards = array_map(
                    fn($card) => $this->gameServices->Decrypt($card),
                    $newCards
                );

                $state['communityCards'] = array_merge($state['communityCards'], $newCards);
                $this->settleRound($state);
                unset($player);
                foreach ($state['players'] as &$player) {
                    $player['currentBet'] = 0;
                    $player['hasPlayed'] = false;
                }
                unset($player);
                file_put_contents($pokerPath, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $Etag = ['Etag' => (string) Str::uuid()];
                file_put_contents(base_path(self::ETAG_PATH), json_encode($Etag, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                return response()->json();
            }
            $rounds = ['preFlop', 'flop', 'turn', 'river', 'showdown'];
            $currentIndex = array_search($state['roundStep'], $rounds);
            $state['roundStep'] = $rounds[$currentIndex + 1];
            $state['requiredBet'] = 0;
            switch ($state['roundStep']) {
                case 'flop':
                    $state['communityCards'] = array_map(
                        fn($card) => $this->gameServices->Decrypt($card),
                        array_slice($state['deck'], 0, 3)
                    );
                    $state['deck'] = array_slice($state['deck'], 3);
                    break;
                case 'turn':
                    $state['communityCards'][] = $this->gameServices->Decrypt($state['deck'][0]);
                    $state['deck'] = array_slice($state['deck'], 1);
                    break;
                case 'river':
                    $state['communityCards'][] = $this->gameServices->Decrypt($state['deck'][0]);
                    $state['deck'] = array_slice($state['deck'], 1);
                    break;
                case 'showdown':
                    foreach ($state['players'] as &$player) {
                        $player['cards'] = array_map(function ($card) {
                            return $this->gameServices->Decrypt($card);
                        }, $player['cards']);
                    }
                    $this->settleRound($state);
                    break;
            }

            foreach ($state['players'] as &$player) {
                $player['currentBet'] = 0;
                $player['hasPlayed'] = false;
            }
        }
        file_put_contents($pokerPath, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $Etag = ['Etag' => (string) Str::uuid()];
        file_put_contents(base_path(self::ETAG_PATH), json_encode($Etag, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return response()->json();
    }
    private function settleRound(&$state): void
    {
        $pokerHands = ['Carte Haute', 'Paire', 'Deux Paires', 'Brelan', 'Quinte', 'Flush', 'Full House', 'Carrée', 'Quinte Flush', 'Quinte Flush Royale'];
        $communityCards = $state['communityCards'];
        $players = &$state['players'];

        foreach ($players as &$player) {
            if ($player['hasFolded']) {
                continue;
            }
            $hand = [];
            $hand[] = $communityCards;
            $hand[] = $player['cards'];
            $player['hand'] = $this->getPokerHand($hand);
        }
        unset($player);

        $winnerSeatId = [];
        $winnerHand = -1;
        $bestHandValue = [];

        foreach ($players as $player) {
            if ($player['hasFolded']) {
                continue;
            }
            $handParts = explode(',', $player['hand']);
            $handName = $handParts[0];
            $handValue = array_search($handName, $pokerHands);

            if ($handValue > $winnerHand) {
                $winnerHand = $handValue;
                $winnerSeatId = [$player['id']];
                $bestHandValue = array_slice($handParts, 1);
            } else if ($handValue === $winnerHand) {
                $currentHandValue = array_slice($handParts, 1);

                $isPush = true;
                for ($i = 0; $i < count($bestHandValue); $i++) {
                    if ((int) $currentHandValue[$i] > (int) $bestHandValue[$i]) {
                        $winnerSeatId = [$player['id']];
                        $bestHandValue = $currentHandValue;
                        $isPush = false;
                        break;
                    } else if ((int) $currentHandValue[$i] < (int) $bestHandValue[$i]) {
                        $isPush = false;
                        break;
                    }
                }

                if ($isPush) {
                    $winnerSeatId[] = $player['id'];
                }
            }
        }

        $potShare = floor($state['pot'] / count($winnerSeatId));

        foreach ($players as &$player) {
            if (in_array($player['id'], $winnerSeatId)) {
                $player['hasWon'] = true;
                $player['balance'] += $potShare;
            }
        }

        $userPath = base_path(self::USERS_PATH);
        $users = json_decode(@file_get_contents($userPath), true) ?? [];

        foreach ($users as &$user) {
            if (in_array($user['id'], $winnerSeatId)) {
                $user['points'] += $potShare;
                $user['points'] = (int) $user['points'];
            }
        }

        file_put_contents($userPath, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if (session()->has('user')) {
            foreach ($users as $u) {
                if ($u['id'] === session('user')->id) {
                    $sessionUser = clone session('user');
                    $sessionUser->points = (int) $u['points'];
                    session(['user' => $sessionUser]);
                    break;
                }
            }
        }

        foreach ($players as &$player) {
            $playerBalance = (int) ($player['balance'] ?? 0);
            if ($playerBalance < 100) {
                $player['toKick'] = true;
            }
        }
        unset($player);
    }
    private function getPokerHand($cards)
    {
        $rankValues = [
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            '10' => 10,
            'J' => 11,
            'Q' => 12,
            'K' => 13,
            'A' => 14
        ];
        $flatCards = [];
        foreach ($cards as $cardSet) {
            if (is_array($cardSet)) {
                $flatCards = array_merge($flatCards, $cardSet);
            } else {
                $flatCards[] = $cardSet;
            }
        }

        $values = [];
        $suits = [];
        foreach ($flatCards as $card) {
            [$v, $s] = explode('-', $card);
            $val = $rankValues[$v];
            $values[] = $val;
            $suits[$s][] = $val;
        }

        sort($values);
        $high = $values[6];
        $counts = array_count_values($values);
        arsort($counts);
        $groups = array_values($counts);

        $flushSuit = null;
        foreach ($suits as $suit => $vals) {
            if (count($vals) >= 5) {
                $flushSuit = $suit;
                $flushVals = $vals;
                sort($flushVals);
                break;
            }
        }


        $hasStraight = function (array $vals): bool {
            $vals = array_values(array_unique($vals));
            if (in_array(14, $vals))
                $vals[] = 1;
            sort($vals);
            $consec = 1;
            for ($i = 1; $i < count($vals); $i++) {
                if ($vals[$i] == $vals[$i - 1] + 1)
                    $consec++;
                elseif ($vals[$i] != $vals[$i - 1])
                    $consec = 1;
                if ($consec >= 5)
                    return true;
            }
            return false;
        };

        $getStraightHigh = function (array $vals): ?int {
            $vals = array_values(array_unique($vals));
            if (in_array(14, $vals))
                $vals[] = 1;
            sort($vals);
            $consec = 1;
            for ($i = 1; $i < count($vals); $i++) {
                if ($vals[$i] == $vals[$i - 1] + 1) {
                    $consec++;
                    if ($consec >= 5)
                        return $vals[$i];
                } elseif ($vals[$i] != $vals[$i - 1]) {
                    $consec = 1;
                }
            }
            return null;
        };

        $isStraight = $hasStraight($values);
        $straightHigh = $isStraight ? $getStraightHigh($values) : null;

        if ($flushSuit) {
            $flushUnique = array_values(array_unique($flushVals));
            sort($flushUnique);
            if ($hasStraight($flushUnique)) {
                $highStraight = $getStraightHigh($flushUnique);
                if ($highStraight == 14 && min($flushUnique) == 10)
                    return "Quinte Flush Royale";
                return "Quinte Flush, " . $highStraight;
            }
        }
        $group4val = null;
        $group3val = null;
        $group2val = [];
        foreach ($counts as $val => $n) {
            if ($n === 4) {
                $group4val = $val;
            }
            if ($n === 3) {
                if ($group3val) {
                    if ($val > $group3val) {
                        $group3val = $val;
                    }
                } else {
                    $group3val = $val;
                }
            }
            if ($n === 2) {
                $group2val[] = $val;
            }
        }
        sort($group2val);
        if (count($group2val) > 2) {
            array_shift($group2val);
        }
        if ($group4val)
            return "Carrée," . $group4val;
        if ($group3val && $group2val)
            return "Full House," . $group3val . ',' . $group2val[count($group2val) - 1];
        if ($flushSuit)
            return "Flush," . $flushVals[4] . ',' . $flushVals[3] . ',' . $flushVals[2] . ',' . $flushVals[1] . ',' . $flushVals[0];
        if ($isStraight)
            return 'Quinte,' . $straightHigh;
        if ($group3val)
            return "Brelan," . $group3val;
        if (count($group2val) == 2)
            return "Deux Paires," . $group2val[0] . ',' . $group2val[1];
        if (count($group2val) == 1)
            return "Paire," . $group2val[0];
        return "Carte Haute," . $high;
    }

    public function quitPoker(Request $request): JsonResponse
    {
        $data = [];
        if ($request->isJson()) {
            $data = $request->json()->all();
        }
        if (empty($data)) {
            $raw = $request->getContent();
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }
        if (empty($data)) {
            $data = $request->all();
        }

        $playerId = isset($data['playerId']) ? (int) $data['playerId'] : null;
        $force = !empty($data['force']);

        if (!$playerId) {
            return response()->json(['success' => false, 'message' => 'playerId missing'], 200);
        }

        $path = base_path(self::POKER_STATE_PATH);
        $state = json_decode(@file_get_contents($path), true) ?? [];
        if ($state['roundStep'] === 'waiting') {
            $state['players'] = array_filter($state['players'], function ($p) use ($playerId) {
                return $p['id'] !== $playerId;
            });
        } else {
            foreach ($state['players'] as &$player) {
                if ($player['id'] === $playerId) {
                    $player['toKick'] = true;
                    if ($force) {
                        $player['hasFolded'] = true;
                        $activePlayers = array_filter($state['players'], fn($p) => !$p['hasFolded']);

                        if (count($activePlayers) === 1) {
                            $state['roundStep'] = 'winByFold';
                            $winner = reset($activePlayers);
                            $winnerId = $winner['id'];

                            foreach ($state['players'] as &$p) {
                                if ($p['id'] === $winnerId) {
                                    $p['hasWon'] = true;
                                    $p['balance'] += ($state['pot'] ?? 0);
                                    break;
                                }
                            }
                        }
                    }
                    break;
                }
            }
        }
        unset($player);

        file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $Etag = ['Etag' => (string) Str::uuid()];
        file_put_contents(base_path(self::ETAG_PATH), json_encode($Etag, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return response()->json(['success' => true], 200);
    }

    public function getWordListForClient(): JsonResponse
    {
        $words = $this->getWordList();

        // Affiche que la cache est valide pendant 1000 sec. Jai tu vraiment de faire ca? idk mais je le garde la parce que je pense quon le faisait avec chourot
        return response()->json($words)
            ->header('Cache-Control', 'public, max-age=1000')
            ->header('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 1000));
    }

    private function getWordList(): array
    {
        return Cache::remember('liotodle_words', 1000, function () { //temps de vie  de la cache 1000 sec
            $wordsFile = storage_path('app/json/liotodle.json');
            return array_map('strtoupper', json_decode(file_get_contents($wordsFile), true));
        });
    }
    public function checkWord(Request $request): JsonResponse
    {
        $request->validate([
            'word' => 'required|string|size:5'
        ]);

        $inputWord = strtoupper($request->query('word'));
        $secretWord = session('liotodle_secret');

        if (!$secretWord) {
            return response()->json(['error' => 'No game in progress'], 400);
        }

        $result = [];
        $secretLetters = str_split($secretWord);
        $inputLetters = str_split($inputWord);

        for ($i = 0; $i < 5; $i++) {
            if ($inputLetters[$i] === $secretLetters[$i]) {
                $result[$i] = 'correct';
            } elseif (in_array($inputLetters[$i], $secretLetters)) {
                $result[$i] = 'present';
            } else {
                $result[$i] = 'absent';
            }
        }

        return response()->json([
            'valid' => true,
            'result' => $result,
            'won' => $inputWord === $secretWord
        ]);
    }

    public function liotodleWord(): JsonResponse
    {
        $words = $this->getWordList();
        $randomWord = $words[array_rand($words)];

        session(['liotodle_secret' => $randomWord]);

        return response()->json(['ready' => true]);
    }

    public function finishDaily(Request $request)
    {
        if (!session()->has('user')) {
            return response()->json(['error' => 'No user'], 401);
        }

        $user = session()->get('user');
        $user->daily = false;

        session()->put('user', $user);

        $path = base_path(self::USERS_PATH);

        $users = json_decode(file_get_contents($path));

        foreach ($users as $u) {
            if ($u->id == $user->id) {
                $u->daily = false;
                break;
            }
        }

        file_put_contents($path, json_encode($users, JSON_PRETTY_PRINT));

        return response()->json(['status' => 'daily_disabled']);
    }
}
