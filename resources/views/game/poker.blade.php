@include('shared.header')
@include('shared.navbar')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0"></script>

<main class="blackjack-page py-4">
    <div class="container">
        <section class="game-outline text-center">
            <header class="mb-4">
                <h1 class="mb-2">Crash</h1>
                <p class="mb-3 text-uppercase" style="letter-spacing: 0.12em;">
                    Volez au autres en gros
                </p>
                <span class="balance" id="balanceUI">
                    Solde : <span data-balance>{{ number_format($playerBalance, 0, '', ' ') }}</span>
                </span>
            </header>

            <section id="gameMat" class="p-3 rounded bg-dark text-center">
                
            </section>
        </section>
    </div>
</main>

<script defer src="{{ asset('js/poker.js') }}"></script>
<script>
    window.gameSession = {
        userId: {{ session('user')->id ?? 'null' }},
        balance: {{ (int) $playerBalance }},
        endpoints: { saveBalance: '{{ url('game/balance') }}' },
        csrfToken: '{{ csrf_token() }}'
    };
</script>
@include('shared.footer')

</html>