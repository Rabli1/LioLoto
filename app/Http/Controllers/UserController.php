<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Services\UserServices;
use App\Models\User;
use ValueError;

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
            "profileImage" => "fa-user",
            "profileColor" => "black",
            "gold" => 0,
            "silver" => 0,
            "bronze" => 0,
            "pointsLost" => 0,
            "bio" => "",
            "confirmed" => true,
            "banned" => false,
            "lvl" => 1,
            "exp" => 0,
            "last_update" => date("Y-m-d"),
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
    public function profile(Request $request)
    {
        return view('user.profile', ['user' => $this->userService->findById($request->input('id'))]);
    }
    public function updateAvatar(Request $request)
    {
        $user = session('user');
        $profileImage = $request->input('image') ?? 'fa-user';
        $profileColor = $request->input('color') ?? 'black';
        $path = base_path('database/json/users.json');
        $users = json_decode(@file_get_contents($path), true) ?? [];
        foreach ($users as &$entry) {
            if ($entry['id'] === $user->id) {
                $entry['profileImage'] = $profileImage;
                $entry['profileColor'] = $profileColor;
                $user->profileImage = $entry['profileImage'];
                $user->profileColor = $entry['profileColor'];
                break;
            }
        }
        unset($entry);

        file_put_contents($path, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        session(['user' => $user]);

        return redirect('user/profile?id=' . $user->id);
    }
    public function updateBio(Request $request)
    {
        $user = session('user');
        $validated = $request->validate([
            'bio' => ['nullable', 'string', 'max:99'],
        ]);
        $path = base_path('database/json/users.json');
        $users = json_decode(@file_get_contents($path), true) ?? [];
        foreach ($users as &$entry) {
            if (($entry['id'] ?? null) === $user->id) {
                $entry['bio'] = $validated['bio'] ?? '';
                $user->bio = $entry['bio'];
                break;
            }
        }
        unset($entry);

        file_put_contents($path, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        session(['user' => $user]);

        return redirect('user/profile?id=' . $user->id);
    }
    public function leaderboard()
    {
        $users = json_decode(file_get_contents('../database/json/users.json'), true);
        array_multisort(array_column($users, 'points'), SORT_DESC, $users);
        $top10 = collect($users)->take(10);
        $apartUser = null;
        $isInTop10 = false;
        $userConnected = session()->has('user');
        if ($userConnected) {
            foreach ($top10 as $user) {
                if ($user['id'] == session('user')->id) {
                    $isInTop10 = true;
                    break;
                }
            }
        }
        $position = 0;
        if ($userConnected && !$isInTop10) {
            $apartUser = session('user');
            for ($i = 0; $i < count($users); $i++) {
                if ($users[$i]['id'] == $apartUser->id) {
                    $position = $i + 1;
                }
            }
        }
        return view('leaderboard', ["top10" => $top10, "apartUser" => $apartUser, "position" => $position]);
    }
}
