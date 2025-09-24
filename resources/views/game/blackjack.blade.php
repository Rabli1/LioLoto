<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lio Loto - Blackjack</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="{{ asset('js/blackjack_alt.js') }}"></script>
    <style>
        :root {
            --page-bg: #050505;
            --table-bg: linear-gradient(165deg, #111 0%, #090909 45%, #141414 100%);
            --accent: #d7263d;
            --accent-soft: rgba(215, 38, 61, 0.14);
            --text-main: #f5f5f5;
            --text-muted: rgba(255, 255, 255, 0.6);
            --card-bg: #fefefe;
            --chip-white: #ffffff;
        }

        body {
            min-height: 100vh;
            background: radial-gradient(circle at top, #1a1a1d 0%, #080808 75%, var(--page-bg) 100%);
            color: var(--text-main);
            font-family: 'Segoe UI', 'Inter', system-ui, -apple-system, sans-serif;
        }

        .blackjack-page {
            padding: 2.5rem 0 4rem;
        }

        .table-shell {
            background: var(--table-bg);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.65);
            overflow: hidden;
            position: relative;
        }

        .table-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top, rgba(215, 38, 61, 0.15), transparent 55%);
            pointer-events: none;
        }

        .table-header {
            position: relative;
            padding: 2rem 2.25rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .table-header h1 {
            font-size: 2rem;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            margin: 0;
        }

        .table-header p {
            margin: 0.5rem 0 0;
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .score-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.4rem 0.95rem;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .hand-section {
            position: relative;
            padding: 1.75rem 2.25rem;
        }

        .section-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.22em;
            color: var(--text-muted);
            margin-bottom: 0.65rem;
        }

        #dealerContainer,
        #playerContainer {
            display: flex;
            flex-direction: row;
            gap: 0.85rem;
            justify-content: center;
            min-height: 180px;
        }

        #dealerContainer img,
        #playerContainer img {
            width: 120px;
            height: 168px;
            border-radius: 12px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.45);
            background: var(--card-bg);
            border: 2px solid rgba(0, 0, 0, 0.18);
        }

        .control-bar {
            padding: 1.5rem 2.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }

        .action-btn {
            min-width: 140px;
            padding: 0.85rem 1.75rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-main);
            background: transparent;
            transition: transform 0.18s ease, background 0.18s ease, border 0.18s ease;
        }

        .action-btn.primary {
            background: var(--accent);
            border-color: rgba(255, 255, 255, 0.25);
        }

        .action-btn.secondary {
            background: rgba(255, 255, 255, 0.08);
        }

        .action-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .action-btn:not(:disabled):hover {
            transform: translateY(-3px);
            background: rgba(215, 38, 61, 0.82);
            border-color: rgba(255, 255, 255, 0.35);
        }

        .betting-panel {
            padding: 2rem 2.25rem 2.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.35);
        }

        .betting-panel h2 {
            font-size: 1.1rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin-bottom: 1.2rem;
        }

        .chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.9rem;
            justify-content: center;
            margin-bottom: 1.2rem;
        }

        .betToken {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            border: 4px solid rgba(0, 0, 0, 0.2);
            color: #000;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
            background: var(--chip-white);
        }

        .betToken:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(215, 38, 61, 0.35);
        }

        #selectedBet {
            font-size: 1.15rem;
            color: var(--accent);
        }

        #betAmount,
        #selectedBet {
            min-height: 1.2rem;
        }

        .bet-actions button {
            min-width: 140px;
            border-radius: 999px;
        }

        .status-banner {
            padding: 0.8rem 1.4rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.08);
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .split-hand {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
        }

        .split-hand.active-hand {
            border: 1px solid var(--accent);
            box-shadow: 0 0 0 2px rgba(215, 38, 61, 0.25);
        }

        .hand-cards {
            display: inline-flex;
            gap: 0.75rem;
        }

        .hand-total {
            font-size: 0.85rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        @media (max-width: 991.98px) {
            .table-header,
            .hand-section,
            .control-bar,
            .betting-panel {
                padding: 1.5rem 1.25rem;
            }

            #dealerContainer img,
            #playerContainer img {
                width: 100px;
                height: 140px;
            }

            .action-btn {
                min-width: 120px;
            }
        }
    </style>
</head>

<body>
    @include('shared.header')
    @include('shared.navbar')

    <main class="blackjack-page">
        <div class="container">
            <section class="table-shell">
                <header class="table-header text-center text-lg-start">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-7">
                            <h1>Blackjack</h1>
                            <p>Le but est d'atteindre 21 sans le d??passer. Le croupier tire sur 16 et reste sur 17.</p>
                        </div>
                        <div class="col-lg-5 text-lg-end">
                            <span class="score-pill ms-2">Solde: $<span id="playerBalance">{{ number_format($playerBalance, 0, '', ' ') }}</span></span>
                        </div>
                    </div>
                </header>

                <section class="hand-section">
                    <div class="section-label text-center">
                        <span class="score-pill">Compte du croupier: 
                            <span id="dealerSum">0</span>
                        </span>
                    </div>
                    <div id="dealerContainer"></div>
                </section>

                <section class="hand-section">
                    <div class="section-label text-center">
                        <span class="score-pill ms-2">Votre total: 
                            <span id="playerSum">0</span>
                        </span>
                    </div>
                    <div id="playerContainer"></div>
                </section>

                <div class="text-center pb-3">
                    <span id="betAmount" class="status-banner">Mise actuelle: 0</span>
                </div>

                <div class="control-bar">
                    <button id="hit" class="action-btn primary" disabled>Tirer</button>
                    <button id="stay" class="action-btn secondary" disabled>Rester</button>
                    <button id="split" class="action-btn primary" disabled>Split</button>
                    <button id="double" class="action-btn secondary" disabled>Double</button>
                </div>

                <div class="betting-panel text-center">
                    <h2>Choisissez votre mise</h2>
                    <div id="betContainer">
                        <div id="betTokens" class="chip-row">
                            <button class="betToken" data-value="25">25</button>
                            <button class="betToken" data-value="50">50</button>
                            <button class="betToken" data-value="100">100</button>
                            <button class="betToken" data-value="250">250</button>
                            <button class="betToken" data-value="500">500</button>
                        </div>
                        <div class="text-center mt-3">
                            <button id="clearBet" class="action-btn secondary" type="button">Effacer</button>
                        </div>
                        <div class="bet-actions mt-3 d-flex flex-column flex-sm-row gap-3 justify-content-center align-items-center">
                            <span id="selectedBet" class="fw-bold">Aucune mise</span>
                            <button id="placeBet" class="btn btn-outline-light" type="button">Valider</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script>
        window.blackjackSession = {
            userId: {{ session('user')->id ?? 'null' }},
            balance: {{ (int) $playerBalance }},
            endpoints: {
                saveBalance: '{{ url('game/balance') }}'
            }
        };
    </script>
</body>

</html>




