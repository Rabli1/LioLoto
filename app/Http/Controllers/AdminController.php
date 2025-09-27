<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\UserServices;
use App\Models\User;

class AdminController extends Controller
{
    private const USERS_PATH = 'database/json/users.json';
        private UserServices $userService;

    public function __construct(UserServices $userService)
    {
        $this->userService = $userService;
    }
    public function dashboard(): View
    {
        $users = $this->userService->all();
        return view('admin.dashboard', [
            'users' => $users,
        ]);
    }
}