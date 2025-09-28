<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lio Loto</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        body {
            background-color: #000;
            color: #fff;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    @include('shared.header')
    @include('shared.navbar')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <div class="container mt-5" style="padding-bottom: 300px;">
        <div class="row">
            <!-- Game -->
            <div class="d-flex flex-row col-lg-6 mb-4">
                <div class="leaderboard-card text-center d-flex flex-column">
                    <div class="row"><img src='{{ asset("img/blackjack.jpg") }}' alt="BlackJack"></div>
                    <div class="row"><h3>BlackJack</h3></div>
                    <div class="row mt-auto"><a href="/game/blackjack" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
                <div class="leaderboard-card text-center ms-5">
                    <div class="row"><img src='{{ asset("img/plinko.webp") }}' alt="Plinko"></div>
                    <div class="row"><h3>Plinko</h3></div>
                    <div class="row mt-auto"><a href="/game/plinko" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
                <div class="leaderboard-card text-center d-flex flex-column ms-5 mw-100">
                    <div class="row"><img src='{{ asset("img/Mines.webp") }}' alt="Plinko"></div>
                    <div class="row"><h3>Mines</h3></div>
                    <div class="row mt-auto"><a href="/game/mines" class="btn btn-danger btn-md">Jouer</a></div>
                </div>
            </div>
        </div>
    </div>
</body>
@include('shared.footer')
</html>