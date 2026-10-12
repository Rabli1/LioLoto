@php($pageTitle = 'Connexion')
@extends('layouts.app')
@section('content')
<main id="main-content" class="page-width auth-page">
    <div class="auth-intro"><span class="eyebrow">CONTENT DE VOUS REVOIR</span><h1>Votre prochaine<br>partie vous attend<span class="brand-dot">.</span></h1><p>Retrouvez vos points, vos jeux et votre place dans le classement.</p>@include('shared.game-art',['gameKey'=>'blackjack'])</div>
    <div class="form-card"><h2>Connexion</h2><p>Installez-vous. La table est prête.</p>@include('shared.errors')
        @if($message ?? null)<p class="form-message" role="status">{{ $message }}</p>@endif
        <form method="post" action="{{ url('/user/connection') }}">@csrf
            <label for="username">Nom d’utilisateur</label><input id="username" name="username" type="text" autocomplete="username" maxlength="255" value="{{ old('username', $username ?? '') }}" required>
            <label for="password">Mot de passe</label><input id="password" name="password" type="password" autocomplete="current-password" required>
            <div class="remember-row"><input id="remember" name="remember" type="checkbox" value="1"><label for="remember">Se souvenir de moi</label></div>
            <button class="button" type="submit">Se connecter <span aria-hidden="true">↗</span></button>
            <button class="button button-ghost" type="submit" formaction="{{ url('/user/resend-confirmation') }}">Renvoyer la confirmation</button>
        </form><div class="form-footnote">Première visite ? <a href="{{ url('/user/signIn') }}">Créer un compte</a></div>
    </div>
</main>
@endsection
