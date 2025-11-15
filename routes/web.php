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
Route::get('/user/changePassword', [UserController::class, 'changePassword'])->name('user.changePassword');
Route::post('/user/changePassword', [UserController::class, 'updatePassword'])->name('user.updatePassword');
Route::get('user/support', [UserController::class, 'support']);
Route::get('/leaderboard', [UserController::class, 'leaderboard']);
Route::get('/game', function () {
    return view('game.index');
});
Route::get('game/blackjack', [GameController::class, 'blackjack']);
Route::get('game/plinko', [GameController::class, 'plinko']);
Route::get('game/mines', [GameController::class, 'mines']);
Route::get('game/chicken-road', [GameController::class, 'chickenRoad']);
Route::get('game/roulette', [GameController::class, 'roulette']);
Route::get('game/slot-machine', [GameController::class, 'slotMachine']);
Route::get('game/crash', [GameController::class, 'crash']);
Route::get('game/coinflip',[GameController::class, 'coinflip']);
Route::get('game/poker', [GameController::class, 'poker']);
Route::get('game/liotodle', [GameController::class, 'liotodle']);
Route::get('/game/liotodle/word', [GameController::class, 'liotodleWord']);
Route::get('/game/liotodle/check', [GameController::class, 'checkWord']);
Route::get('/game/liotodle/list', [GameController::class, 'getWordListForClient']);

Route::get('/check-username', [UserController::class, 'checkUsername']);
Route::get('/check-email', [UserController::class, 'checkEmail']);
Route::get('/user/confirm/{token}', [UserController::class, 'confirmAccount'])->name('user.confirm');
Route::post('/game/balance', [GameController::class, 'saveBalance']);
Route::post('/game/updateEtag', [GameController::class, 'updateEtag']);
Route::post('/game/getPokerState', [GameController::class, 'getPokerState']);
Route::post('/game/joinPoker', [GameController::class, 'joinPoker']);
Route::post('/game/initRound', [GameController::class, 'initRound']);
Route::post('/game/placeBet', [GameController::class, 'placeBet']);
Route::post('/game/quitPoker', [GameController::class, 'quitPoker']);

Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
Route::post('admin/fixPoints', [AdminController::class, 'fixPoints']);
Route::post('admin/toggleBan', [AdminController::class, 'toggleBan']);
Route::post('admin/toggleAdmin', [AdminController::class, 'toggleAdmin']);
Route::post('admin/deleteUser', [AdminController::class, 'deleteUser']);
Route::post('admin/killPoker', [AdminController::class, 'killPoker']);
