<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class UserServices
{
    public function all(): Collection
    {
        return User::query()->get()->map(fn (User $user) => $user->toArray());
    }

    public function findById(int $id): ?array
    {
        return User::find($id)?->toArray();
    }

    public function findByUsername(string $username): ?array
    {
        return User::where('name', $username)->first()?->toArray();
    }

    public function findByEmail(string $email): ?array
    {
        return User::where('email', strtolower($email))->first()?->toArray();
    }

    public function verifyCredentials(string $username, string $password): ?User
    {
        $user = User::where('name', $username)->first();
        // Missing users still perform password verification.
        $hash = $user?->password ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

        return password_verify($password, $hash) ? $user : null;
    }

    public function current(): ?User
    {
        return session()->has('user_id') ? User::find(session('user_id')) : null;
    }

    public function rememberInSession(User $user): void
    {
        session(['user_id' => $user->id, 'user' => (object) $user->toArray(),
            'credential_version' => hash('sha256', $user->password)]);
    }
}
