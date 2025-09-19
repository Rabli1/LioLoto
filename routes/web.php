<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::get('user/signIn', [UserController::class, 'signIn']);
Route::get('user/connection', [UserController::class, 'connection']);
Route::post('user/connection', [UserController::class, 'authenticate']);
Route::post('user/addUser', [UserController::class, 'addUser']);
Route::get('user/deconnection', [UserController::class, 'logout']);
Route::get('user/profile', [ProfileController::class, 'profile']);

// AJAX checks
Route::get('/check-username', [UserController::class, 'checkUsername']);
Route::get('/check-email', [UserController::class, 'checkEmail']);
