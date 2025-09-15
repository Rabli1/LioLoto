<?php
 
namespace App\Http\Controllers;
 
use App\Models\User;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function profile(): View
    {
        $json = storage_path('../database/json/users.json');
    $users = json_decode(file_get_contents($json), true);
        return view('user.profile', ['users' => $users]);
    }
}