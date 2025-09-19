<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class GameController extends Controller
{
    function blackjack(): View{
        return view('game.blackjack');
    }
}