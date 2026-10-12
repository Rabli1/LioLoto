@php($pageTitle = 'Jeux')
@extends('layouts.app')
@section('content')
@php($catalog = require resource_path('games.php'))
<main id="main-content" class="page-width">
    <section class="lobby-hero">
        <div class="hero-copy"><h1>LioLoto</h1><div class="hero-actions"><a class="button" href="#games">Voir les jeux <span aria-hidden="true">↗</span></a><a class="text-link" href="{{ url('/user/support') }}">Règles des jeux <span aria-hidden="true">→</span></a></div><div class="hero-facts"><span><b>10</b> jeux</span><span><b>1 000</b> points au départ</span></div></div>
        <div class="hero-feature"><span class="hero-orbit orbit-one"></span><span class="hero-orbit orbit-two"></span><div class="hero-feature-label"><span class="mini-tag">♠ CARTES</span></div>@include('shared.game-art', ['gameKey'=>'blackjack'])<div class="hero-feature-footer"><div><h2>Blackjack</h2></div><a href="{{ url('/game/blackjack') }}" class="round-link" aria-label="Jouer au Blackjack">↗</a></div></div>
    </section>
    <div class="lobby-divider"><span>Points virtuels</span><a href="{{ url('/leaderboard') }}">Classement <span aria-hidden="true">→</span></a></div>
    @include('shared.game-grid')
    <section class="daily-banner"><div><h2>Liotodle</h2><p>Un mot de cinq lettres. Six essais par jour.</p><a class="button button-light" href="{{ url('/game/liotodle') }}">Jouer <span aria-hidden="true">↗</span></a></div>@include('shared.game-art',['gameKey'=>'liotodle'])</section>
</main>
@endsection
