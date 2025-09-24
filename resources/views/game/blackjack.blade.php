<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lio Loto</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset("js/blackjack.js") }}"></script>

    <style>
        body {
            background-color: #000;
            color: #fff;
        }

        div{
            text-align: center;
        }

        #dealerContainer img {
            height: 175px;
            width: 125px;
            margin: 1px;
        }

        #dealerContainer {
            margin-bottom: 300px;
        }

        #playerContainer img {
            height: 175px;
            width: 125px;
            margin: 1px;
        }

        #hit {
            width: 100px;
            height: 50px;
            font-size: 20px;
        }

        #stay {
            width: 100px;
            height: 50px;
            font-size: 20px;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    @include('shared.header')
    @include('shared.navbar')

    <div id="betContainer" class="mb-3">
        <label class="form-label">Votre mise :</label>
        <div id="betTokens">
            <button class="btn btn-warning betToken" data-value="25">25</button>
            <button class="btn btn-primary betToken" data-value="50">50</button>
            <button class="btn btn-success betToken" data-value="100">100</button>
        </div>
        <div class="mt-2">
            <span id="selectedBet" class="fw-bold"></span>
            <button id="placeBet" class="btn btn-success ms-2">Valider la mise</button>
        </div>
    </div>

    <div id="gameMat">
        <h2>Main du Croupier: <span id="dealerSum"></span></h2>

        <div id="dealerContainer"></div>

        

        <h2>Votre Main: <span id="playerSum"></span></h2>

        <div id="playerContainer"></div>

        <button id="hitButton" class="btn btn-primary mt-3">Hit</button>
        <button id="stayButton" class="btn btn-danger mt-3">Stay</button>
    </div>
    <div id="betAmount"></div>
</body>

</html>