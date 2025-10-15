function Plinko(x, y, r) {
    var options = {
        isStatic: true,
        restitution: 0.5, //les rebonds
        friction: 0 //friction
    }
    this.body = Bodies.circle(x, y, r, options);
    this.r = r;
    World.add(world, this.body);
}

Plinko.prototype.show = function () {
    fill(220, 0, 0);
    stroke(255);
    var pos = this.body.position;
    push();
    translate(pos.x, pos.y);
    ellipse(0, 0, this.r * 2);
    pop();
}


let totalParticles = 0;
let particlesDropped = 0;
let dropInterval = 30;

document.addEventListener('DOMContentLoaded', () => {
    if (window.Balance) {
        window.Balance.init({
            session: window.gameSession || {},
            displaySelectors: ['#mines-balance']
        });
    }

    const betTokens = document.querySelectorAll('.betToken');
    const selectedBetLabel = document.getElementById('selectedBet');
    const clearBetButton = document.getElementById('clearBet');
    const placeBetButton = document.getElementById('placeBet');

    let pendingBet = 0;

    betTokens.forEach((btn) => {
        btn.addEventListener('click', function () {
            pendingBet += parseInt(this.getAttribute('data-value'), 10);
            selectedBetLabel.textContent = pendingBet > 0 ? `Mise sélectionnée : ${pendingBet}` : 'Aucune mise sélectionnée';
        });
    });

    clearBetButton.addEventListener('click', function () {
        pendingBet = 0;
        selectedBetLabel.textContent = 'Aucune mise sélectionnée';
    });

    placeBetButton.addEventListener('click', function () {
        if (!pendingBet) return alert('Veuillez sélectionner un jeton de mise !');

        if (!window.Balance.canMise(pendingBet)) {
            console.log("Solde insuffisant pour la mise de :", pendingBet, "Montant :", window.Balance._state.balance);
            return alert('Solde insuffisant.');
        }

        window.Balance.miser(pendingBet);

        totalParticles = parseInt(document.getElementById('minesCount').value, 10);
        particlesDropped = 0;
        console.log("Mise validée :", pendingBet, "| Billes :", totalParticles);
        console.log("Montant :", window.Balance._state);
    });
});