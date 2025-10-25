<?php

namespace App\Services;
use Illuminate\Support\Facades\Crypt;

class GameServices
{
    private string $file;

    public function __construct()
    {
        $this->file = database_path('json/users.json');
    }
    public function addExp($exp, &$user){
        $user['exp'] += $exp;
        while ($user['exp'] >= $user['lvl'] * 1000) {
            $user['exp'] -= $user['lvl'] * 1000;
            $user['lvl'] += 1;
        }
    }
    public function addPointLost($pointsLost, &$user){

        $user['pointsLost'] += abs($pointsLost);
    }
    public function Encrypt(string $data): string{
        return Crypt::encryptString($data);
    }
    public function Decrypt(string $data): string{
        return Crypt::decryptString($data);
    }
}
