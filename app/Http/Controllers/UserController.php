<?php
 
namespace App\Http\Controllers;
 
use App\Models\User;
use Illuminate\View\View;
 
class UserController extends Controller
{
    public function signIn(string $id): View
    {
        return view('user.signIn');
    }
}