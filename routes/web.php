<?php
use App\Models\User;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
Route::get('/', function () {
    return view('index');
});

Route::get('user/signIn', [UserController::class, 'signIn']);
Route::get('/check-username', function (Request $request) {
    $username = $request->query("username");
    $json = storage_path('../database/json/users.json');
    $users = json_decode(file_get_contents($json), true);
    $collectedData = collect($users);
    $exists = $collectedData->contains("name", $username);
    return response()->json(["exists" => $exists]);
});
Route::get('/check-email', function (Request $request) {
    $email = $request->query("email");
    $json = storage_path('../database/json/users.json');
    $users = json_decode(file_get_contents($json), true);
    $collectedData = collect($users);
    $exists = $collectedData->contains("email", $email);
    return response()->json(["exists" => $exists]);
});
Route::post('/addUser', function (Request $request) {
    $username = $request->input("username");
    $email = $request->input("email");
    $password = $request->input("password");
    $json = storage_path('../database/json/users.json');
    $users = json_decode(file_get_contents($json), true);
    $lastId = $users[count($users) - 1]['id'];
    $newUser = [
        "id" => $lastId + 1,
        "name" => $username,
        "email" => $email,
        "password" => password_hash($password, PASSWORD_BCRYPT),
        "points" => 1000,
        "image" => "fa-slab fa-regular fa-circle-user",
        "color" => "pink",
        "gold" => 0,
        "silver" => 0,
        "bronze" => 0,
        "pointsLost" => 0,
        "bio" => "",
        "confirmed" => true,
        "banned" => false,
        "lvl" => 1,
        "exp" => 0
    ];
    $users[] = $newUser;
    file_put_contents('../database/json/users.json', json_encode($users));
    return redirect('user/connection?message=votre compte à été créé');
});

Route::get('user/connection', [UserController::class, 'connection']);
Route::post('/connection', function (Request $request) {
    $username = $request->input("username");
    $password = $request->input("password");
    $json = storage_path('../database/json/users.json');
    $users = json_decode(file_get_contents($json), true);
    $VerfiedUser = null;
    foreach ($users as $user) {
        if ($user['name'] == $username) {
            if (password_verify($password, $user['password'])) {
                $VerfiedUser = $user;
                break;
            }
        }
    }
    if ($VerfiedUser == null) {
        return redirect('user/connection?message=le nom d\'utilisateur ou le mot de passe est incorrect');
    }
    $user = new User([
        'id' => $VerfiedUser['id'],
        'name' => $VerfiedUser['name'],
        'points' => $VerfiedUser['points'],
        'profileImage' => $VerfiedUser['profileImage'],
        'profileColor' => $VerfiedUser['profileColor'],
        'admin' => $VerfiedUser['admin'],
    ]);
    Auth::login($user);
    return redirect('/');
});

Route::get('user/profile', [ProfileController::class, 'profile']);
