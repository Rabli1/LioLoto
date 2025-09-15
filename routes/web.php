<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('index');
});
Route::get('user/signIn', [UserController::class, 'signIn' ]);
<<<<<<< HEAD

Route::get('/check-username', function ($username) {
    $exists = User::where('username', $username)->exists();
    return response()->json(['exists' => $exists]);
});
=======
Route::get('user/profile', [ProfileController::class, 'profile' ]);
>>>>>>> 6145566e971cf31847a80e92e1b99899745cc53d
