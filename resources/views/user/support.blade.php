@php($pageTitle = 'Règles des jeux')
@extends('layouts.app')
@section('content')
@php($catalog = require resource_path('games.php'))
<main id="main-content" class="page-width content-page"><h1>Règles des jeux</h1><p class="content-intro">Les parties utilisent des points virtuels, sans échange contre de l’argent.</p>
    <div class="support-grid">@foreach($catalog as $key=>$item)<details class="rules-card" @if($game === $key) open @endif><summary>{{ $item['icon'] }} {{ $item['name'] }} <span aria-hidden="true">⌄</span></summary><p>{{ $item['rules'] }}</p><a class="small-link" href="{{ url('/game/'.$key) }}">Jouer à {{ $item['name'] }} →</a></details>@endforeach</div>
    <div class="content-card support-extra"><h2>Parties en cours</h2><p class="content-intro">Reprenez une partie en revenant sur son jeu. Terminez ou abandonnez la manche avant d’en lancer une autre. Abandonner entraîne la perte de la mise. Les manches expirent après 24 heures.</p><h2>Liotodle</h2><p class="content-intro">Une partie par jour. Trouver le mot rapporte 300 points.</p></div>
</main>
@endsection
