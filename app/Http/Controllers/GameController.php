<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Services\GameServices;
use Illuminate\Support\Str;

class GameController extends Controller
{
    private GameServices $gameServices;

    public function __construct(GameServices $gameServices)
    {
        $this->gameServices = $gameServices;
    }
    private const USERS_PATH = 'database/json/users.json';
    private const POKER_STATE_PATH = 'database/json/pokerState.json';
    private const ETAG_PATH = 'database/json/Etag.json';

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
    public function getEtag(): JsonResponse
    {
        $Etag = json_decode(@file_get_contents(base_path(self::ETAG_PATH)), true) ?? [];
        return response()->json([
            'Etag' => $Etag
        ]);
    }
    public function updateEtag(): JsonResponse
    {
        $Etag = [ 'Etag' => (string) Str::uuid()];
        file_put_contents(base_path(self::ETAG_PATH), json_encode($Etag, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return response()->json([
            'newEtag' => $Etag
        ]);
    }
    public function getPokerState(): JsonResponse
    {
        $state = json_decode(@file_get_contents(base_path(self::POKER_STATE_PATH)), true) ?? [];
        return response()->json([
            'gameState' => $state
        ]);
    }
    public function joinPoker(): void
    {
        $user = session('user');
        $path = base_path(self::POKER_STATE_PATH);
        $state = json_decode(@file_get_contents($path), true) ?? [];

        $players = $state['queue'] ?? [];

        $players[] = [
            'id' => $user->id,
            'name' => $user->name,
            'balance' => $user->points,
            'profileImage' => $user->profileImage,
            'profileColor' => $user->profileColor,
            'currentBet' => 0,
            'isAllIn' => false,
            'hasFolded' => false,
            'hasPlayed' => false,
            'toKick' => false,
            'cards' => [],
        ];
        $state['queue'] = $players;

        file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
    public function initRound(Request $request): JsonResponse
    {
        $path = base_path(self::POKER_STATE_PATH);
        $state = json_decode(@file_get_contents($path), true) ?? [];
        
        // Kick players
        $state['players'] = array_filter($state['players'], function($player) {
            return !($player['toKick'] ?? false);
        });
        $state['players'] = array_values($state['players']);

        // Pop Queue
        if (count($state['players']) < 6) {
            $spacesAvailable = 6 - count($state['players']);
            for ($i = 0; $i < $spacesAvailable; $i++) {
                if (empty($state['queue'])) break;
                $state['players'][] = array_shift($state['queue']);
            }
        }

        $values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
        $suits = ['C', 'D', 'H', 'S'];
        $deck = [];

        foreach ($suits as $suit) {
            foreach ($values as $value) {
                $deck[] = "{$value}-{$suit}";
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
        $state['requiredBet'] = 25;
        $state['deck'] = $deck;
        
        // Reset player states and deal cards
        foreach ($state['players'] as &$player) {
            $player['hasFolded'] = false;
            $player['isAllIn'] = false;
            $player['currentBet'] = 0;
            $player['hasPlayed'] = false;
            $player['toKick'] = false;
            $player['cards'] = [array_pop($state['deck']), array_pop($state['deck'])];
        }

        file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return response()->json(['success' => true]);
    }

    public function placeBet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:0',
            'playerId' => 'required|integer'
        ]);

        $path = base_path(self::POKER_STATE_PATH);
        $state = json_decode(@file_get_contents($path), true) ?? [];
        
        foreach ($state['players'] as &$player) {
            if ($player['id'] === $validated['playerId']) {
                $player['currentBet'] += $validated['amount'];
                $player['hasPlayed'] = true;
                $state['pot'] += $validated['amount'];
                break;
            }
        }

        file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return response()->json(['success' => true]);
    }

    public function nextRound(): JsonResponse
    {
        $path = base_path(self::POKER_STATE_PATH);
        $state = json_decode(@file_get_contents($path), true) ?? [];
        
        $rounds = ['preFlop', 'flop', 'turn', 'river', 'showdown'];
        $currentIndex = array_search($state['roundStep'], $rounds);
        $state['roundStep'] = $rounds[$currentIndex + 1];

        switch ($state['roundStep']) {
            case 'flop':
                $state['communityCards'] = array_slice($state['deck'], 0, 3);
                $state['deck'] = array_slice($state['deck'], 3);
                break;
            case 'turn':
                $state['communityCards'][] = $state['deck'][0];
                $state['deck'] = array_slice($state['deck'], 1);
                break;
            case 'river':
                $state['communityCards'][] = $state['deck'][0];
                $state['deck'] = array_slice($state['deck'], 1);
                break;
        }

        foreach ($state['players'] as &$player) {
            $player['currentBet'] = 0;
            $player['hasPlayed'] = false;
        }

        file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return response()->json(['success' => true]);
    }

    public function quitPoker(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'playerId' => 'required|integer'
        ]);

        $path = base_path(self::POKER_STATE_PATH);
        $state = json_decode(@file_get_contents($path), true) ?? [];
        
        foreach($state['players'] as &$player) {
            if ($player['id'] === $validated['playerId']) {
                $player['toKick'] = true;
                break;
            }
        }

        file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return response()->json(['success' => true]);
    }
}
