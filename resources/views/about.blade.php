@php($pageTitle = 'À propos')
@extends('layouts.app')
@section('content')
<main id="main-content" class="page-width content-page"><span class="eyebrow">CRÉÉ AVEC UNE ENVIE DE JOUER</span><h1>Bienvenue chez LioLoto<span class="brand-dot">.</span></h1><p class="content-intro">Une plateforme de jeux créée par quatre étudiants en informatique. Les classiques de l’arcade, les défis quotidiens et le plaisir de grimper dans le classement se retrouvent au même endroit.</p><div class="content-card"><h2>Le plaisir, simplement</h2><p class="content-intro">Vous commencez avec 1 000 points virtuels. Choisissez un jeu, explorez ses règles et jouez à votre rythme. LioLoto est gratuit et les points ne sont jamais de l’argent.</p></div>
    <div class="section-heading about-team-heading"><h2>L’équipe</h2></div><div class="team-grid">
        @foreach([['Justin Lessard','justin.jpg','Accueil, Plinko et Liotodle'],['Émil Tittley','emil.jpg','Mines, Coin Flip, Chicken Road et machine à sous'],['Thomas Lahaie','thomas.png','Blackjack et Roulette'],['Gabriel Massé','gab.png','Classement, Crash et Poker']] as $member)<article class="content-card"><img src="{{ asset('img/members/'.$member[1]) }}" alt="{{ $member[0] }}" loading="lazy"><h3>{{ $member[0] }}</h3><p>{{ $member[2] }}</p></article>@endforeach
    </div>
</main>
@endsection
