{{-- resources/views/game/blackjack.blade.php --}}
<html>
    <head>
        @include('shared.header')
        @include('shared.navbar')
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Loto Lio - Blackjack</title>
        <link rel="stylesheet" href="{{ asset('css/game.css') }}">
    </head>
<main class="blackjack-page py-4">
    <div class="container">
        <section class="game-outline text-center">
            <header class="mb-4">
                <h1 class="mb-2">Blackjack</h1>
                <p class="mb-3 text-uppercase" style="letter-spacing: 0.12em;">
                    Atteignez 21 sans dépasser et battez le croupier!
                </p>
                <span class="balance">Solde : $<span id="blackjack-balance" data-balance>{{ number_format($playerBalance, 0, '', ' ') }}</span></span>
            </header>

            <section id="betContainer" class="mb-4">
                <h2 class="h5 mb-3">Sélectionnez votre mise</h2>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <button class="betToken btn btn-outline-light" data-value="10">10</button>
                    <button class="betToken btn btn-outline-light" data-value="50">50</button>
                    <button class="betToken btn btn-outline-light" data-value="100">100</button>
                    <button class="betToken btn btn-outline-light" data-value="250">250</button>
                    <button class="betToken btn btn-outline-light" data-value="500">500</button>
                </div>
                <p id="selectedBet" class="fw-semibold mt-3">Aucune mise sélectionnée</p>
                <div class="d-flex flex-row justify-content-center">
                    <button id="clearBet" class="btn btn-light mt-2 me-3" type="button">Effacer la mise</button>
                    <button id="placeBet" class="btn btn-danger mt-2" type="button">Placer la mise</button>
                </div>
            </section>

            <section id="gameMat" style="display: none;">
                <div id="betAmount" class="mb-3"></div>

                <div class="mb-4">
                    <h3 class="h6 text-uppercase mb-3">Croupier</h3>
                    <div id="dealerContainer"></div>
                    <p class="mt-3">Total : <span id="dealerSum">0</span></p>
                </div>

                <div class="mb-4">
                    <h3 class="h6 text-uppercase mb-3">Joueur</h3>
                    <div id="playerContainer"></div>
                    <p class="mt-3">Total : <span id="playerSum">0</span></p>
                </div>

                <div class="action-buttons d-flex flex-wrap justify-content-center">
                    <button id="stayButton" class="btn btn-light mt-2 me-3" type="button">Rester</button>
                    <button id="hitButton" class="btn btn-danger mt-2" type="button">Tirer</button>
                </div>

                <div id="resultMessage" class="mt-4 fw-semibold text-uppercase"></div>
            </section>
        </section>
    </div>
</main>

<script>
    window.blackjackSession = {
        userId: {{ session('user')->id ?? 'null' }},
        balance: {{ (int) $playerBalance }},
        endpoints: { saveBalance: '{{ url('game/balance') }}' },
        csrfToken: '{{ csrf_token() }}'
    };
</script>
<script defer src="{{ asset('js/blackjack_balance.js') }}"></script>
<script defer src="{{ asset('js/blackjack.js') }}"></script>
</html>