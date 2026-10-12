@php($pageTitle = 'Comment jouer')
@extends('layouts.app')
@section('content')
@php($catalog = require resource_path('games.php'))
<main id="main-content" class="page-width content-page"><span class="eyebrow">ON VOUS EXPLIQUE TOUT</span><h1>À chaque jeu, ses règles<span class="brand-dot">.</span></h1><p class="content-intro">Commencez avec 1 000 points virtuels, choisissez une mise et trouvez votre jeu préféré. Les points ne peuvent être achetés ni échangés contre de l’argent.</p>
    <div class="support-grid">@foreach($catalog as $key=>$item)<details class="rules-card" @if($game === $key) open @endif><summary>{{ $item['icon'] }} {{ $item['name'] }} <span aria-hidden="true">⌄</span></summary><p>{{ $item['rules'] }}</p><a class="small-link" href="{{ url('/game/'.$key) }}">Jouer à {{ $item['name'] }} →</a></details>@endforeach</div>
    <div class="content-card support-extra"><h2>Une manche à la fois</h2><p class="content-intro">Vous pouvez reprendre une partie en revenant sur son jeu. Terminez ou abandonnez votre manche avant d’en lancer une autre. Abandonner entraîne la perte de la mise. Les manches en cours expirent après 24 heures.</p><h2>Une nouvelle journée</h2><p class="content-intro">Lors du renouvellement quotidien, les soldes reviennent à 1 000 points et les trois meilleurs joueurs reçoivent une médaille. Le défi Liotodle se joue une fois par jour et un mot trouvé rapporte 300 points.</p></div>
</main>
@endsection
