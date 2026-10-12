@php($pageTitle = 'Modifier le mot de passe')
@extends('layouts.app')
@section('content')
<main id="main-content" class="page-width auth-page">
    <div class="auth-intro"><span class="eyebrow">VOTRE COMPTE</span><h1>Une nouvelle<br>phrase secrète<span class="brand-dot">.</span></h1><p>Choisissez un mot de passe unique d’au moins 12 caractères.</p><a class="small-link" href="{{ url('/user/profile') }}?id={{ session('user')->id }}">← Retour au profil</a></div>
    <div class="form-card"><h2>Modifier le mot de passe</h2>@include('shared.errors')
        <form action="{{ route('user.updatePassword') }}" method="post">@csrf
            <label for="current_password">Mot de passe actuel</label><input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
            <label for="new_password">Nouveau mot de passe</label><input id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="12" maxlength="72" required>
            <label for="new_password_confirmation">Confirmer le nouveau mot de passe</label><input id="new_password_confirmation" name="new_password_confirmation" type="password" autocomplete="new-password" minlength="12" maxlength="72" required>
            <button class="button" type="submit">Enregistrer le mot de passe</button>
        </form>
    </div>
</main>
@endsection
