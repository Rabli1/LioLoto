{{-- resources/views/game/slot-machine.blade.php --}}
<html>

<head>
    @include('shared.header')

    @include('shared.navbar')

    @viteReactRefresh
    @vite(['resources/js/app.jsx'])
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loto Lio - Slot Machine</title>
</head>
<main class="py-4">
    <div class="container">
        <section class="game-outline text-center">
            <header class="mb-4">
                <h1 class="mb-2">Slot Machine</h1>
                <p class="mb-3 text-uppercase" style="letter-spacing: 0.12em;">
                    Faites tourner la machine et tentez votre chance !
                </p>
                <span class="balance">Solde : <span id="slot-balance"
                        data-balance>{{ number_format($playerBalance, 0, '', ' ') }}</span></span>
            </header>

            <section class="slot-machine-board">
                <div id="slot-reels" class="slot-machine-reels">
                    <div class="slot-reel" data-index="0" data-symbol="">
                        <div class="slot-symbol"></div>
                    </div>
                    <div class="slot-reel" data-index="1" data-symbol="">
                        <div class="slot-symbol"></div>
                    </div>
                    <div class="slot-reel" data-index="2" data-symbol="">
                        <div class="slot-symbol"></div>
                    </div>
                </div>
                <div class="slot-hud mt-3">
                    <span class="slot-stat">Mise : <span id="slot-current-bet">0</span></span>
                    <span class="slot-stat">Dernier gain : <span id="slot-last-win">0</span></span>
                </div>
                <div id="slot-status" class="slot-status mt-3"></div>
            </section>

            <section id="slot-controls" class="mt-4">
                <h2 class="h5 mb-3">Selectionnez votre mise</h2>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <button class="slot-bet-token btn btn-outline-light" data-value="10">10</button>
                    <button class="slot-bet-token btn btn-outline-light" data-value="50">50</button>
                    <button class="slot-bet-token btn btn-outline-light" data-value="100">100</button>
                    <button class="slot-bet-token btn btn-outline-light" data-value="250">250</button>
                    <button class="slot-bet-token btn btn-outline-light" data-value="500">500</button>
                </div>
                <p id="slot-selected-bet" class="fw-semibold mt-3">Aucune mise selectionnee</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <button id="slot-clear-bet" class="btn btn-light" type="button">Effacer la mise</button>
                    <button id="slot-spin" class="btn btn-danger btn-lg" type="button"
                        @if(!session()->has('user')) disabled @endif>Lancer</button>
                </div>
            </section>

            <section class="slot-paytable mt-4">
                <h2 class="h6 mb-3 text-uppercase">Table des gains</h2>
                <div class="slot-paytable-grid">
                    <div class="slot-paytable-row">
                        <span class="slot-paytable-info">
                            <img src="{{ asset('img/slot/diamond.svg') }}" alt="Diamant">
                            <span>3 Diamants</span>
                        </span>
                        <span class="slot-paytable-multiplier">x100</span>
                    </div>
                    <div class="slot-paytable-row">
                        <span class="slot-paytable-info">
                            <img src="{{ asset('img/slot/seven.svg') }}" alt="Sept">
                            <span>3 Sept</span>
                        </span>
                        <span class="slot-paytable-multiplier">x45</span>
                    </div>
                    <div class="slot-paytable-row">
                        <span class="slot-paytable-info">
                            <img src="{{ asset('img/slot/bar.svg') }}" alt="Bar">
                            <span>3 Bar</span>
                        </span>
                        <span class="slot-paytable-multiplier">x25</span>
                    </div>
                    <div class="slot-paytable-row">
                        <span class="slot-paytable-info">
                            <img src="{{ asset('img/slot/cherry.svg') }}" alt="Cerise">
                            <span>3 Cerises</span>
                        </span>
                        <span class="slot-paytable-multiplier">x15</span>
                    </div>
                    <div class="slot-paytable-row">
                        <span class="slot-paytable-info">
                            <img src="{{ asset('img/slot/orange.svg') }}" alt="Orange">
                            <span>3 Oranges</span>
                        </span>
                        <span class="slot-paytable-multiplier">x12</span>
                    </div>
                    <div class="slot-paytable-row">
                        <span class="slot-paytable-info">
                            <img src="{{ asset('img/slot/lemon.svg') }}" alt="Citron">
                            <span>3 Citrons</span>
                        </span>
                        <span class="slot-paytable-multiplier">x10</span>
                    </div>
                    <div class="slot-paytable-row">
                        <span class="slot-paytable-info">
                            <img src="{{ asset('img/slot/cherry.svg') }}" alt="Cerise">
                            <span>2 Cerises</span>
                        </span>
                        <span class="slot-paytable-multiplier">x2</span>
                    </div>
                    <div class="slot-paytable-row">
                        <span class="slot-paytable-info">
                            <img src="{{ asset('img/slot/bar.svg') }}" alt="Bar">
                            <span>2 Bar</span>
                        </span>
                        <span class="slot-paytable-multiplier">x4</span>
                    </div>
                </div>
            </section>
        </section>
    </div>
</main>

<script>
    window.gameSession = {
        userId: {{ session('user')->id ?? 'null' }},
        balance: {{ (int) $playerBalance }},
        endpoints: { saveBalance: '{{ url('game/balance') }}' },
        csrfToken: '{{ csrf_token() }}'
    };
</script>
<script defer src="{{ asset('js/slot_machine.js') }}"></script>
@include('shared.footer')

</html>
