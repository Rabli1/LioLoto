<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RefreshSessionUser;
use App\Http\Middleware\RequireAdmin;
use App\Http\Middleware\RequireUser;
use Illuminate\Support\Facades\Route;

Route::middleware(RefreshSessionUser::class)->group(function () {
    Route::view('/', 'index');
    Route::view('/about', 'about');
    Route::view('/game', 'game.index');
    Route::get('/leaderboard', [UserController::class, 'leaderboard']);
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::get('/user/support', [UserController::class, 'support']);
    Route::get('/user/signIn', [UserController::class, 'signIn']);
    Route::get('/user/connection', [UserController::class, 'connection'])->name('login');
    Route::post('/user/connection', [UserController::class, 'authenticate'])->middleware('throttle:5,1,login:');
    Route::post('/user/addUser', [UserController::class, 'addUser'])->middleware('throttle:5,10,registration:');
    Route::post('/user/resend-confirmation', [UserController::class, 'resendConfirmation'])->middleware('throttle:3,10,confirmation-resend:');
    Route::get('/user/confirm/{token}', [UserController::class, 'confirmAccount'])->middleware(['signed', 'throttle:20,1,confirmation:'])->name('user.confirm');
    Route::get('/check-username', [UserController::class, 'checkUsername'])->middleware('throttle:30,1,username-check:');
    Route::get('/check-email', [UserController::class, 'checkEmail'])->middleware('throttle:30,1,email-check:');

    Route::middleware(RequireUser::class)->group(function () {
        Route::post('/user/deconnection', [UserController::class, 'logout']);
        Route::post('/user/updateAvatar', [UserController::class, 'updateAvatar'])->name('user.updateAvatar');
        Route::post('/user/updateBio', [UserController::class, 'updateBio']);
        Route::get('/user/changePassword', [UserController::class, 'changePassword'])->name('user.changePassword');
        Route::post('/user/changePassword', [UserController::class, 'updatePassword'])->middleware('throttle:5,10,password-change:')->name('user.updatePassword');

        Route::get('/game/rounds/current', [GameController::class, 'currentRound']);
        Route::post('/game/rounds', [GameController::class, 'startRound'])->middleware('throttle:60,1,round-start:');
        Route::post('/game/rounds/{round}/actions', [GameController::class, 'roundAction'])->whereUuid('round')->middleware('throttle:120,1,round-action:');

        // Old clients must never be allowed to write balances or other players' poker state.
        foreach (['balance', 'updateEtag', 'getPokerState', 'joinPoker', 'initRound', 'placeBet', 'quitPoker', 'liotodle/finish'] as $endpoint) {
            Route::post('/game/'.$endpoint, [GameController::class, 'retiredEndpoint']);
        }
        foreach (['liotodle/word', 'liotodle/check', 'liotodle/list'] as $endpoint) {
            Route::get('/game/'.$endpoint, [GameController::class, 'retiredEndpoint']);
        }

        Route::prefix('admin')->middleware(RequireAdmin::class)->group(function () {
            Route::get('/dashboard', [AdminController::class, 'dashboard']);
            Route::post('/fixPoints', [AdminController::class, 'fixPoints']);
            Route::post('/toggleBan', [AdminController::class, 'toggleBan']);
            Route::post('/toggleAdmin', [AdminController::class, 'toggleAdmin']);
            Route::post('/deleteUser', [AdminController::class, 'deleteUser']);
            Route::post('/killPoker', [AdminController::class, 'killPoker']);
        });
    });

    Route::get('/game/{game}', [GameController::class, 'show']);
});
