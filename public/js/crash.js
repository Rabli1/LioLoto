const ctx = document.getElementById("chart");
const multiplier = $("#crash-multiplier");

let fini = false;
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
  let value = parseFloat(multiplier.text()) || 1.00;
  let sleepTime = 25;
  let increment = 0.01;

  while (!fini) {
    value += increment;
    multiplier.text(value.toFixed(2) + "x");
    if(value < 20){
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
}

animateGame();
