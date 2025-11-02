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

    public function wordle(): View
    {

        $player = $this->resolvePlayer();
        $balance = $player?->points ?? 0;

        return view('game.wordle', [
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
        $this->userServices->redirectIfNotAdmin();
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
        $playerInQueue = array_filter($players, function ($player) use ($user) {
            return $player['id'] == $user->id;
        });

        if (!empty($playerInQueue)) {
            return;
        }
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
            "hasWon" => false,
            'toKick' => false,
            'cards' => [],
        ];
        $state['queue'] = $players;

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
    public function initRound(Request $request): JsonResponse
    {
        $path = base_path(self::POKER_STATE_PATH);
        $state = json_decode(@file_get_contents($path), true) ?? [];

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
        $state['requiredBet'] = 50;
        $state['deck'] = $deck;

        //blind logic

        // Reset player states and deal cards
        foreach ($state['players'] as &$player) {
            $player['hasFolded'] = false;
            $player['isAllIn'] = false;
            $player['currentBet'] = 0;
            $player['hasPlayed'] = false;
            $player['hasWon'] = false;
            $player['toKick'] = false;
            $player['cards'] = [array_pop($state['deck']), array_pop($state['deck'])];
        }

        file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $Etag = ['Etag' => (string) Str::uuid()];
        file_put_contents(base_path(self::ETAG_PATH), json_encode($Etag, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return response()->json(['success' => true]);
    }

    public function placeBet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|integer',
            'playerId' => 'required|integer',
        ]);
        $validatedAmount = $validated['amount'];
        $pokerPath = base_path(self::POKER_STATE_PATH);
        $userPath = base_path(self::USERS_PATH);
        $state = json_decode(@file_get_contents($pokerPath), true) ?? [];

        $state['requiredBet'] = max((int) $state['requiredBet'], (int) $validatedAmount);

        $actingIndex = array_search($validated['playerId'], array_column($state['players'], 'id'));

        $player = &$state['players'][$actingIndex];
        $valToReturn = 0;
        if ($validatedAmount === -1) {
            $valToReturn = $player['balance'];
            $player['hasFolded'] = true;

            $activePlayers = array_filter($state['players'], fn($p) => !$p['hasFolded']);

            if (count($activePlayers) === 1) {
                $winnerKey = array_key_first($activePlayers);
                $state['players'][$winnerKey]['hasWon'] = true;
            }
        } else {
            $valToReturn = $player['balance'] - $validatedAmount;
            $player['currentBet'] += $validatedAmount;
            $player['hasPlayed'] = true;
            $player['balance'] -= $validatedAmount;

            $state['pot'] += $validatedAmount;

            // change in users.json
            $users = json_decode(@file_get_contents($userPath), true) ?? [];
            $user = clone session('user');
            foreach ($users as &$entry) {
                if (($entry['id'] ?? null) === $user->id) {
                    if ($validatedAmount > 0) {
                        $this->gameServices->addExp($validatedAmount, $entry);
                    }
                    $entry['points'] -= $validatedAmount;
                    $entry['points'] = (int) $entry['points'];
                    $valToReturn = $entry['points'];
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

        if ($roundEnd) {
            $rounds = ['preFlop', 'flop', 'turn', 'river', 'showdown'];
            $currentIndex = array_search($state['roundStep'], $rounds);
            $state['roundStep'] = $rounds[$currentIndex + 1];
            $state['requiredBet'] = 0;
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

        }
        file_put_contents($pokerPath, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $Etag = ['Etag' => (string) Str::uuid()];
        file_put_contents(base_path(self::ETAG_PATH), json_encode($Etag, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return response()->json(['newBalance' => $valToReturn]);
    }

    public function quitPoker(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'playerId' => 'required|integer'
        ]);

        $path = base_path(self::POKER_STATE_PATH);
        $state = json_decode(@file_get_contents($path), true) ?? [];

        foreach ($state['players'] as &$player) {
            if ($player['id'] === $validated['playerId']) {
                $player['toKick'] = true;
                break;
            }
        }

        file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $Etag = ['Etag' => (string) Str::uuid()];
        file_put_contents(base_path(self::ETAG_PATH), json_encode($Etag, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return response()->json(['success' => true]);
    }

    public function wordleWord(): JsonResponse
    {
        // Générer un mot secret stocké en session (invisible au client)
        $wordsFile = storage_path('app/json/wordle.json');
        $words = json_decode(file_get_contents($wordsFile), true);

        $randomWord = strtoupper($words[array_rand($words)]);

        // Stocker le mot en SESSION (pas renvoyé au client)
        session(['wordle_secret' => $randomWord]);

        // Renvoyer seulement une confirmation
        return response()->json(['ready' => true]);
    }

    public function checkWord(Request $request): JsonResponse
    {
        $request->validate([
            'word' => 'required|string|size:5'
        ]);

        $inputWord = strtoupper($request->query('word'));
        $secretWord = session('wordle_secret');

        if (!$secretWord) {
            return response()->json(['error' => 'No game in progress'], 400);
        }

        // Vérifier si le mot existe dans le dictionnaire
        $wordsFile = storage_path('app/json/wordle.json');
        $words = array_map('strtoupper', json_decode(file_get_contents($wordsFile), true));

        if (!in_array($inputWord, $words)) {
            return response()->json(['valid' => false]);
        }

        // Calculer les couleurs des lettres
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


    private function getWordList(): array
    {
        return Cache::remember('wordle_words', 3600, function () {
            $wordsFile = storage_path('app/json/wordle.json');
            return array_map('strtoupper', json_decode(file_get_contents($wordsFile), true));
        });
    }
}
