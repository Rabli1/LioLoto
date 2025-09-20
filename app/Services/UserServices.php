<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class UserServices
{
    private string $file;

    public function __construct()
    {
        $this->file = storage_path('../database/json/users.json');
    }

    public function all(): Collection
    {
        $users = json_decode(file_get_contents($this->file), true);
        return collect($users ?? []);
    }
    public function findById(int $id): ?array
    {
        return $this->all()->first(fn($u) => $u['id'] === $id);
    }
    public function findByUsername(string $username): ?array
    {
        return $this->all()->first(fn($u) => $u['name'] === $username);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->all()->first(fn($u) => $u['email'] === $email);
    }
    public function verifyCredentials(string $username, string $password): ?User
    {
        $user = $this->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return new User($user);
        }
        return null;
    }
}
