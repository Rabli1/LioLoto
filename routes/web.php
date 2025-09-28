<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::get('user/signIn', [UserController::class, 'signIn']);
Route::get('user/connection', [UserController::class, 'connection']);
Route::post('user/connection', [UserController::class, 'authenticate']);
Route::post('user/addUser', [UserController::class, 'addUser']);
Route::get('user/deconnection', [UserController::class, 'logout']);
Route::get('user/profile', [UserController::class, 'profile']);
Route::post('user/updateAvatar', [UserController::class, 'updateAvatar'])->name('user.updateAvatar');
Route::post('user/updateBio', [UserController::class, 'updateBio']);
Route::get('/leaderboard', [UserController::class, 'leaderboard']);
Route::get('/game', function () {
    return view('game.index');
});
Route::get('game/blackjack', [GameController::class, 'blackjack']);
Route::get('game/plinko', [GameController::class, 'plinko']);
Route::post('game/balance', [GameController::class, 'saveBalance']);
Route::get('game/mines', [GameController::class, 'mines']);

Route::get('/check-username', [UserController::class, 'checkUsername']);
Route::get('/check-email', [UserController::class, 'checkEmail']);

Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
Route::post('admin/fixPoints', [AdminController::class, 'fixPoints']);
Route::post('admin/toggleBan', [AdminController::class, 'toggleBan']);
Route::post('/test-form', function (Request $request) {
    dd($request);
});