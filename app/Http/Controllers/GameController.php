<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Services\GameServices;

class GameController extends Controller
{
    private GameServices $gameServices;

    public function __construct(GameServices $gameServices)
    {
        $this->gameServices = $gameServices;
    }
    private const USERS_PATH = 'database/json/users.json';

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
                if($difference > 0){
                    $this->gameServices->addExp($difference, $entry);
                }
                $entry['points'] = $validated['balance'];
                $user->points = $entry['points'];
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
}
