<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
    public function dashboard(): View | RedirectResponse
    {
        $this->userService->redirectIfNotConnected();
        $this->userService->redirectIfNotAdmin();
        $users = $this->userService->all();
        return view('admin.dashboard', [
            'users' => $users,
        ]);
    }
    public function fixPoints(Request $request):RedirectResponse
    {
        $userId = (int) $request->input('userId');
        $newPoints = (int) $request->input('points');
        $path = base_path(self::USERS_PATH);
        $users = json_decode(@file_get_contents($path), true);
        foreach ($users as &$user) {
            if ($user['id'] === $userId) {
                $user['points'] = $newPoints;
                file_put_contents($path, json_encode($users));
            }
        }
        return redirect('/admin/dashboard');
    }
    public function toggleBan(Request $request): RedirectResponse
    {
        $userId = (int) $request->input('userId');
        $action = $request->input('action');
        $path = base_path(self::USERS_PATH);
        $users = json_decode(@file_get_contents($path), true);
        foreach ($users as &$user) {
            if ($user['id'] === $userId) {
                if ($action === 'ban') {
                    $user['banned'] = true;
                } elseif ($action === 'unban') {
                    $user['banned'] = false;
                }
                file_put_contents($path, json_encode($users));
                break;
            }
        }
        return redirect('/admin/dashboard');
    }
    public function toggleAdmin(Request $request): RedirectResponse
    {
        $userId = (int) $request->input('userId');
        $action = $request->input('action');
        $path = base_path(self::USERS_PATH);
        $users = json_decode(@file_get_contents($path), true);
        foreach ($users as &$user) {
            if ($user['id'] === $userId) {
                if ($action === 'admin') {
                    $user['admin'] = true;
                } elseif ($action === 'user') {
                    $user['admin'] = false;
                }
                file_put_contents($path, json_encode($users));
                break;
            }
        }
        return redirect('/admin/dashboard');
    }
        public function deleteUser(Request $request): RedirectResponse
    {
        $userId = (int) $request->input('userId');
        $path = base_path(self::USERS_PATH);
        $users = json_decode(@file_get_contents($path), true);
        $users = array_filter($users, function ($user) use ($userId) {
            return $user['id'] !== $userId;
        });
        file_put_contents($path, json_encode(array_values($users)));
        return redirect('/admin/dashboard');
    }
    public function killPoker(Request $request): RedirectResponse
    {
        $this->userService->redirectIfNotAdmin();
        $path = database_path('json/pokerState.json');
        $initialState = [
            "playersTurn" => 1,
            "roundStep" => "waiting",
            "communityCards" => [],
            "pot" => 75,
            "requiredBet" => 50,
            "smallBlind" => 1,
            "queue" => [],
            "players" => [],
            "deck" => []
        ];
        file_put_contents($path, json_encode($initialState));
        return redirect('/admin/dashboard');
    }
}