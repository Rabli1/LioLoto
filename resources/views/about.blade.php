@php($pageTitle = 'À propos')
@extends('layouts.app')
@section('content')
<main id="main-content" class="page-width content-page"><h1>À propos</h1><p class="content-intro">LioLoto est une plateforme de jeux créée par quatre étudiants en informatique.</p><div class="content-card"><h2>Points virtuels</h2><p class="content-intro">Les jeux sont gratuits. Les points ne peuvent être achetés ni échangés contre de l’argent.</p></div>
    <div class="section-heading about-team-heading"><h2>L’équipe</h2></div><div class="team-grid">
        @foreach([['Justin Lessard','justin.jpg','Accueil, Plinko et Liotodle'],['Émil Tittley','emil.jpg','Mines, Coin Flip, Chicken Road et machine à sous'],['Thomas Lahaie','thomas.png','Blackjack et Roulette'],['Gabriel Massé','gab.png','Classement, Crash et Poker']] as $member)<article class="content-card"><img src="{{ asset('img/members/'.$member[1]) }}" alt="{{ $member[0] }}" loading="lazy"><h3>{{ $member[0] }}</h3><p>{{ $member[2] }}</p></article>@endforeach
    </div>
</main>
@endsection
