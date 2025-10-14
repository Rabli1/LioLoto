{{-- resources/views/game/mines.blade.php --}}
<html>
    <head>
        @include('shared.header')
        @include('shared.navbar')
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Loto Lio - Mines</title>
    </head>
<main class="py-4">
    <div class="container">
        <section class="game-outline text-center">
            <header class="mb-4">
                <h1 class="mb-2">Mines</h1>
                <p class="mb-3 text-uppercase" style="letter-spacing: 0.12em;">
                    Ouvrez des cases, encaissez avant la mine !
                </p>
                <span class="balance">Solde : <span id="mines-balance" data-balance>{{ number_format($playerBalance, 0, '', ' ') }}</span></span>
            </header>

            <section id="betContainer" class="mb-4">
                <h2 class="h5 mb-3">Sélectionnez votre mise</h2>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <button class="betToken btn btn-outline-light" data-value="10">10</button>
                    <button class="betToken btn btn-outline-light" data-value="50">50</button>
                    <button class="betToken btn btn-outline-light" data-value="100">100</button>
                    <button class="betToken btn btn-outline-light" data-value="250">250</button>
                    <button class="betToken btn btn-outline-light" data-value="500">500</button>
                </div>
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-3 mt-3">
                    <label for="minesCount" class="form-label mb-0">Mines:</label>
                    <select id="minesCount" class="form-select form-select-sm" style="width: 90px;">
                        @for($i=1; $i<=24; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <p id="selectedBet" class="fw-semibold mt-3">Aucune mise sélectionnée</p>
                <div class="d-flex flex-row justify-content-center">
                    <button id="clearBet" class="btn btn-light mt-2 me-3" type="button">Effacer la mise</button>
                    <button id="placeBet" class="btn btn-danger mt-2" type="button" @if(!session()->has('user')) disabled @endif>Placer la mise</button>
                </div>
            </section>

            <section id="gameMat" style="display: none;">
                <div class="mb-3">
                    <div id="roundInfo" class="small text-muted"></div>
                    <div id="betInfo" class="fw-semibold"></div>
                    <div id="multiplierInfo" class="mt-1"></div>
                    <div id="payoutInfo" class="mt-1"></div>
                </div>

                <div id="minesGrid" class="mines-grid mx-auto"></div>

                <div class="action-buttons d-flex flex-wrap justify-content-center mt-3">
                    <button id="cashoutButton" class="btn btn-success me-3" type="button" disabled>Encaisser</button>
                    <button id="newRoundButton" class="btn btn-light" type="button" style="display: none;">Nouvelle manche</button>
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
<script defer src="{{ asset('js/mines.js') }}"></script>
@include('shared.footer')
</html>
