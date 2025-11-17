{{-- resources/views/game/coinflip.blade.php --}}
<html>

<head>
    @include('shared.header')

    @include('shared.navbar')

    @viteReactRefresh
    @vite(['resources/js/app.jsx'])
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loto Lio - Coin Flip</title>
    <style>
        .progress-wrapper {
            position: relative;
        }

        .progress-wrapper .target-marker {
            position: absolute;
            top: -4px;
            bottom: -4px;
            width: 2px;
            background: rgba(255, 0, 0, 0.8);
            transform: translateX(-1px);
            pointer-events: none;
        }

        .progress-wrapper .target-marker::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 10px;
            height: 10px;
            background: rgba(255, 0, 0, 0.85);
            border-radius: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
</head>
<main class="py-4">
    <div class="container">
        <section class="game-outline text-center">
            <header class="mb-4">
                <div class="d-flex justify-content-center align-items-end gap-2">
                    <h1 class="mb-2">Coin Flip</h1>
                    <div class="info-container">
                        <button id="infoBtn" class="btn btn-secondary p-2">
                            <i class="fa-solid fa-info"></i>
                        </button>

                        <div class="info-popup" id="infoBox">
                            <p class="mb-0">
                                Choisissez un chiffre entre un 100. Le résultat est aléatoire et votre gain dépendra du niveau de risque.
                                plus d'information <a href="/user/support?game=coinflip">ici</a>
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
                    Choisissez votre pourcentage et pariez sur UNDER ou OVER.
                </p>
                <span class="balance">Solde : $<span id="coinflip-balance"
                        data-balance>{{ number_format($playerBalance, 0, '', ' ') }}</span></span>
            </header>

            <section id="betContainer" class="mb-4">
                <h2 class="h5 mb-3">Selectionnez votre mise</h2>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <button class="betToken btn btn-outline-light" data-value="10">10</button>
                    <button class="betToken btn btn-outline-light" data-value="50">50</button>
                    <button class="betToken btn btn-outline-light" data-value="100">100</button>
                    <button class="betToken btn btn-outline-light" data-value="250">250</button>
                    <button class="betToken btn btn-outline-light" data-value="500">500</button>
                </div>
                <p id="selectedBet" class="fw-semibold mt-3">Aucune mise selectionnee</p>
                <div class="d-flex flex-row justify-content-center">
                    <button id="clearBet" class="btn btn-light mt-2 me-3" type="button">Effacer la mise</button>
                    <button id="startRound" class="btn btn-danger mt-2" type="button" @if(!session()->has('user'))
                    disabled @endif>Lancer</button>
                </div>
            </section>

            <section id="gameControls" class="mb-4">
                <div class="d-flex flex-wrap justify-content-center gap-3 align-items-center">
                    <label for="targetInput" class="form-label mb-0">Pourcentage cible</label>
                    <input id="targetInput" type="number" class="form-control form-control-sm" value="50" min="1"
                        max="99" style="width: 90px;">
                    <input id="targetSlider" type="range" class="form-range" value="50" min="1" max="99"
                        style="max-width: 240px;">
                </div>
                <div class="d-flex justify-content-center gap-3 mt-3">
                    <button id="chooseUnder" class="btn btn-outline-light active" type="button"
                        data-choice="under">Under</button>
                    <button id="chooseOver" class="btn btn-outline-light" type="button" data-choice="over">Over</button>
                </div>
                <p id="choiceSummary" class="mt-3">Miser sur UNDER 50 (x2.00)</p>
            </section>

            <section id="gameBoard" class="text-center">
                <div class="progress-wrapper mx-auto" style="max-width: 480px;">
                    <div class="progress" style="height: 36px;">
                        <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%;"
                            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0</div>
                    </div>
                    <div id="targetMarker" class="target-marker"></div>
                </div>
                <p id="rolledValue" class="mt-3 fs-5">Valeur tiree : 0</p>
                <p id="resultMessage" class="mt-2 fw-semibold text-uppercase"></p>
                <div class="d-flex flex-row justify-content-center gap-3 mt-3">
                    <button id="playAnother" class="btn btn-light" type="button" style="display: none;">Nouvelle
                        manche</button>
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
<script defer src="{{ asset('js/coinflip.js') }}"></script>
@include('shared.footer')

</html>