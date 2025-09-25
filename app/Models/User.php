<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    public $id;
    public $name;
    public $points;
    public $profileImage;
    public $profileColor;
    public $admin;
    public $gold;
    public $silver;
    public $bronze;
    public $pointsLost;
    public $lvl;
    public $exp;
    public $timestamps = false;

    protected $table = null;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    // Return primary key attribute name
    public function getKeyName()
    {
        return 'id';
    }
}
