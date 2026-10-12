<?php

namespace App\Http\Controllers;

use App\Models\GameRound;
use App\Models\User;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(UserServices $users)
    {
        return view('admin.dashboard', ['users' => $users->all()]);
    }

    public function fixPoints(Request $request)
    {
        $validated = $request->validate([
            'userId' => ['required', 'integer', 'exists:users,id'],
            'points' => ['required', 'integer', 'min:0', 'max:1000000000'],
        ]);
        $this->mutateUser($request, $validated['userId'], function (User $user) use ($validated) {
            $user->points = $validated['points'];
        });
        return redirect('/admin/dashboard');
    }

    public function toggleBan(Request $request)
    {
        $validated = $request->validate([
            'userId' => ['required', 'integer', 'exists:users,id'],
            'action' => ['required', Rule::in(['ban', 'unban'])],
        ]);
        $this->mutateUser($request, $validated['userId'], function (User $user) use ($validated) {
            $user->banned = $validated['action'] === 'ban';
        }, true);
        return redirect('/admin/dashboard');
    }

    public function toggleAdmin(Request $request)
    {
        $validated = $request->validate([
            'userId' => ['required', 'integer', 'exists:users,id'],
            'action' => ['required', Rule::in(['admin', 'user'])],
        ]);
        $this->mutateUser($request, $validated['userId'], function (User $user) use ($validated) {
            $user->admin = $validated['action'] === 'admin';
        }, true);
        return redirect('/admin/dashboard');
    }

    public function deleteUser(Request $request)
    {
        $validated = $request->validate(['userId' => ['required', 'integer', 'exists:users,id']]);
        $this->mutateUser($request, $validated['userId'], fn (User $user) => $user->delete(), true);
        return redirect('/admin/dashboard');
    }

    public function killPoker(Request $request)
    {
        DB::transaction(function () use ($request) {
            $actor = User::whereKey($request->attributes->get('player')->id)->lockForUpdate()->firstOrFail();
            abort_unless($actor->admin && !$actor->banned, 403);
            GameRound::where('game', 'poker')->where('status', 'active')
                ->update(['status' => 'lost', 'updated_at' => now()]);
        });
        return redirect('/admin/dashboard');
    }

    private function mutateUser(Request $request, int $id, callable $change, bool $protectSelf = false): void
    {
        DB::transaction(function () use ($request, $id, $change, $protectSelf) {
            // Lock administrators in a consistent order so simultaneous privilege changes cannot remove the final administrator.
            $admins = User::where('admin', true)->orderBy('id')->lockForUpdate()->get();
            $actor = $admins->firstWhere('id', $request->attributes->get('player')->id);
            abort_unless($actor && !$actor->banned, 403);
            abort_if($protectSelf && $id === $actor->id, 422, 'Ton propre compte est protégé.');
            $user = User::whereKey($id)->lockForUpdate()->firstOrFail();
            $change($user);
            if ($user->exists) {
                $user->save();
            }
        });
    }
}
