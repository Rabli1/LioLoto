<?php
 
namespace App\Http\Controllers;
 
use App\Models\User;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
 
class UserController extends Controller
{
    public function signIn(): View{
        return view('user.signIn');
    }
    public function connection(): View{
        return view('user.connection');
    }
}