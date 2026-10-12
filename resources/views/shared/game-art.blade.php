<div class="game-art art-{{ $gameKey }}" aria-hidden="true">
@switch($gameKey)
    @case('blackjack') @case('poker')
        <span class="art-card card-left"><b>A</b><i>♠</i></span><span class="art-card card-right"><b>{{ $gameKey === 'poker' ? 'K' : 'J' }}</b><i>♥</i></span><span class="art-chip">{{ $gameKey === 'poker' ? '♥' : '21' }}</span>@break
    @case('plinko')
        <div class="art-pegs">@for($row=0;$row<5;$row++)<div>@for($p=0;$p<$row+3;$p++)<i></i>@endfor</div>@endfor</div><span class="art-ball"></span><span class="art-plinko-bin">×16</span>@break
    @case('mines')
        <div class="art-mine-grid">@for($i=0;$i<9;$i++)<span>@if(in_array($i,[1,4,6]))◇@endif</span>@endfor</div>@break
    @case('roulette')
        <div class="art-wheel"><span>0</span><i>◎</i></div><span class="art-pointer">▼</span>@break
    @case('slot-machine')
        <div class="art-slots"><span>7</span><span>7</span><span>7</span></div><span class="art-slot-glint">✦</span>@break
    @case('crash')
        <svg class="art-graph" viewBox="0 0 280 150"><path class="graph-grid" d="M15 10V135H270 M15 95H270 M15 55H270 M65 10V135 M115 10V135 M165 10V135 M215 10V135"/><path class="graph-fill" d="M15 130C80 130 110 120 145 93S207 53 249 18V135H15Z"/><path class="graph-line" d="M15 130C80 130 110 120 145 93S207 53 249 18"/></svg><span class="art-multiplier">2.48<span>×</span></span>@break
    @case('coinflip')
        <span class="art-coin coin-back">L</span><span class="art-coin coin-front">✦</span>@break
    @case('chicken-road')
        <div class="art-road"><span></span><span></span><span></span></div><span class="art-chicken">🐔</span><span class="art-car">▰</span>@break
    @case('liotodle')
        <div class="art-word">@foreach(str_split('JOUER') as $i=>$letter)<span class="letter-{{ $i }}">{{ $letter }}</span>@endforeach</div><div class="art-word word-shadow"><span>L</span><span>I</span><span>O</span><span>!</span><span>!</span></div>@break
@endswitch
</div>
