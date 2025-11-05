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
        $playersInGame = array_filter($state['players'], function ($player) use ($user) {
            return $player['id'] == $user->id;
        });
        if (!empty($playerInQueue) || !empty($playersInGame)) {
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
            'hand' => "",
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

        //build deck
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
                $player['balance'] -= 25;
                $player['currentBet'] += 25;
                $state['pot'] += 25;
                $player['hasPlayed'] = true;

            } elseif ($player['id'] === $bigBlindId) {
                $player['balance'] -= 50;
                $player['currentBet'] += 50;
                $state['pot'] += 50;
                $player['hasPlayed'] = true;
            }
        }
        unset($player);
        $state['playersTurn'] = ($state['smallBlind'] + 2) % $playerCount;

        // update users.json
        foreach ($users as &$user) {
            if ($smallBlindId === $user['id']) {
                $user['points'] -= 25;
                $user['points'] = (int) $user['points'];
            }
            if ($bigBlindId === $user['id']) {
                $user['points'] -= 50;
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
        $valToReturn = 0;

        if ($validatedAmount === -1) {
            $valToReturn = $player['balance'];
            $player['hasFolded'] = true;

            $activePlayers = array_filter($state['players'], fn($p) => !$p['hasFolded']);

            if (count($activePlayers) === 1) {
                $state['roundStep'] = 'winByFold';
                $winnerKey = $activePlayers[0]['id'];
                $state['players'][$winnerKey]['hasWon'] = true;
                $winnerId = $state['players'][$winnerKey]['id'];
                foreach ($users as &$user) {
                    if ($winnerId === $user['id']) {
                        $user['points'] += $state['pot'];
                    }
                }
                unset($user);
            }
        } else {
            $state['requiredBet'] = max((int) $state['requiredBet'], (int) $validatedAmount);
            $valToReturn = $player['balance'] - $validatedAmount;
            $player['currentBet'] += $validatedAmount;
            $player['hasPlayed'] = true;
            $player['balance'] -= $validatedAmount;

            $state['pot'] += $validatedAmount;

            // change in users.json
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
                case 'showdown':
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
        return response()->json(['newBalance' => $valToReturn]);
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

        if (!empty($winnerSeatId)) {
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
        }
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
        // Flatten the nested array of cards
        $flatCards = [];
        foreach ($cards as $cardSet) {
            if (is_array($cardSet)) {
                $flatCards = array_merge($flatCards, $cardSet);
            } else {
                $flatCards[] = $cardSet;
            }
        }
        // Parse ranks and suits
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

        // Detect flush
        $flushSuit = null;
        foreach ($suits as $suit => $vals) {
            if (count($vals) >= 5) {
                $flushSuit = $suit;
                $flushVals = $vals;
                sort($flushVals);
                break;
            }
        }

        // Helper inline functions
        $hasStraight = function (array $vals): bool {
            $vals = array_values(array_unique($vals));
            if (in_array(14, $vals))
                $vals[] = 1; // Ace low
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
                $vals[] = 1; // Ace low
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

        // Check for straight
        $isStraight = $hasStraight($values);
        $straightHigh = $isStraight ? $getStraightHigh($values) : null;

        // Check for straight flush
        if ($flushSuit) {
            $flushUnique = array_values(array_unique($flushVals));
            sort($flushUnique);
            if ($hasStraight($flushUnique)) {
                $highStraight = $getStraightHigh($flushUnique);
                if ($highStraight == 14 && min($flushUnique) == 10)
                    return "Quinte Flush Royale";
                return "Quinte Flush";
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
            return "Carrée";
        if ($group3val && $group2val)
            return "Full House," . $group3val . ',' . $group2val[count($group2val) - 1];
        if ($flushSuit)
            return "Flush";
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
        $validated = $request->validate([
            'playerId' => 'required|integer',
            'force' => 'nullable|boolean'
        ]);
        $path = base_path(self::POKER_STATE_PATH);
        $state = json_decode(@file_get_contents($path), true) ?? [];

        foreach ($state['players'] as &$player) {
            if ($player['id'] === $validated['playerId']) {
                $player['toKick'] = true;
                if ($validated['force']) {
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
                    break;
                }
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
