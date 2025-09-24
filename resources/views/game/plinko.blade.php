<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lio Loto - Plinko</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="{{ asset('js/plinko.js') }}"></script>

    <style>
        :root {
            --page-bg: #030303;
            --panel-bg: linear-gradient(160deg, #111 0%, #080808 60%, #161616 100%);
            --accent: #d7263d;
            --accent-soft: rgba(215, 38, 61, 0.2);
            --accent-strong: rgba(215, 38, 61, 0.55);
            --text-main: #f6f6f6;
            --text-muted: rgba(255, 255, 255, 0.65);
            --board-border: rgba(255, 255, 255, 0.12);
            --pin-color: rgba(255, 255, 255, 0.35);
        }

        body {
            min-height: 100vh;
            background: radial-gradient(circle at top, #17171c 0%, #050506 70%, var(--page-bg) 100%);
            color: var(--text-main);
            font-family: 'Segoe UI', 'Inter', system-ui, -apple-system, sans-serif;
        }

        .plinko-page {
            padding: 2.5rem 0 4rem;
        }

        .plinko-shell {
            background: var(--panel-bg);
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 65px rgba(0, 0, 0, 0.65);
            overflow: hidden;
            position: relative;
        }

        .plinko-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top, rgba(215, 38, 61, 0.18), transparent 58%);
            pointer-events: none;
        }

        .plinko-header {
            position: relative;
            padding: 2rem 2.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .plinko-header h1 {
            font-size: 2rem;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            margin: 0;
        }

        .plinko-header p {
            margin: 0.6rem 0 0;
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .balance-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.2rem;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--text-main);
            font-weight: 600;
        }

        .controls-grid {
            padding: 1.75rem 2.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
        }

        .control-card {
            background: rgba(255, 255, 255, 0.04);
            border-radius: 18px;
            padding: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .control-card h2 {
            font-size: 0.95rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .difficulty-select select,
        .size-select input[type="range"],
        .bet-input input[type="number"] {
            width: 100%;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-main);
            border-radius: 999px;
            padding: 0.65rem 1rem;
        }

        .bet-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.7rem;
        }

        .bet-chip {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 4px solid rgba(0, 0, 0, 0.28);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
            background: rgba(255, 255, 255, 0.9);
            color: #111;
        }

        .bet-chip:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(215, 38, 61, 0.35);
        }

        .bet-chip.active {
            background: var(--accent);
            color: var(--text-main);
            box-shadow: 0 10px 28px rgba(215, 38, 61, 0.45);
        }

        .board-wrapper {
            padding: 0 2.5rem 2.5rem;
        }

        .board-surface {
            background: rgba(255, 255, 255, 0.04);
            border-radius: 24px;
            padding: 2rem;
            border: 1px solid var(--board-border);
            position: relative;
            overflow: hidden;
            min-height: 420px;
        }

        #plinko-board {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .pin-row {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            position: relative;
        }

        .pin {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--pin-color);
            box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.45);
        }

        .multiplier-row {
            display: grid;
            gap: 0.65rem;
            margin-top: 2rem;
            text-align: center;
        }

        .multiplier-slot {
            padding: 0.65rem 0.5rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            font-weight: 600;
            transition: transform 0.18s ease, background 0.18s ease;
        }

        .multiplier-slot.win {
            background: var(--accent);
            transform: translateY(-6px);
        }

        .ball {
            position: absolute;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 10px rgba(215, 38, 61, 0.65);
            pointer-events: none;
        }

        .status-line {
            text-align: center;
            padding: 1.5rem 2.5rem 0;
        }

        .status-banner {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.85rem 1.6rem;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.22);
            color: var(--text-muted);
        }

        .history-panel {
            padding: 1.75rem 2.5rem 2.5rem;
        }

        .history-panel h2 {
            font-size: 0.95rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        #plinko-history {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            max-height: 210px;
            overflow-y: auto;
        }

        .history-entry {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 1.1rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(255, 255, 255, 0.04);
            font-size: 0.9rem;
        }

        .history-entry.win {
            border-color: var(--accent-strong);
            background: rgba(215, 38, 61, 0.14);
        }

        .history-entry.loss {
            opacity: 0.75;
        }

        .drop-button {
            width: 100%;
            border-radius: 999px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 0.9rem 1.5rem;
            background: var(--accent);
            border: 1px solid rgba(255, 255, 255, 0.16);
            color: var(--text-main);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .drop-button:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .drop-button:not(:disabled):hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 34px rgba(215, 38, 61, 0.35);
        }

        .hand-cards {
            display: inline-flex;
            gap: 0.75rem;
        }

        @media (max-width: 991.98px) {
            .plinko-header,
            .controls-grid,
            .board-wrapper,
            .status-line,
            .history-panel {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }

            .board-surface {
                padding: 1.5rem;
                min-height: 360px;
            }
        }
    </style>
