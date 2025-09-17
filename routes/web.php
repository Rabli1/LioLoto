<?php
use App\Models\User;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
Route::get('/', function () {
    return view('index');
});

Route::get('user/signIn', [UserController::class, 'signIn']);
Route::get('/check-username', function (Request $request) {
    $username = $request->query("username");
    $json = storage_path('../database/json/users.json');
    $users = json_decode(file_get_contents($json), true);
    $collectedData = collect($users);
    $exists = $collectedData->contains("nom", $username);
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
Route::post('/addUser', function(Request $request){
    $username = $request->input("username");
    $email = $request->input("email");
    $password = $request->input("password");
    $json = storage_path('../database/json/users.json');
    $users = json_decode(file_get_contents($json), true);
    $lastId = $users[count($users) - 1]['id'];
    $newUser = [
        "id" => $lastId + 1,
        "nom" => $username,
        "email" => $email,
        "motDePasse" => password_hash($password, PASSWORD_BCRYPT),
        "points" => 1000,
        "imageProfil" => "fa-slab fa-regular fa-circle-user",
        "couleurProfil" => "pink",
        "or" => 0,
        "argent" => 0,
        "bronze" => 0,
        "pointPerdu" => 0,
        "bio" => "",    
        "confirme" => true,
        "banni" => false
    ];
    $users[] = $newUser;
    file_put_contents('../database/json/users.json', json_encode($users));
    return redirect('user/connection?message=votre compte à été créé');
});

Route::get('user/connection', [UserController::class, 'connection']);
Route::post('/connection', function(Request $request) {
    $username = $request->input("username");
    $password = $request->input("password");
    $json = storage_path('../database/json/users.json');
    $users = json_decode(file_get_contents($json), true);
    $userId = null;
    foreach( $users as $user){
        if($user['nom'] == $username){
            if(password_verify($password, $user['motDePasse'])){
                $userId = $user['id'];
                break;
            }
        }
    }
    if($userId == null){
        return redirect('user/connection?message=le nom d\'utilisateur ou le mot de passe est incorrect');
    }
    session_start();
    session(["userId" => $userId]);
    return redirect('/');
});

Route::get('user/profile', [ProfileController::class, 'profile']);
