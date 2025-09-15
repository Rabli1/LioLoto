<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});
Route::get('user/signIn', [UserController::class, 'signIn' ]);
Route::get('user/profile', [ProfileController::class, 'profile' ]);