</head>

<body>
    @include('shared.header')
    @include('shared.navbar')

    <main class="plinko-page">
        <div class="container">
            <section class="plinko-shell">
                <header class="plinko-header">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3">
                        <div>
                            <h1>Plinko</h1>
                            <p>Lachez la bille, ciblez les multiplicateurs, et tentez de gravir les gains.</p>
                        </div>
                        <div class="balance-pill">Solde&nbsp;: $<span id="plinko-balance">{{ number_format($playerBalance ?? 0, 0, '', ' ') }}</span></div>
                    </div>
                </header>

                <section class="controls-grid">
                    <div class="control-card difficulty-select">
                        <h2>DIFFICULTE</h2>
                        <select id="plinko-difficulty" class="form-select">
                            <option value="easy">Facile</option>
                            <option value="medium" selected>Moyen</option>
                            <option value="hard">Difficile</option>
                        </select>
                    </div>

                    <div class="control-card size-select">
                        <h2>TAILLE</h2>
                        <label for="plinko-size" class="form-label text-uppercase text-muted small">Nombre de rangees <span id="plinko-size-label" class="ms-1">8</span></label>
                        <input type="range" id="plinko-size" min="6" max="12" value="8">
                    </div>

                    <div class="control-card bet-select">
                        <h2>MISE</h2>
                        <div class="bet-chips mb-3">
                            <button class="bet-chip" data-bet="10">10</button>
                            <button class="bet-chip" data-bet="25">25</button>
                            <button class="bet-chip" data-bet="50">50</button>
                            <button class="bet-chip" data-bet="100">100</button>
                            <button class="bet-chip" data-bet="250">250</button>
                        </div>
                        <div class="bet-input">
                            <input type="number" id="plinko-bet" class="form-control" placeholder="Mise personnalisee" min="1" step="1">
                        </div>
                    </div>

                    <div class="control-card action-select">
                        <h2>LANCEMENT</h2>
                        <button id="plinko-drop" class="drop-button" type="button" disabled>Lancer la bille</button>
                        <div class="mt-3 small text-muted">Chaque lancement coute la mise selectionnee. Les gains ou pertes sont automatiquement appliques au solde.</div>
                    </div>
                </section>

                <section class="board-wrapper">
                    <div class="board-surface">
                        <div id="plinko-board"></div>
                        <div id="plinko-multipliers" class="multiplier-row"></div>
                    </div>
                </section>

                <section class="status-line">
                    <div id="plinko-status" class="status-banner">Selectionnez une mise et appuyez sur Lancer pour demarrer.</div>
                </section>

                <section class="history-panel">
                    <h2>Historique</h2>
                    <div id="plinko-history"></div>
                </section>
            </section>
        </div>
    </main>

    <script>
        window.plinkoSession = {
            userId: {{ session('user')->id ?? 'null' }},
            balance: {{ (int) ($playerBalance ?? 0) }},
            endpoints: {
                saveBalance: '{{ url('game/balance') }}'
            },
            csrfToken: '{{ csrf_token() }}'
        };
    </script>
</body>

</html>
