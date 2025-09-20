<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});
Route::get('/leaderboard', function () {
    $users = json_decode(file_get_contents('../database/json/users.json'), true);
    array_multisort(array_column($users, 'points'), SORT_DESC, $users);
    $top10 = collect($users)->take(10);
    $apartUser = null;
    $isInTop10 = false;
    $userConnected = session()->has('user');
    if($userConnected){
        foreach($top10 as $user){
            if($user['id'] == session('user')->id){
                $isInTop10 = true;
                break;
            }
        }
    }
    $position = 0;
    if($userConnected && !$isInTop10){
        $apartUser = session('user');
        for($i = 0; $i < count($users); $i++){
            if($users[$i]['id'] == $apartUser->id){
                $position = $i + 1;
            }
        }
    }
    return view('leaderboard', ["top10" => $top10, "apartUser" => $apartUser, "position" => $position]);
});
Route::get('/game', function () {
    return view('game.index');
});

Route::get('user/signIn', [UserController::class, 'signIn']);
Route::get('user/connection', [UserController::class, 'connection']);
Route::post('user/connection', [UserController::class, 'authenticate']);
Route::post('user/addUser', [UserController::class, 'addUser']);
Route::get('user/deconnection', [UserController::class, 'logout']);
Route::get('user/profile', [UserController::class, 'profile']);
Route::get('game/blackjack', [GameController::class, 'blackjack']);


Route::get('/check-username', [UserController::class, 'checkUsername']);
Route::get('/check-email', [UserController::class, 'checkEmail']);
