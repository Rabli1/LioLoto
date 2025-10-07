const ctx = document.getElementById("chart");
const multiplier = $("#crash-multiplier");
const lastCrash = $("#lastCrash");
const playBtn = $("#play");
const cachOutBtn = $("#cashOut");
const bet = $("#bet");
const winMessage = $("#win-message");
const auto = $("#auto");
const autoWithdrawal = $("#autoWithdrawal");
let betAmount = 0;
let value = 1;
let autoCashout = false;
let xValues = [];
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
  while (value < gameEnd) {
    value += increment;
    multiplier.text(value.toFixed(2) + "x");

    if(autoCashout){
      if(cashOut < value){
        winMessage.text(`${value.toFixed(2)}x -> ${(value * betAmount).toFixed(2)} gagné`)
        autoCashout = false;
        //backend ici
      }
    }

    if(value < 25){
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
  lastCrash.append(`<div class="bg-secondary rounded p-1">${value.toFixed(2) + "x"}</div>`);

}

playBtn.on("click", async function(){
  playBtn.prop('disabled', true);
  await animateGame();
  playBtn.prop('disabled', false);
});

cachOutBtn.on("click", function(){
  cachOutBtn.prop('disabled', true)
  winMessage.text(`${value.toFixed(2)}x -> ${(value * betAmount).toFixed(2)} gagné`)
  //backend ici
});

