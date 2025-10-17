@include('shared.header')
@include('shared.navbar')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0"></script>

<main class="blackjack-page py-4">
  <div class="container">
    <section class="game-outline text-center">
      <header class="mb-4">
        <h1 class="mb-2">Crash</h1>
        <p class="mb-3 text-uppercase" style="letter-spacing: 0.12em;">
          Quittez le train avant que tout s'écroule!
        </p>
        <span class="balance" id="balanceUI">
          Solde : <span data-balance>{{ number_format($playerBalance, 0, '', ' ') }}</span>
        </span>
      </header>

      <div class="row g-4 align-items-start">
        <div class="col-12 col-lg-4">
          <section id="betContainer" class="p-3 rounded bg-dark text-start">
            <h2 class="h5 mb-3 text-center text-lg-start">Sélectionnez votre mise</h2>
            <form class="w-100">
              <div class="mb-4">
                <label for="bet" class="form-label">Montant :</label>
                <input type="number" class="form-control" min="1" max="{{ $playerBalance }}" value="1" name="bet" id="bet">
                <span id="balance-error" class="text-danger"></span>
              </div>

              <div class="form-check form-switch mb-3">
                <input type="checkbox" class="form-check-input" id="auto">
                <label class="form-check-label" for="auto">Auto</label>
              </div>

              <div class="mb-3">
                <label for="autoWithdrawal" class="form-label">Retirer à :</label>
                <input type="number" class="form-control" min="1.01" name="autoWithdrawal" id="autoWithdrawal" disabled>
              </div>

              <div class="d-flex flex-wrap gap-2 mt-4">
                <button id="play" class="btn btn-light flex-fill" type="button" @if(!session()->has('user')) disabled @endif>Jouer</button>
                <button id="cashOut" class="btn btn-danger flex-fill" type="button" 
                  @if(!session()->has('user')) disabled @endif>
                  Retirer
                </button>
              </div>
            </form>
            <div id="win-message" class="text-success mt-2"></div>
            <div class="mt-3 bold">Derniers résultats :</div>
            <div id="lastCrash" class="mt-1 d-flex flex-wrap gap-2">

            </div>
          </section>
        </div>

        <div class="col-12 col-lg-8">
          <section id="gameMat" class="p-3 rounded bg-dark text-center">
            <span id="crash-multiplier" class="fs-4 d-block mb-2">1.00x</span>
            <div class="chart-container position-relative" style="height: 50vh; width: 100%;">
              <canvas id="chart"></canvas>
            </div>
          </section>
        </div>
      </div>
    </section>
  </div>
</main>

<script defer src="{{ asset('js/crash.js') }}"></script>
<script>
  Chart.defaults.responsive = true;
  Chart.defaults.maintainAspectRatio = false;

  window.gameSession = {
    userId: {{ session('user')->id ?? 'null' }},
    balance: {{ (int) $playerBalance }},
    endpoints: { saveBalance: '{{ url('game/balance') }}' },
    csrfToken: '{{ csrf_token() }}'
  };

  $('#auto').on('click', function () {
    $("#autoWithdrawal").prop('disabled', !this.checked);
  });
</script>
@include('shared.footer')
</html>