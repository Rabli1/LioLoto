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
        dropInterval: 30,
    };

    function format(value) {
        return Number(value || 0).toLocaleString('fr-FR');
    }

    function updateBetLabel(label, pending) {
        if (!label) return;
        label.textContent = pending
            ? 'Mise selectionnee : ' + format(pending)
            : 'Aucune mise selectionnee';
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
        const ballCountSelect = document.getElementById('minesCount');

        let pendingBet = 0;
        updateBetLabel(selectedBetLabel, pendingBet);

        betTokens.forEach((btn) => {
            btn.addEventListener('click', () => {
                const value = parseInt(btn.getAttribute('data-value'), 10);
                pendingBet += value;
                updateBetLabel(selectedBetLabel, pendingBet);
            });
        });

        clearBetButton?.addEventListener('click', () => {
            if (state.active) return;
            pendingBet = 0;
            updateBetLabel(selectedBetLabel, pendingBet);
        });

        placeBetButton?.addEventListener('click', () => {
            if (state.active) {
                alert('Une manche est deja en cours.');
                return;
            }

            if (!pendingBet) {
                alert('Veuillez selectionner une mise !');
                return;
            }

            const selectedBalls = parseInt(ballCountSelect?.value, 10);
            const totalBalls = selectedBalls > 0 ? selectedBalls : 1;

            if (!window.Balance || !window.Balance.canMise(pendingBet) || !window.Balance.miser(pendingBet)) {
                alert('Solde insuffisant.');
                return;
            }

            state.total = totalBalls;
            state.remaining = totalBalls;
            state.dropped = 0;
            state.betPerBall = pendingBet;
            state.active = true;

            pendingBet = 0;
            updateBetLabel(selectedBetLabel, pendingBet);
        });
    });

    window.handlePlinko = function (hit) {
        if (!state.active) {
            return;
        }

        const value = Number(typeof hit === 'object' ? hit.value : hit);
        if (value > 0 && window.Balance) {
            const payout = Math.round(state.betPerBall * value);
            if (payout > 0) {
                window.Balance.gain(payout);
            }
        }

        state.remaining = Math.max(0, state.remaining - 1);
        if (!state.remaining) {
            state.active = false;
            state.total = 0;
            state.dropped = 0;
            state.betPerBall = 0;
        }
    };
})();
