{{-- resources/views/game/chicken-road.blade.php --}}
<html>

<head>
    @include('shared.header')

    @include('shared.navbar')

    @viteReactRefresh
    @vite(['resources/js/app.jsx'])
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loto Lio - Chicken Road</title>
    <link rel="stylesheet" href="{{ asset('css/chicken-road.css') }}">
    <script></script>
</head>

<body>
    <main class="chicken-road-page py-4">
        <div class="container">
            <section class="game-outline text-center">
                <header class="mb-4">
                    <div class="d-flex justify-content-center align-items-end gap-2">
                        <h1 class="mb-2">Chicken Road</h1>
                        <div class="info-container">
                            <button id="infoBtn" class="btn btn-secondary p-2">
                                <i class="fa-solid fa-info"></i>
                            </button>

                            <div class="info-popup" id="infoBox">
                                <p class="mb-0">
                                    Aidez la poule à traverser en évitant les obstacles. Plus elle avance, plus vos
                                    récompenses augmentent.
                                    plus d'information <a href="/user/support?game=chicken_road">ici</a>
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
                        Choisissez la voie, esquivez les mines, encaissez avant le crash !
                    </p>
                    <span class="balance">Solde :
                        <span id="chicken-balance" data-balance>{{ number_format($playerBalance, 0, '', ' ') }}</span>
                    </span>
                </header>

                <section id="betContainer" class="mb-4">
                    <h2 class="h5 mb-3 text-uppercase">Selectionnez votre mise</h2>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <button class="betToken btn btn-outline-light" data-value="10">10</button>
                        <button class="betToken btn btn-outline-light" data-value="50">50</button>
                        <button class="betToken btn btn-outline-light" data-value="100">100</button>
                        <button class="betToken btn btn-outline-light" data-value="250">250</button>
                        <button class="betToken btn btn-outline-light" data-value="500">500</button>
                    </div>

                    <div class="d-flex flex-wrap justify-content-center align-items-center gap-3 mt-3">
                        <label for="difficultyLevel" class="form-label mb-0">Difficulte :</label>
                        <select id="difficultyLevel" class="form-select form-select-sm">
                            <option value="1">Facile</option>
                            <option value="2">Medium</option>
                            <option value="3">Difficile</option>
                        </select>
                    </div>

                    <p id="selectedBet" class="fw-semibold mt-3">Aucune mise selectionnee</p>

                    <div class="d-flex flex-row justify-content-center">
                        <button id="clearBet" class="btn btn-light mt-2 me-3" type="button">Effacer la mise</button>
                        <button id="placeBet" class="btn btn-danger mt-2" type="button" @if(!session()->has('user'))
                        disabled @endif>Placer la mise</button>
                    </div>
                </section>

                <section id="roadMat" style="display: none;">
                    <div class="hud">
                        <div id="roundInfo" class="badge"></div>
                        <div id="progressInfo"></div>
                        <div id="multiplierInfo"></div>
                        <div id="nextMultiplierInfo"></div>
                        <div id="payoutInfo"></div>
                    </div>

                    <div class="road-viewport">
                        <div id="roadTrack" class="road-track"></div>
                    </div>

                    <div class="action-buttons d-flex flex-wrap justify-content-center gap-3 mt-3">
                        <button id="cashoutButton" class="btn btn-success" type="button" disabled>Encaisser</button>
                        <button id="newRoundButton" class="btn btn-light" type="button" style="display: none;">Nouvelle
                            manche</button>
                    </div>

                    <div id="resultMessage" class="mt-3 fw-semibold text-uppercase"></div>
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
    <script defer src="{{ asset('js/chicken_road.js') }}"></script>
    @include('shared.footer')
</body>

</html>