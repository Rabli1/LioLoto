<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserServices;
use App\Models\User;

class UserController extends Controller
{
    private UserServices $userService;

    public function __construct(UserServices $userService)
    {
        $this->userService = $userService;
    }

    public function signIn()
    {
        return view('user.signIn');
    }
    public function addUser(Request $request)
    {
        $username = $request->input("username");
        $email = $request->input("email");
        $password = $request->input("password");
        $users = $this->userService->all();
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
    }

    public function connection(Request $request)
    {
        return view('user.connection', ['message' => $request->input('message')]);
    }

    public function authenticate(Request $request)
    {
        $user = $this->userService->verifyCredentials($request->username, $request->password);
        if (!$user) {
            return redirect('user/connection?message=Nom d\'utilisateur ou mot de passe incorrect');
        }

        session(['user' => $user]);
        return redirect('/');
    }

    public function logout()
    {
        session()->forget('user');
        return redirect('user/connection?message=Vous avez été déconnecté');
    }

    public function checkUsername(Request $request)
    {
        return response()->json([
            'exists' => (bool) $this->userService->findByUsername($request->query('username'))
        ]);
    }

    public function checkEmail(Request $request)
    {
        return response()->json([
            'exists' => (bool) $this->userService->findByEmail($request->query('email'))
        ]);
    }
    public function profile(Request $request){
        return view('user.profile', ['user' => $this->userService->findById($request->input('id'))]);
    }
}
