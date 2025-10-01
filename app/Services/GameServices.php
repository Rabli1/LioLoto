<?php

namespace App\Services;


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
    public function addPointLost($pointLost, &$user){

        $user['pointsLost'] += abs($pointLost);
    }
}
