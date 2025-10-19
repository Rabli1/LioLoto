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
</head>
<main class="py-4">
    <div class="container">
        <section class="game-outline text-center">
            <header class="mb-4">
                <h1 class="mb-2">Roulette</h1>
                <p class="mb-3 text-uppercase" style="letter-spacing: 0.12em;">
                    Placer des mises et tenter votre chance !
                </p>
            </header>

            <section>
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
</script>
<script defer src="{{ asset('js/roulette.js') }}"></script>
@include('shared.footer')

</html>