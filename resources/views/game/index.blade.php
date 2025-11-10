<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lio Loto</title>

    <style>
        body {
            background-color: #000;
            color: #fff;
        }
    </style>
</head>

<body>

    @include('shared.header')

    @include('shared.navbar')

    @viteReactRefresh
    @vite(['resources/js/app.jsx'])

    <div class="container mt-5">
        <h1 style="text-align: center; margin-top: 20px;">Jeux</h1>
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="leaderboard-card text-center">
                    <div class="row"><img style="height: 210px;" src='{{ asset("img/blackjack.jpg") }}' alt="BlackJack">
                    </div>
                    <div class="row">
                        <h3>BlackJack</h3>
                    </div>
                    <div class="row"><a href="/game/blackjack" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="leaderboard-card text-center">
                    <div class="row"><img style="height: 210px;" src='{{ asset("img/plinko.webp") }}' alt="Plinko">
                    </div>
                    <div class="row">
                        <h3>Plinko</h3>
                    </div>
                    <div class="row"><a href="/game/plinko" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="leaderboard-card text-center">
                    <div class="row"><img style="height: 210px;" src='{{ asset("img/mines.webp") }}' alt="Mines"></div>
                    <div class="row">
                        <h3>Mines</h3>
                    </div>
                    <div class="row"><a href="/game/mines" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="leaderboard-card text-center">
                    <div class="row"><img style="height: 210px;" src='{{ asset("img/roulette.png") }}' alt="Roulette">
                    </div>
                    <div class="row">
                        <h3>Roulette</h3>
                    </div>
                    <div class="row"><a href="/game/roulette" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="leaderboard-card text-center">
                    <div class="row"><img style="height: 210px;" src='{{ asset("img/slot/card.svg") }}'
                            alt="Machine à sous"></div>
                    <div class="row">
                        <h3>Machine à sous</h3>
                    </div>
                    <div class="row"><a href="/game/slot-machine" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="leaderboard-card text-center">
                    <div class="row"><img style="height: 210px;" src='{{ asset("img/crash.png") }}' alt="Crash"></div>
                    <div class="row">
                        <h3>Crash</h3>
                    </div>
                    <div class="row"><a href="/game/crash" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="leaderboard-card text-center">
                    <div class="row"><img style="height: 210px;" src='{{ asset("img/coinflip.png") }}' alt="Coinflip">
                    </div>
                    <div class="row">
                        <h3>Coinflip</h3>
                    </div>
                    <div class="row"><a href="/game/coinflip" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="leaderboard-card text-center">
                    <div class="row"><img style="height: 210px;" src='{{ asset("img/poker.png") }}' alt="Poker"></div>
                    <div class="row">
                        <h3>Poker</h3>
                    </div>
                    <div class="row"><a href="/game/poker" class="btn btn-danger btn-md">Jouer (beta)</a></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="leaderboard-card text-center">
                    <div class="row"><img style="height: 210px;" src='{{ asset("img/chicken.png") }}'
                            alt="Chicken-Road">
                    </div>
                    <div class="row">
                        <h3>Chicken Road</h3>
                    </div>
                    <div class="row"><a href="/game/chicken-road" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
            </div>
        </div>
    </div>

    </div>
</body>
@include('shared.footer')

</html>
