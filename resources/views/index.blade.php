@php($pageTitle = 'Le plaisir de jouer')
@extends('layouts.app')
@section('content')
@php($catalog = require resource_path('games.php'))
<main id="main-content" class="page-width">
    <section class="lobby-hero">
        <div class="hero-copy"><span class="eyebrow"><span class="live-dot"></span> VOTRE NOUVEAU TERRAIN DE JEU</span><h1>La chance a<br>du <em>style.</em></h1><p>Les classiques que vous aimez.<br>Une arcade qui change la donne.</p><div class="hero-actions"><a class="button" href="#games">Explorer les jeux <span aria-hidden="true">↗</span></a><a class="text-link" href="{{ url('/user/support') }}">Comment ça marche <span aria-hidden="true">→</span></a></div><div class="hero-facts"><span><b>10</b> jeux à découvrir</span><span><b>1 000</b> points au départ</span><span><b>100 %</b> gratuit</span></div></div>
        <div class="hero-feature"><span class="hero-orbit orbit-one"></span><span class="hero-orbit orbit-two"></span><div class="hero-feature-label"><span class="eyebrow">LE GRAND CLASSIQUE</span><span class="mini-tag">♠ CARTES</span></div>@include('shared.game-art', ['gameKey'=>'blackjack'])<div class="hero-feature-footer"><div><small>Votre prochaine main</small><h2>Blackjack</h2></div><a href="{{ url('/game/blackjack') }}" class="round-link" aria-label="Jouer au Blackjack">↗</a></div></div>
    </section>
    <div class="lobby-divider"><span><i aria-hidden="true">✦</i> Votre mise, votre rythme</span><span>Des points virtuels. Du vrai plaisir.</span><a href="{{ url('/leaderboard') }}">Voir le classement <span aria-hidden="true">→</span></a></div>
    @include('shared.game-grid')
    <section class="daily-banner"><div><span class="eyebrow">UNE PAUSE QUI FAIT RÉFLÉCHIR</span><h2>Un mot. Un jour.<br>Votre petit défi.</h2><p>Liotodle vous attend, sans mise et avec six chances de briller.</p><a class="button button-light" href="{{ url('/game/liotodle') }}">Jouer au mot du jour <span aria-hidden="true">↗</span></a></div>@include('shared.game-art',['gameKey'=>'liotodle'])</section>
</main>
@endsection
