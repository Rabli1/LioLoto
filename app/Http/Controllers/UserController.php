<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Services\UserServices;
use App\Models\User;
use ValueError;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordChangedMail;

class UserController extends Controller
{
    private UserServices $userService;

    public function __construct(UserServices $userService)
    {
        $this->userService = $userService;
    }

    public function signIn()
    {
        $this->userService->redirectIfConnected();
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
            "admin" => false,
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
        $this->userService->redirectIfConnected();
        return view('user.connection', ['message' => $request->input('message')]);
    }

    public function changePassword()
    {
        return view('user.changePassword');
    }

    public function updatePassword(Request $request)
{
    $request->validate([
        'current_password' => ['required'],
        'new_password' => ['required', 'string'],
        'new_password_confirmation' => ['required'],
    ]);

    $user = session('user');

    if (!$user) {
        return redirect('user/connection?message=Vous devez être connecté');
    }

    if ($request->new_password !== $request->new_password_confirmation) {
        return back()->withErrors(['new_password' => 'Les deux mots de passe ne sont pas identiques.']);
    }

    if (!password_verify($request->current_password, $user->password)) { 
        return back()->withErrors(['current_password' => 'L’ancien mot de passe est incorrect.']);
    }

    $path = base_path('database/json/users.json');
    $users = json_decode(file_get_contents($path), true);

    foreach ($users as &$entry) {
        if ($entry['id'] === $user->id) {
            $entry['password'] = password_hash($request->new_password, PASSWORD_BCRYPT);
            $user->password = $entry['password'];
            break;
        }
    }
    unset($entry);

    file_put_contents($path, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    session(['user' => $user]);

     Mail::to($user->email)->send(new PasswordChangedMail($user));

    return redirect()->route('user.changePassword')->with('success', 'Mot de passe modifié avec succès.');
}

    public function authenticate(Request $request)
    {
        $user = $this->userService->verifyCredentials($request->username, $request->password);
        if (!$user) {
            return redirect('user/connection?message=Nom d\'utilisateur ou mot de passe incorrect');
        }
        if($user->banned){
            return redirect('user/connection?message=Votre compte a été banni');
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
    
    // public function resetDate(Request $request)
    // {
    //     $user = session('user');
    //     if (!$user) {
    //         return response()->json(['error' => 'Not authenticated'], 401);
    //     }

    //     $path = base_path('database/json/users.json');
    //     $users = json_decode(@file_get_contents($path), true) ?? [];
    //     $updated = false;

    //     foreach ($users as &$entry) {
    //         if ($entry['id'] == $user->id) {
    //             if ($entry['last_update'] !== date("Y-m-d")) {
    //                 $entry['last_update'] = date("Y-m-d");
    //                 $entry['points'] = 1000;
    //                 $user->last_update = $entry['last_update'];
    //                 $user->points = $entry['points'];
    //                 session(['user' => $user]);
    //                 $updated = true;
    //             }
    //             break;
    //         }
    //     }
    //     unset($entry);

    //     if ($updated) {
    //         file_put_contents($path, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    //         return response()->json(['success' => true, 'points' => 1000]);
    //     } else {
    //         return response()->json(['success' => false, 'message' => 'Already reset today or user not found']);
    //     }
    // }

    public function updatePoints(Request $request)
    {
        $userId = $request->input('userId');
        $balance = $request->input('balance');

        $path = base_path('database/json/users.json');
        $users = json_decode(@file_get_contents($path), true) ?? [];

        $updated = false;
        foreach ($users as &$entry) {
            if ($entry['id'] == $userId) {
                $entry['points'] = intval($balance);
                $updated = true;
                break;
            }
        }
        unset($entry);

        if ($updated) {
            file_put_contents($path, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return response()->json(['balance' => intval($balance)]);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }
    public function support(){
        return view('user.support');
    }
}
