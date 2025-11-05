<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class UserServices
{
    private string $file;

    public function __construct()
    {
        $this->file = database_path('json/users.json');
    }

    public function all(): Collection
    {
        if (!File::exists($this->file)) {
            return collect();
        }

        try {
            $contents = File::get($this->file);
        } catch (\Throwable $exception) {
            Log::warning('Unable to read users file.', [
                'path' => $this->file,
                'exception' => $exception,
            ]);

            return collect();
        }

        if ($contents === '') {
            return collect();
        }

        $decoded = json_decode($contents, true);

        if (!is_array($decoded)) {
            Log::warning('Users file contains invalid JSON.', [
                'path' => $this->file,
            ]);

            return collect();
        }

        return collect($decoded);
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
    public function redirectIfNotConnected(){
        if(!session()->has("user")){
            abort(redirect('/user/connection'));
        }
    }
    public function redirectIfConnected(){
        if(session()->has("user")){
            abort(redirect('/'));
        }
    }
    public function redirectIfNotAdmin(){
        if(session()->has('user') && !session("user")->admin){
            abort(redirect('/'));
        }
    } 
}
