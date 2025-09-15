<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('index');
});
Route::get('user/signIn', [UserController::class, 'signIn' ]);

Route::get('/check-username', function ($username) {
    $exists = User::where('username', $username)->exists();
    return response()->json(['exists' => $exists]);
});
