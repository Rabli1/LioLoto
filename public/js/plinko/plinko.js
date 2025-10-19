function Plinko(x, y, r) {
    var options = {
        isStatic: true,
        restitution: 0.5,
        friction: 0,
    };
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
};

(function () {
    const state = window.plinkoGame = window.plinkoGame || {
        total: 0,
        dropped: 0,
        remaining: 0,
        active: false,
        betPerBall: 0,
        totalBet: 0,
        roundPayout: 0,
        dropInterval: 30,
    };

    function format(value) {
        return Number(value || 0).toLocaleString('fr-FR');
    }

    function updateBetLabel(label, pending, ballCount = 1) {
        if (!label) return;
        if (!pending) {
            label.textContent = 'Aucune mise sélectionnée';
            return;
        }
        const total = pending * ballCount;
        label.textContent = `Mise sélectionnée : ${format(pending)} × ${ballCount} = ${format(total)}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.Balance) {
            window.Balance.init({
                session: window.plinkoSession || window.gameSession || {},
                displaySelectors: ['#plinko-balance'],
            });
        }

        const betTokens = document.querySelectorAll('.betToken');
        if (!betTokens.length) return;

        const selectedBetLabel = document.getElementById('selectedBet');
        const clearBetButton = document.getElementById('clearBet');
        const placeBetButton = document.getElementById('placeBet');
        const ballCountSelect = document.getElementById('plinkosCount');

        let pendingBet = 0;
        let currentBallCount = parseInt(ballCountSelect?.value, 10) || 1;

        ballCountSelect?.addEventListener('change', () => {
            currentBallCount = parseInt(ballCountSelect?.value, 10) || 1;
            updateBetLabel(selectedBetLabel, pendingBet, currentBallCount);
        });

        updateBetLabel(selectedBetLabel, pendingBet, currentBallCount);

        betTokens.forEach((btn) => {
            btn.addEventListener('click', () => {
                const value = parseInt(btn.getAttribute('data-value'), 10);
                pendingBet += value;
                updateBetLabel(selectedBetLabel, pendingBet, currentBallCount);
            });
        });

        clearBetButton?.addEventListener('click', () => {
            if (state.active) return;
            pendingBet = 0;
            updateBetLabel(selectedBetLabel, pendingBet, currentBallCount);
        });

        placeBetButton?.addEventListener('click', () => {
            if (state.active) {
                alert('Une manche est déjà en cours.');
                return;
            }

            if (!pendingBet) {
                alert('Veuillez sélectionner une mise !');
                return;
            }

            const selectedBalls = parseInt(ballCountSelect?.value, 10);
            const totalBalls = selectedBalls > 0 ? selectedBalls : 1;
            const totalBetAmount = pendingBet * totalBalls;

            if (
                !window.Balance ||
                !window.Balance.canMise(totalBetAmount) ||
                !window.Balance.miser(totalBetAmount, { persist: false })
            ) {
                alert('Solde insuffisant.');
                return;
            }

            const statsList = document.getElementById('statsList');
            const totalSpan = document.getElementById('totalGain');
            if (statsList) statsList.innerHTML = '';
            if (totalSpan) totalSpan.textContent = '0';
            totalGain = 0;

            state.total = totalBalls;
            state.remaining = totalBalls;
            state.dropped = 0;
            state.betPerBall = pendingBet;
            state.totalBet = totalBetAmount;
            state.roundPayout = 0;
            state.active = true;

            pendingBet = 0;
            updateBetLabel(selectedBetLabel, pendingBet, currentBallCount);

        });
    });

    window.handlePlinko = function (hit) {
        if (!state.active) return;

        const value = Number(typeof hit === 'object' ? hit.value : hit);
        const mise = state.betPerBall;
        let gain = 0;

        if (value > 0) {
            gain = Math.round(mise * value);
            state.roundPayout += gain;
        }

        onBouleTerminee(mise, value);

        state.remaining = Math.max(0, state.remaining - 1);

        if (!state.remaining) {
            if (window.Balance) {
                if (state.roundPayout > 0) {
                    window.Balance.gain(state.roundPayout, { persist: false });
                }
                window.Balance.ajouterMontantJSON();
            }
            state.active = false;
            state.total = 0;
            state.dropped = 0;
            state.betPerBall = 0;
            state.totalBet = 0;
            state.roundPayout = 0;
        }
    };
    let totalGain = 0;

    // Fonction pour ajouter une ligne de stats
    function ajouterStat(mise, multiplicateur, gain) {
        const statsList = document.getElementById('statsList');
        const totalSpan = document.getElementById('totalGain');

        const div = document.createElement('div');
        div.classList.add('d-flex', 'justify-content-between', 'mb-1');
        div.innerHTML = `
        <span>${mise} x ${multiplicateur}</span>
        <span> ${gain}</span>
    `;

        statsList.appendChild(div);

        totalGain += gain;
        totalSpan.textContent = totalGain.toFixed(2);
    }

    // Exemple d’utilisation (à appeler à chaque fois qu’une boule termine)
    function onBouleTerminee(mise, multiplicateur) {
        const gain = mise * multiplicateur;
        ajouterStat(mise, multiplicateur, gain);
    }
})();