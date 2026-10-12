<section id="games" class="games-section">
    <div class="section-heading"><div><h2>Jeux</h2></div><span class="games-count">10 jeux</span></div>
    <div class="game-filters" role="group" aria-label="Filtrer les jeux"><button class="is-active" data-filter="all" aria-pressed="true">Tous les jeux <span>10</span></button>@foreach(['Cartes','Chance','Risque','Quotidien'] as $category)<button data-filter="{{ $category }}" aria-pressed="false">{{ $category }}</button>@endforeach</div>
    <div class="game-grid">@foreach($catalog as $gameKey=>$game)<a class="game-tile" href="{{ url('/game/'.$gameKey) }}" data-category="{{ $game['category'] }}"><div class="tile-art">@include('shared.game-art')<span class="tile-play" aria-hidden="true">↗</span></div><div class="tile-copy"><div><h3>{{ $game['name'] }}</h3></div><span class="tile-icon" aria-hidden="true">{{ $game['icon'] }}</span></div></a>@endforeach</div>
</section>
