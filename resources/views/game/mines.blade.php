@include('shared.header')
@include('shared.navbar')

<main class="mines-page py-4">
    <div class="container">
        <section class="mines-outline text-center">
            <header class="mb-4">
                <h1 class="mb-2">Mines</h1>
                <p class="mb-3 text-uppercase" style="letter-spacing: 0.12em;">Selectionnez votre mise, choisissez une difficulte puis revelez les diamants.</p>
                <span class="balance">Solde : $<span id="mines-balance" data-balance>{{ number_format($playerBalance, 0, '', ' ') }}</span></span>
            </header>

            <section id="mines-setup" class="mx-auto" style="max-width: 420px;">
                <form id="mines-form" class="text-start">
                    <div class="mb-3">
                        <label for="mines-bet" class="form-label">Mise</label>
                        <input type="number" id="mines-bet" name="bet" class="form-control" min="1" step="1" placeholder="Entrez votre mise" required>
                    </div>
                    <div class="mb-4">
                        <label for="mines-difficulty" class="form-label">Nombre de mines</label>
                        <input type="number" id="mines-difficulty" name="difficulty" class="form-control" min="1" max="24" value="5" required>
                        <div class="form-text">Entre 1 et 24. Plus il y a de mines, plus le gain potentiel augmente.</div>
                    </div>
                    <div class="d-flex justify-content-center gap-3">
                        <button type="reset" id="mines-clear" class="btn btn-outline-light">Reinitialiser</button>
                        <button type="submit" class="btn btn-danger">Lancer la partie</button>
                    </div>
                </form>
            </section>

            <section id="mines-game" class="d-none">
                <div class="mb-3">
                    <p class="mb-1">Mise actuelle : <span id="mines-current-bet">0</span></p>
                    <p class="mb-3">Mines actives : <span id="mines-current-difficulty">0</span></p>
                    <p id="mines-message" class="fw-semibold"></p>
                </div>

                <div class="d-flex justify-content-center">
                    <div id="mines-board" class="d-grid gap-2" style="grid-template-columns: repeat(5, 60px); width: max-content;"></div>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" id="mines-restart" class="btn btn-light">Nouvelle mise</button>
                </div>
            </section>
        </section>
    </div>
</main>

<script>
    window.minesSession = {
        userId: {{ session('user')->id ?? 'null' }},
        balance: {{ (int) $playerBalance }},
        endpoints: { saveBalance: '{{ url('game/balance') }}' },
        csrfToken: '{{ csrf_token() }}'
    };
    document.title = 'Lio Loto - Mines';
</script>
<script defer src="{{ asset('js/mines.js') }}"></script>
</body>
</html>