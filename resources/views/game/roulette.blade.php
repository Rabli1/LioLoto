{{-- resources/views/game/mines.blade.php --}}
<html>

<head>
    @include('shared.header')

    @include('shared.navbar')

    @viteReactRefresh
    @vite(['resources/js/app.jsx'])
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loto Lio - Roulette</title>
    <link rel="stylesheet" href="{{ asset('css/game.css') }}">
    <link rel="stylesheet" href="{{ asset('css/roulette.css') }}">
</head>
<main class="py-4">
    <div class="container">
        <section class="game-outline text-center">

            <div id="endContainer"><div id="endAmount"></div></div>
            

            <header class="mb-4">
                <div class="d-flex justify-content-center align-items-end gap-2">
                    <h1 class="mb-2">Roulette</h1>
                    <div class="info-container">
                        <button id="infoBtn" class="btn btn-secondary p-2">
                            <i class="fa-solid fa-info"></i>
                        </button>

                        <div class="info-popup" id="infoBox">
                            <p class="mb-0">
                                Dans la roulette, misez sur un numéro, une couleur ou une zone. Le résultat dépend du
                                lancer de la bille.
                                plus d'information <a href="/user/support?game=roulette">ici</a>
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
                    Placer des mises et tenter votre chance !
                </p>
            </header>

            <section id="betContainer" class="mb-4">
                <h2 class="h5 mb-3">Sélectionnez votre mise</h2>
                <div class="tokenWage" data-value="10">1</div>
                <div class="tokenWage" data-value="10">10</div>
                <div class="tokenWage" data-value="50">50</div>
                <div class="tokenWage" data-value="100">100</div>
                <div class="tokenWage" data-value="250">250</div>
                <div class="tokenWage" data-value="500">500</div>
                <button id="clearMat" class="btn btn-light" type="button">Effacer les mises</button>
            </section>

            <section>
                <span class="balance">Solde : <span id="roulette-balance"
                        data-balance>{{ number_format($playerBalance, 0, '', ' ') }}</span></span>
                <div id="rouletteContainer"></div>
            </section>

            <section id="betMat">
                <div id="bettingMat">
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
    window.soundAssets = {
        token: '{{ asset('sounds/token.mp3') }}',
        roulette: '{{ asset('sounds/roulette.wav') }}',
        rouletteWin: '{{ asset('sounds/rouletteWin.wav') }}',
        rouletteLose: '{{ asset('sounds/rouletteLose.mp3') }}',
    };
</script>
<script defer src="{{ asset('js/roulette.js') }}"></script>
@include('shared.footer')

</html>