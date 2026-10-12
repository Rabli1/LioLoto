<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'password', 'bio', 'profileImage', 'profileColor'];

    protected $hidden = ['password', 'remember_token', 'confirmation_token'];

    public function publicProfile(): array
    {
        return $this->only(['id', 'name', 'points', 'profileImage', 'profileColor', 'gold',
            'silver', 'bronze', 'pointsLost', 'bio', 'lvl', 'exp']);
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'admin' => 'boolean',
            'confirmed' => 'boolean',
            'banned' => 'boolean',
            'daily' => 'boolean',
            'points' => 'integer',
            'pointsLost' => 'integer',
            'gold' => 'integer',
            'silver' => 'integer',
            'bronze' => 'integer',
            'lvl' => 'integer',
            'exp' => 'integer',
        ];
    }
}
