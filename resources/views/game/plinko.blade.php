<html>

<head>
    @include('shared.header')
    @include('shared.navbar')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lotoli - Plinko</title>
    <link rel="stylesheet" href="{{ asset('css/game.css') }}">
    
    <script src="{{ asset('js/plinko/libraries/matter.js') }}"></script>
    <script src="{{ asset('js/plinko/libraries/p5.js') }}"></script>
    <script src="{{ asset('js/plinko/particle.js') }}"></script>
    <script src="{{ asset('js/plinko/sketch.js') }}"></script>
    <script src="{{ asset('js/plinko/plinko.js') }}"></script>
    <script src="{{ asset('js/plinko/boundaries.js') }}"></script>

</head>

<body>
    <main class="plinko-page py-4">
        <div class="container">
            <section class="game-outline text-center">
                <header class="mb-4">
                    <h1 class="mb-2">Plinko</h1>
                    <p class="mb-3 text-uppercase" style="letter-spacing: 0.12em;">
                        Laissez tomber la balle et voyez où elle atterrit !
                    </p>
                </header>
                
                <div id="canvas-container" style="display: flex; justify-content: center; margin: 20px auto;">
                </div>
            </section>
        </div>
    </main>
</body>
@include('shared.footer')

</html>