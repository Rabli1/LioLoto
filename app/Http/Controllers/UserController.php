<?php
 
namespace App\Http\Controllers;
 
use App\Models\User;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
 
class UserController extends Controller
{
    public function signIn(): View{
        return view('user.signIn');
    }
public function connection(Request $request): View
{
    $message = $request->query('message');
    return view('user.connection', ['message' => $message]);
}
}