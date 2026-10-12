@php($pageTitle = 'Connexion')
@extends('layouts.app')
@section('content')
<main id="main-content" class="page-width auth-page">
    <div class="auth-intro"><h1>Connexion</h1>@include('shared.game-art',['gameKey'=>'blackjack'])</div>
    <div class="form-card">@include('shared.errors')
        @if($message ?? null)<p class="form-message" role="status">{{ $message }}</p>@endif
        <form method="post" action="{{ url('/user/connection') }}">@csrf
            <label for="username">Nom d’utilisateur</label><input id="username" name="username" type="text" autocomplete="username" maxlength="255" value="{{ old('username', $username ?? '') }}" required>
            <label for="password">Mot de passe</label><input id="password" name="password" type="password" autocomplete="current-password" required>
            <div class="remember-row"><input id="remember" name="remember" type="checkbox" value="1"><label for="remember">Se souvenir de moi</label></div>
            <button class="button" type="submit">Se connecter <span aria-hidden="true">↗</span></button>
            @if(config('auth.require_email_confirmation'))<button class="button button-ghost" type="submit" formaction="{{ url('/user/resend-confirmation') }}">Renvoyer la confirmation</button>@endif
        </form><div class="form-footnote">Première visite ? <a href="{{ url('/user/signIn') }}">Créer un compte</a></div>
    </div>
</main>
@endsection
