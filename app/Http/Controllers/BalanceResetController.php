<?php

namespace App\Http\Controllers;

use App\Models\User;

class BalanceResetController extends Controller
{
    private const DATE_FILE = '/blackjack_last_reset.txt';
    private const USERS_FILE = 'database/json/users.json';
    private const RESET_AMOUT = 1000;

    public static function resetPoint(): void
    {
        $resetFile = storage_path(self::DATE_FILE);
        $today = date('Y-m-d');
        $lastReset = null;

        if (is_file($resetFile)) {
            $lastReset = trim((string)file_get_contents($resetFile));
        }

        if ($lastReset === $today) {
            return;
        }

        $path = base_path(self::USERS_FILE);
        $users = json_decode(@file_get_contents($path), true) ?? [];

        if (!$users) {
            file_put_contents($resetFile, $today);
            return;
        }

        foreach ($users as &$entry) {
            $entry['points'] = self::RESET_AMOUT;
        }
        unset($entry);

        file_put_contents($path, json_encode($users, JSON_PRETTY_PRINT));
        file_put_contents($resetFile, $today);

        $sessionUser = session('user');
        if ($sessionUser instanceof User) {
            $sessionUser->points = self::RESET_AMOUT;
            session(['user' => $sessionUser]);
        }
    }
}
