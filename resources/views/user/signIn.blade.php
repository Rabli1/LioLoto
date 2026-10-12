@php($pageTitle = 'Créer un compte')
@extends('layouts.app')
@section('content')
<main id="main-content" class="page-width auth-page">
    <div class="auth-intro"><h1>Créer un compte</h1>@include('shared.game-art',['gameKey'=>'coinflip'])</div>
    <div class="form-card">@include('shared.errors')
        <form method="post" action="{{ url('/user/addUser') }}" data-registration>@csrf
            <label for="username">Nom d’utilisateur</label><input id="username" name="username" type="text" autocomplete="username" minlength="4" maxlength="20" pattern="[A-Za-z0-9_-]{4,20}" value="{{ old('username') }}" required aria-describedby="username-hint"><small id="username-hint" class="form-hint">4 à 20 caractères : lettres, chiffres, tirets et traits de soulignement.</small>
            <label for="email">Courriel{{ config('auth.require_email_confirmation') ? '' : ' (facultatif)' }}</label><input id="email" name="email" type="email" autocomplete="email" maxlength="254" value="{{ old('email') }}" @required(config('auth.require_email_confirmation'))>@if(config('auth.require_email_confirmation'))<small class="form-hint">Confirmez votre compte avec le lien reçu par courriel.</small>@endif
            <label for="password">Mot de passe</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="12" maxlength="72" required aria-describedby="password-hint"><small id="password-hint" class="form-hint">Au moins 12 caractères.</small>
            <label for="confirmPassword">Confirmer le mot de passe</label><input id="confirmPassword" name="confirmPassword" type="password" autocomplete="new-password" minlength="12" maxlength="72" required>
            <button class="button" type="submit">Créer un compte <span aria-hidden="true">↗</span></button>
        </form><div class="form-footnote">Déjà un compte ? <a href="{{ url('/user/connection') }}">Se connecter</a></div>
    </div>
</main>
@endsection
