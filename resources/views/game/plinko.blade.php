<html>

<head>
    @include('shared.header')

    @include('shared.navbar')

    @viteReactRefresh
    @vite(['resources/js/app.jsx'])
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lotoli - Plinko</title>
    <link rel="stylesheet" href="{{ asset('css/game.css') }}">
    <link rel="prefetch" href="{{ asset('sounds/plinko.mp3') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.6.0/p5.js"></script>
    <script src="{{ asset('js/plinko/libraries/matter.js') }}"></script>
    <!--<script src="{{ asset('js/plinko/libraries/p5.js') }}"></script>-->

</head>

<body>
    <main class="plinko-page py-4">
        <div class="container">
            <section class="game-outline text-center">
                <header class="mb-4">
                    <div class="d-flex justify-content-center align-items-end gap-2">
                        <h1 class="mb-2">Plinko</h1>
                        <div class="info-container">
                            <button id="infoBtn" class="btn btn-secondary p-2">
                                <i class="fa-solid fa-info"></i>
                            </button>

                            <div class="info-popup" id="infoBox">
                                <p class="mb-0">
                                    Laissez tomber une bille dans un plateau à picots. Elle rebondit aléatoirement
                                    jusqu’à une case de gain.
                                    plus d'information <a href="/user/support?game=plinko">ici</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <script>
                        const btn = document.getElementById("infoBtn");
                        const box = document.getElementById("infoBox");

                        btn.addEventListener("click", () => {
                            box.style.display = (box.style.display === "block") ? "none" : "block";
                        });
                    </script>
                    <p class="mb-3 text-uppercase" style="letter-spacing: 0.12em;">
                        Laissez tomber la balle et voyez où elle atterrit !
                    </p>
                    <span class="balance">Solde : <span id="plinko-balance"
                            data-balance>{{ number_format($playerBalance, 0, '', ' ') }}</span></span>
                </header>

                <div id="canvas-container"
                    style="display: flex; justify-content: center; margin: 20px auto; padding-right: 20%;">
                    <div class="col-md-3" style="padding-right: 20px;">
                        <div id="statsContainer" class="p-3 bg-dark text-white shadow">
                            <h5 class="text-center">Statistiques</h5>
                            <hr class="border-light">
                            <div id="statsList" style="max-height: 400px; overflow-y: auto;"></div>
                            <hr class="border-light">
                            <div class="d-flex justify-content-between">
                                <strong>Total :</strong> <span id="totalGain">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="betContainer" class="mb-4">
                <h2 class="h5 mb-3" style="text-align: center;">Sélectionnez votre mise</h2>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <button class="betToken btn btn-outline-light" data-value="10">10</button>
                    <button class="betToken btn btn-outline-light" data-value="50">50</button>
                    <button class="betToken btn btn-outline-light" data-value="100">100</button>
                    <button class="betToken btn btn-outline-light" data-value="250">250</button>
                    <button class="betToken btn btn-outline-light" data-value="500">500</button>
                </div>
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-3 mt-3">
                    <label for="plinkosCount" class="form-label mb-0">Billes:</label>
                    <select id="plinkosCount" class="form-select form-select-sm" style="width: 90px;">
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <p id="selectedBet" class="fw-semibold mt-3" style="text-align: center;">Aucune mise sélectionnée</p>
                <div class="d-flex flex-row justify-content-center">
                    <button id="clearBet" class="btn btn-light mt-2 me-3" type="button">Effacer la mise</button>
                    <button id="placeBet" class="btn btn-danger mt-2" type="button" @if(!session()->has('user'))
                    disabled @endif>Placer la mise</button>
                </div>
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
        window.soundAssets = {
            plinko: '{{ asset('sounds/plinko.mp3') }}',
            plinkoCash: '{{ asset('sounds/plinkoCash.mp3') }}',
            plinkoBadCash: '{{ asset('sounds/plinkoBadCash.mp3') }}',
            plinkoGoodCash: '{{ asset('sounds/plinkoGoodCash.mp3') }}',
            plinkoBestCash: '{{ asset('sounds/plinkoBestCash.mp3') }}'
        };
    </script>

    <script defer src="{{ asset('js/plinko/plinko.js') }}"></script>
    <script defer src="{{ asset('js/plinko/particle.js') }}"></script>
    <script defer src="{{ asset('js/plinko/sketch.js') }}"></script>
    <script defer src="{{ asset('js/plinko/boundaries.js') }}"></script>

</body>
@include('shared.footer')

</html>