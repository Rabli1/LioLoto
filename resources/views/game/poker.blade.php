@include('shared.header')
@include('shared.navbar')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0"></script>
<link rel="stylesheet" href="{{ asset('css/poker.css') }}">
<main class="blackjack-page py-4">
    <div class="container">
        <section class="game-outline text-center">
            <header class="mb-4">
                <h1 class="mb-2">Crash</h1>
                <p class="mb-3 text-uppercase" style="letter-spacing: 0.12em;">
                    Volez aux autres en gros
                </p>
                <span class="balance" id="balanceUI">
                    Solde : <span data-balance>{{ number_format($playerBalance, 0, '', ' ') }}</span>
                </span>
            </header>
            <section id="gameMat" class="p-3 rounded bg-dark text-center">
                <button class="btn btn-danger">Rejoindre</button>
                <table class="gap-2 w-100 poker">
                    <tr class="poker-row-height">
                        <th></th>
                        <th class="playerSeat">
                            <div>
                                <div>
                                    <i class="fas fa-user text-black"></i>
                                    nom du joueur
                                </div>
                                Solde : 1000
                            </div>
                        </th>
                        <th class="playerSeat">
                            <div>
                                <div>
                                    <i class="fas fa-user text-black"></i>
                                    nom du joueur
                                </div>
                                Solde : 1000
                            </div>
                        </th>
                        <th></th>
                    </tr>
                    <tr class="poker-row-height">
                        <th class="playerSeat">
                            <div>
                                <div>
                                    <i class="fas fa-user text-black"></i>
                                    nom du joueur
                                </div>
                                Solde : 1000
                            </div>
                        </th>
                        <th id="poker-center" colspan="2">
                            <div class="d-flex flex-column align-items-center">
                                <div id="pot" class="mb-2">pot : 2000</div>
                                <div>
                                    <img src="{{ asset('img/cards/2-C.png') }}" class="img-fluid" alt="Card">
                                    <img src="{{ asset('img/cards/2-C.png') }}" class="img-fluid" alt="Card">
                                    <img src="{{ asset('img/cards/2-C.png') }}" class="img-fluid" alt="Card">
                                    <img src="{{ asset('img/cards/2-C.png') }}" class="img-fluid" alt="Card">
                                    <img src="{{ asset('img/cards/2-C.png') }}" class="img-fluid" alt="Card">
                                </div>
                            </div>

                        </th>
                        <th class="playerSeat">
                            <div>
                                <div>
                                    <i class="fas fa-user text-black"></i>
                                    nom du joueur
                                </div>
                                Solde : 1000
                            </div>
                        </th>
                    </tr>
                    <tr class="poker-row-height">
                        <th></th>
                        <th class="playerSeat">
                            <div>
                                <div>
                                    <i class="fas fa-user text-black"></i>
                                    nom du joueur
                                </div>
                                Solde : 1000
                            </div>
                        </th>
                        <th class="playerSeat">
                            <div>
                                <div>
                                    <i class="fas fa-user text-black"></i>
                                    nom du joueur
                                </div>
                                Solde : 1000
                            </div>
                        </th>
                        <th></th>
                    </tr>
                </table>
            </section>
            <div id="bet-section" class="text-white p-4 rounded shadow-sm">
                <h4 class="text-center mb-1">Placez votre mise</h4>
                <div class="mb-3">(200 pour call)</div>
                <div class="row align-items-center mb-4">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <input type="number" id="betAmount" class="form-control" min="25" max="1000" step="5" value="0">
                    </div>
                    <div class="col-md-8">
                        <input type="range" id="betRange" class="form-range" min="25" max="1000" step="5" value="0">
                    </div>
                </div>

                <div class="d-flex justify-content-between gap-2">
                    <button class="btn btn-outline-light w-25 fw-bold">Fold</button>
                    <button class="btn btn-dark w-25 fw-bold">Call</button>
                    <button class="btn btn-danger w-25 fw-bold">Raise</button>
                    <button class="btn btn-outline-danger w-25 fw-bold">All in</button>
                </div>
            </div>
        </section>
    </div>
</main>
@vite(['resources/js/poker.js'])
<script>
    const betInput = document.getElementById('betAmount');
    const betRange = document.getElementById('betRange');
    betInput.addEventListener('input', () => {
        let value = Math.min(Math.max(betInput.value, betRange.min), betRange.max);
        betRange.value = value;
    });
    betRange.addEventListener('input', () => {
        betInput.value = betRange.value;
    });
    window.gameSession = {
        userId: {{ session('user')->id ?? 'null' }},
        balance: {{ (int) $playerBalance }},
        endpoints: { saveBalance: '{{ url('game/balance') }}' },
        csrfToken: '{{ csrf_token() }}',
        encryptKey: '{{ env('APP_ENCRYPTION_KEY') }}'
    };
</script>
@include('shared.footer')

</html>