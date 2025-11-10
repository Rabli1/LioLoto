@include('shared.header')
@include('shared.navbar')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0"></script>
<link rel="stylesheet" href="{{ asset('css/poker.css') }}">
<main class="blackjack-page py-4">
    <div class="container">
        <section class="game-outline text-center">
            <header class="mb-4">
                <h1 class="mb-2">Poker</h1>
                <p class="mb-3 text-uppercase" style="letter-spacing: 0.12em;">
                    Doit avoir au moins 250 points pour jouer
                </p>
                <span class="balance" id="balanceUI">
                    Solde : <span data-balance>{{ number_format($playerBalance, 0, '', ' ') }}</span>
                </span>
            </header>
            <section id="gameMat" class="p-3 rounded bg-dark text-center">
                @if(session()->has('user'))
                    <button class="btn btn-danger" id="join-button">Rejoindre</button>
                    <button class="btn btn-danger" id="quit-button">Quitter</button>

                @else
                    <a href="{{ url('/user/connection') }}" class="btn btn-danger">Connectez-vous pour rejoindre</a>
                @endif
                <div id="game-message" class="text-white"></div>
                <table class="gap-2 w-100 poker mt-4">
                    <tr class="poker-row-height">
                        <th></th>
                        <th class="playerSeat">
                            <div>
                                <div>
                                    <i class="fas fa-user text-black player-icon"></i>
                                    <span class="player-name">nom du joueur</span>
                                </div>
                                <div class="player-balance">Solde : 1000</div>
                                <div class="player-cards">
                                    <img src="{{ asset('img/cards/BACK.png') }}" class="img-fluid" alt="Card">
                                    <img src="{{ asset('img/cards/BACK.png') }}" class="img-fluid" alt="Card">
                                </div>
                                <div class="hand-name fw-bold"></div>
                            </div>
                        </th>
                        <th class="playerSeat">
                            <div>
                                <div>
                                    <i class="fas fa-user text-black player-icon"></i>
                                    <span class="player-name">nom du joueur</span>
                                </div>
                                <div class="player-balance">Solde : 1000</div>
                                <div class="player-cards">
                                    <img src="{{ asset('img/cards/BACK.png') }}" class="img-fluid" alt="Card">
                                    <img src="{{ asset('img/cards/BACK.png') }}" class="img-fluid" alt="Card">
                                </div>
                                <div class="hand-name fw-bold"></div>
                            </div>
                        </th>
                        <th></th>
                    </tr>
                    <tr class="poker-row-height">
                        <th class="playerSeat">
                            <div>
                                <div>
                                    <i class="fas fa-user text-black player-icon"></i>
                                    <span class="player-name">nom du joueur</span>
                                </div>
                                <div class="player-balance">Solde : 1000</div>
                                <div class="player-cards">
                                    <img src="{{ asset('img/cards/BACK.png') }}" class="img-fluid" alt="Card">
                                    <img src="{{ asset('img/cards/BACK.png') }}" class="img-fluid" alt="Card">
                                </div>
                                <div class="hand-name fw-bold"></div>
                            </div>
                        </th>
                        <th id="poker-center" colspan="2">
                            <div class="d-flex flex-column align-items-center">
                                <div id="pot" class="mb-2">pot : 2000</div>
                                <div id="community-cards">
                                    <img src="{{ asset('img/cards/A-H.png') }}" class="img-fluid" alt="Card">
                                    <img src="{{ asset('img/cards/K-H.png') }}" class="img-fluid" alt="Card">
                                    <img src="{{ asset('img/cards/Q-H.png') }}" class="img-fluid" alt="Card">
                                    <img src="{{ asset('img/cards/J-H.png') }}" class="img-fluid" alt="Card">
                                    <img src="{{ asset('img/cards/10-H.png') }}" class="img-fluid" alt="Card">
                                </div>
                                <div class="hand-name"></div>
                            </div>

                        </th>
                        <th class="playerSeat">
                            <div>
                                <div>
                                    <i class="fas fa-user text-black player-icon"></i>
                                    <span class="player-name">nom du joueur</span>
                                </div>
                                <div class="player-balance">Solde : 1000</div>
                                <div class="player-cards">
                                    <img src="{{ asset('img/cards/BACK.png') }}" class="img-fluid" alt="Card">
                                    <img src="{{ asset('img/cards/BACK.png') }}" class="img-fluid" alt="Card">
                                </div>
                                <div class="hand-name fw-bold"></div>
                            </div>
                        </th>
                    </tr>
                    <tr class="poker-row-height">
                        <th></th>
                        <th class="playerSeat">
                            <div>
                                <div>
                                    <i class="fas fa-user text-black player-icon"></i>
                                    <span class="player-name">nom du joueur</span>
                                </div>
                                <div class="player-balance">Solde : 1000</div>
                                <div class="player-cards">
                                    <img src="{{ asset('img/cards/BACK.png') }}" class="img-fluid" alt="Card">
                                    <img src="{{ asset('img/cards/BACK.png') }}" class="img-fluid" alt="Card">
                                </div>
                                <div class="hand-name fw-bold"></div>
                            </div>
                        </th>
                        <th class="playerSeat">
                            <div>
                                <div>
                                    <i class="fas fa-user text-black player-icon"></i>
                                    <span class="player-name">nom du joueur</span>
                                </div>
                                <div class="player-balance">Solde : 1000</div>
                                <div class="player-cards">
                                    <img src="{{ asset('img/cards/BACK.png') }}" class="img-fluid" alt="Card">
                                    <img src="{{ asset('img/cards/BACK.png') }}" class="img-fluid" alt="Card">
                                </div>
                                <div class="hand-name fw-bold"></div>
                            </div>
                        </th>
                        <th></th>
                    </tr>
                </table>
            </section>
            <div id="bet-section" class="text-white p-4 rounded shadow-sm">
                <h4 class="text-center mb-1">Placez votre mise</h4>
                <div id="required-call">(200 pour call)</div>
                <div class="progress">
                    <div class="progress-bar bg-danger" role="progressbar"
                        style="width: 0%;">
                    </div>
                </div>
                <div id="poker-error" class="text-danger mb-2"></div>
                <div class="row align-items-center mb-4">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <input type="number" id="betAmount" class="form-control" min="25" max="1000" step="5" value="0">
                    </div>
                    <div class="col-md-8">
                        <input type="range" id="betRange" class="form-range" min="25" max="1000" step="5" value="0">
                    </div>
                </div>

                <div class="d-flex justify-content-between gap-2">
                    <button class="btn btn-outline-light w-25 fw-bold" id="fold-button">Fold</button>
                    <button class="btn btn-dark w-25 fw-bold" id="check-button">Check</button>
                    <button class="btn btn-danger w-25 fw-bold" id="call-button">Call</button>
                    <button class="btn btn-outline-danger w-25 fw-bold" id="raise-button">Raise</button>
                </div>
            </div>
        </section>
    </div>
</main>
<script defer src="{{ asset('js/poker.js') }}"></script>
<script>
    const betInput = document.getElementById('betAmount');
    const betRange1 = document.getElementById('betRange');
    betInput.addEventListener('input', () => {
        let value = Math.min(Math.max(betInput.value, betRange1.min), betRange1.max);
        betRange1.value = value;
    });
    betRange1.addEventListener('input', () => {
        betInput.value = betRange1.value;
    });
    window.gameSession = {
        userId: {{ session('user')->id ?? 'null' }},
        balance: {{ (int) $playerBalance }},
        endpoints: { saveBalance: '{{ url('game/balance') }}' },
        csrfToken: '{{ csrf_token() }}',
    };
</script>
@include('shared.footer')

</html>