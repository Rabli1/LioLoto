<?php

namespace App\Http\Controllers;

use App\Mail\AccountConfirmationMail;
use App\Mail\PasswordChangedMail;
use App\Models\User;
use App\Services\UserServices;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(private UserServices $userService) {}

    public function signIn()
    {
        return session()->has('user_id') ? redirect('/') : view('user.signIn');
    }

    public function addUser(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:4', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('users', 'name')],
            'email' => ['required', 'email', 'max:254', Rule::unique('users', 'email')],
            'password' => $this->passwordRules(true),
            'confirmPassword' => ['required', 'same:password'],
        ]);
        $token = Str::random(64);

        try {
            $user = new User([
                'name' => $validated['username'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'profileImage' => 'fa-user',
                'profileColor' => 'black',
            ]);
            $user->forceFill(['confirmation_token' => hash('sha256', $token)])->save();
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23505'])) {
                throw ValidationException::withMessages(['username' => 'Ce nom ou ce courriel est déjà utilisé.']);
            }
            throw $exception;
        }

        $url = URL::temporarySignedRoute('user.confirm', now()->addDay(), ['token' => $token]);
        try {
            Mail::to($user->email)->send(new AccountConfirmationMail($user->toArray(), $url));
        } catch (\Throwable $exception) {
            Log::warning('Account confirmation notification could not be delivered.');
            return redirect('/user/connection')->with('error', 'Compte créé, mais le courriel est indisponible. Réessaie avec « Renvoyer la confirmation ».');
        }

        return redirect('/user/connection')->with('success', 'Compte créé. Vérifie ton courriel pour le confirmer.');
    }

    public function confirmAccount(string $token)
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{64}$/', $token), 404);
        $confirmed = User::where('confirmation_token', hash('sha256', $token))
            ->where('confirmed', false)
            ->update(['confirmed' => true, 'confirmation_token' => null]);

        return redirect('/user/connection')->with($confirmed ? 'success' : 'error',
            $confirmed ? 'Compte confirmé. Tu peux te connecter.' : 'Lien invalide ou déjà utilisé.');
    }

    public function resendConfirmation(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ]);
        $user = $this->userService->verifyCredentials($validated['username'], $validated['password']);
        if ($user && !$user->confirmed && !$user->banned && $user->email) {
            $token = Str::random(64);
            $user->forceFill(['confirmation_token' => hash('sha256', $token)])->save();
            $url = URL::temporarySignedRoute('user.confirm', now()->addDay(), ['token' => $token]);
            try {
                Mail::to($user->email)->send(new AccountConfirmationMail($user->toArray(), $url));
            } catch (\Throwable $exception) {
                Log::warning('Account confirmation notification could not be delivered.');
                return back()->with('error', 'Le courriel est indisponible. Réessaie plus tard.');
            }
        }
        return back()->with('success', 'Si les identifiants correspondent à un compte en attente, un nouveau lien a été envoyé.');
    }

    public function connection(Request $request)
    {
        if (session()->has('user_id')) {
            return redirect('/');
        }

        return response()->view('user.connection', [
            'message' => $request->input('message'),
            'username' => '',
            'password' => '',
        ])->withCookie(cookie()->forget('remember_user'));
    }

    public function authenticate(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
            'remember' => ['sometimes', 'in:1,on,true'],
        ]);
        $user = $this->userService->verifyCredentials($validated['username'], $validated['password']);

        if (!$user || $user->banned || !$user->confirmed) {
            return back()->withErrors(['username' => 'Connexion impossible. Vérifie tes identifiants et la confirmation du compte.'])
                ->withInput($request->only('username'));
        }

        if (Hash::needsRehash($user->password)) {
            $user->password = $validated['password'];
            $user->save();
        }
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $this->userService->rememberInSession($user);

        return redirect('/')->withCookie(cookie()->forget('remember_user'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/user/connection')->withCookie(cookie()->forget('remember_user'));
    }

    public function changePassword()
    {
        return view('user.changePassword');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => $this->passwordRules(),
            'new_password' => array_merge($this->passwordRules(true), ['confirmed']),
        ]);
        $user = DB::transaction(function () use ($request, $validated) {
            $user = User::whereKey($request->attributes->get('player')->id)->lockForUpdate()->firstOrFail();
            if (!password_verify($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages(['current_password' => 'Le mot de passe actuel est incorrect.']);
            }
            $user->password = $validated['new_password'];
            $user->remember_token = Str::random(60);
            $user->save();

            return $user;
        });
        $request->session()->regenerate();
        $this->userService->rememberInSession($user);
        try {
            Mail::to($user->email)->send(new PasswordChangedMail($user));
        } catch (\Throwable $exception) {
            Log::warning('Password change notification could not be delivered.');
        }

        return redirect()->route('user.changePassword')->with('success', 'Mot de passe modifié.');
    }

    public function checkUsername(Request $request)
    {
        $validated = $request->validate(['username' => ['required', 'string', 'max:20']]);
        return response()->json(['exists' => (bool) $this->userService->findByUsername($validated['username'])]);
    }

    public function checkEmail(Request $request)
    {
        $validated = $request->validate(['email' => ['required', 'email', 'max:254']]);
        return response()->json(['exists' => (bool) $this->userService->findByEmail($validated['email'])]);
    }

    public function profile(Request $request)
    {
        $validated = $request->validate(['id' => ['required', 'integer', 'min:1']]);
        $user = User::whereKey($validated['id'])->where('banned', false)->firstOrFail();
        return view('user.profile', ['user' => $user->publicProfile()]);
    }

    public function updateAvatar(Request $request)
    {
        $images = ['fa-user' => 0, 'fa-user-injured' => 0, 'fa-user-secret' => 2, 'fa-user-tie' => 3,
            'fa-user-graduate' => 4, 'fa-user-astronaut' => 5, 'fa-user-ninja' => 7, 'fa-skull' => 10,
            'fa-fish' => 15, 'fa-frog' => 20, 'fa-hippo' => 25, 'fa-poo' => 50];
        $colors = ['black' => 0, 'red' => 0, 'blue' => 0, 'green' => 0, 'yellow' => 0, 'orange' => 5,
            'pink' => 6, 'brown' => 8, 'gray' => 10, 'purple' => 15, 'white' => 25];
        $validated = $request->validate([
            'image' => ['nullable', Rule::in(array_keys($images))],
            'color' => ['nullable', Rule::in(array_keys($colors))],
        ]);
        $id = $request->attributes->get('player')->id;
        DB::transaction(function () use ($id, $validated, $images, $colors) {
            $user = User::whereKey($id)->lockForUpdate()->firstOrFail();
            $image = $validated['image'] ?? $user->profileImage;
            $color = $validated['color'] ?? $user->profileColor;
            if (($images[$image] ?? PHP_INT_MAX) > $user->lvl || ($colors[$color] ?? PHP_INT_MAX) > $user->lvl) {
                throw ValidationException::withMessages(['image' => 'Niveau insuffisant pour cet avatar.']);
            }
            $user->update(['profileImage' => $image, 'profileColor' => $color]);
        });
        return redirect('/user/profile?id='.$id);
    }

    public function updateBio(Request $request)
    {
        $validated = $request->validate(['bio' => ['nullable', 'string', 'max:99']]);
        $user = $request->attributes->get('player');
        $user->update(['bio' => $validated['bio'] ?? '']);
        return redirect('/user/profile?id='.$user->id);
    }

    public function leaderboard()
    {
        $users = User::where('banned', false)->orderByDesc('points')->orderBy('id')->get();
        $top10 = $users->take(10)->map(fn (User $user) => $user->publicProfile());
        $current = $this->userService->current();
        $position = $current ? $users->search(fn (User $user) => $user->id === $current->id) : false;
        $apartUser = $current && !$top10->contains('id', $current->id) ? (object) $current->toArray() : null;
        return view('leaderboard', ['top10' => $top10, 'apartUser' => $apartUser, 'position' => $position === false ? 0 : $position + 1]);
    }

    public function support(Request $request)
    {
        $validated = $request->validate(['game' => ['nullable', 'string', 'max:40']]);
        return view('user.support', ['game' => $validated['game'] ?? '']);
    }

    private function passwordRules(bool $newPassword = false): array
    {
        return array_filter(['bail', 'required', 'string', $newPassword ? 'min:12' : null,
            function (string $attribute, string $value, \Closure $fail) {
                // Bcrypt's 72-byte limit must be enforced in bytes, including Unicode passwords.
                if (strlen($value) > 72 || str_contains($value, "\0")) {
                    $fail('Le mot de passe doit contenir au plus 72 octets, sans caractère nul.');
                }
            }]);
    }
}
