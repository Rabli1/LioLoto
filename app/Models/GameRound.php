<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameRound extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['state' => 'array', 'stake' => 'integer', 'payout' => 'integer'];
    }
}
