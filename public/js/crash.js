const ctx = document.getElementById("chart");
const multiplier = $("#crash-multiplier");
const lastCrash = $("#lastCrash");
const playBtn = $("#play");
const cachOutBtn = $("#cashOut");
const bet = $("#bet");
const winMessage = $("#win-message");
const auto = $("#auto");
const autoWithdrawal = $("#autoWithdrawal");
const balanceUI = $("#balanceUI");
const balanceError = $("#balance-error");
const csrfToken = $('meta[name="csrf-token"]').attr('content');
let betAmount = 0;
let value = 1;
let autoCashout = false;
let xValues = [];

function playCashout() {
  const audio = new Audio('/sounds/cashout.mp3');
  audio.load();
  audio.play();
}

for (let x = 0; x < 10; x += 0.1) {
  xValues.push(x);
}

let yValues = xValues.map(x => Math.pow(x, 1));

const chart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: xValues,
    datasets: [{
      data: yValues,
      borderColor: 'rgba(255, 0, 0, 0.8)',
      borderWidth: 2,
      pointRadius: 0,
      tension: 0.3
    }]
  },
  options: {
    animation: {
      duration: 200,
      easing: 'easeOutCubic'
    },
    plugins: {
      legend: { display: false }
    },
    scales: {
      x: { display: false },
      y: { display: false }
    }
  }
});

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function animateGame() {
  value = 1;
  let sleepTime = 30;
  let increment = 0.01;
  let gameEnd = 1 / Math.random() * 0.99;
  multiplier.removeClass("text-danger");
  betAmount = bet.val();
  autoCashout = auto.is(":checked")
  let cashOut = autoWithdrawal.val()
  if (autoCashout) {
    cachOutBtn.prop('disabled', true);
  } else {
    cachOutBtn.prop('disabled', false);
  }
  winMessage.text("")

  window.gameSession.balance -= betAmount;
  $.ajax({
    url: '/game/balance',
    method: 'POST',
    data: { balance: parseInt(window.gameSession.balance) },
    headers: { 'X-CSRF-TOKEN': csrfToken },
    success: function (response) {
      balanceUI.text(`Solde : ${response.balance.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).replace(/,/g, ' ')}`);
    },
    error: function (xhr, status, error) {
      console.error('Error saving balance:', error);
    }
  });

  while (value < gameEnd) {

    value += increment;
    multiplier.text(value.toFixed(2) + "x");

    if (autoCashout) {
      if (cashOut < value) {
        playCashout();
        console.log(value + " " + cashOut);
        const win = parseInt(cashOut * betAmount);
        winMessage.text(`${cashOut}x -> ${win} gagné`)
        autoCashout = false;
        window.gameSession.balance += win;
        $.ajax({
          url: '/game/balance',
          method: 'POST',
          data: { balance: parseInt(window.gameSession.balance) },
          headers: { 'X-CSRF-TOKEN': csrfToken },
          success: function (response) {
            balanceUI.text(`Solde : ${response.balance.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).replace(/,/g, ' ')}`);
          },
          error: function (xhr, status, error) {
            console.error('Error saving balance:', error);
          }
        });
      }
    }
    if (value < 25) {
      const a = Math.min(value / 2, 10) + 1;
      chart.data.datasets[0].data = xValues.map(x => Math.pow(x, a));
      chart.update();
    }

    if (value > 5) {
      sleepTime = 15;
      if (value > 10) {
        sleepTime = 10;
        increment = 0.02;
        if (value > 20) {
          sleepTime = 1;
          increment = 0.03;
        }
      }
    }
    await sleep(sleepTime);
  }

  multiplier.addClass("text-danger");
  let list = lastCrash.find('div');
  if (list.length > 10) {
    list.eq(0).remove();
  }
  lastCrash.append(`<div class="bg-secondary rounded p-1">${value.toFixed(2) + "x"}</div>`);
}

playBtn.on("click", async function () {
  window.gameSession.balance = Number(window.gameSession.balance)
  balanceError.text("");
  if (window.gameSession.balance < bet.val()) {
    balanceError.text("Vous n'avez pas assez de points");
    return;
  }
  if (bet.val() <= 0) {
    balanceError.text("Mise invalide");
    return;
  }
  playBtn.prop('disabled', true);
  await animateGame();
  cachOutBtn.prop('disabled', true);
  playBtn.prop('disabled', false);
});

cachOutBtn.on("click", function () {
  playCashout();
  window.gameSession.balance = Number(window.gameSession.balance);
  cachOutBtn.prop('disabled', true);
  const balance = parseInt(value * betAmount);
  winMessage.text(`${value.toFixed(2)}x -> ${(balance)} gagné`);

  window.gameSession.balance += balance;
  $.ajax({
    url: '/game/balance',
    method: 'POST',
    data: { balance: parseInt(window.gameSession.balance) },
    headers: { 'X-CSRF-TOKEN': csrfToken },
    success: function (response) {
      balanceUI.text(`Solde : ${response.balance.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).replace(/,/g, ' ')}`);
    },
    error: function (xhr, status, error) {
      console.error('Error saving balance:', error);
    }
  });
});