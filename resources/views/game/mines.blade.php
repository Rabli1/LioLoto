@include('shared.header')
@include('shared.navbar')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loto Lio - Mines</title>
    <link rel="stylesheet" href="{{ asset('css/game.css') }}">
</head>
<main class="blackjack-page py-4">
    <div class="container">
        <section class="blackjack-outline text-center">
            <header class="mb-4">
                <h1 class="mb-2">Mines</h1>
                <p class="mb-3 text-uppercase" style="letter-spacing: 0.12em;">Sélectionnez votre mise, choisissez une difficulté puis révélez les diamants.</p>
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
                <div class="mt-3">
                    <label for="mines-difficulty" class="form-label">Nombre de mines</label>
                    <div class="d-flex justify-content-center gap-2">
                        <input type="number" id="mines-difficulty" class="form-control w-auto text-center" min="1" max="24" value="3">
                    </div>
                    <small class="form-text text-light">Entre 1 et 24. Plus de mines, plus de risque et de gain potentiel.</small>
                </div>
                <p id="selectedBet" class="fw-semibold mt-3">Aucune mise sélectionnée</p>
                <div class="d-flex flex-row justify-content-center">
                    <button id="clearBet" class="btn btn-light mt-2 me-3" type="button">Effacer la mise</button>
                    <button id="placeBet" class="btn btn-danger mt-2" type="button">Placer la mise</button>
                </div>
            </section>

            <section id="mines-game" class="d-none">
                <div class="mb-3">
                    <p class="mb-1">Mise actuelle : <span id="mines-current-bet">0</span></p>
                    <p class="mb-1">Mines actives : <span id="mines-current-difficulty">0</span></p>
                    <p class="mb-1">Multiplicateur actuel : <span id="mines-current-multiplier">1.00x</span></p>
                    <p class="mb-3">Gain potentiel : <span id="mines-potential-payout">0</span> $</p>
                    <p id="mines-message" class="fw-semibold"></p>
                </div>

                <div class="d-flex justify-content-center">
                    <div id="mines-board" class="d-grid gap-2" style="grid-template-columns: repeat(5, 60px); width: max-content;"></div>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" id="mines-cashout" class="btn btn-success">Encaisser</button>
                    <button type="button" id="mines-restart" class="btn btn-light">Nouvelle mise</button>
                </div>
            </section>
        </section>
    </div>
</main>

<script>
    window.minesSession = {
        userId: {{ session('user')->id ?? 'null' }},
        balance: {{ (int) $playerBalance }},
        endpoints: { saveBalance: '{{ url('game/balance') }}' },
        csrfToken: '{{ csrf_token() }}'
    };
    document.title = 'Lio Loto - Mines';
</script>
<script defer src="{{ asset('js/mines.js') }}"></script>
</body>
</html>
