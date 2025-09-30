(function () {
    const GRID_SIZE = 5; // 5x5
    const TOTAL_CELLS = GRID_SIZE * GRID_SIZE;

    let state = {
        bet: 0,
        mines: 3,
        positions: new Set(),
        revealed: new Set(),
        picks: 0,
        roundActive: false,
        currentMultiplier: 1,
        cashable: false,
    };

    function dispatchGameEvent(name, detail = {}) {
        document.dispatchEvent(new CustomEvent(name, { detail }));
    }

    function el(id) { return document.getElementById(id); }

    function format(n) {
        return Math.floor(n).toLocaleString('fr-FR');
    }

    function resetRoundState() {
        state.positions = new Set();
        state.revealed = new Set();
        state.picks = 0;
        state.roundActive = false;
        state.currentMultiplier = 1;
        state.cashable = false;
    }

    function multiplierForPicks(picks, total, mines) {
        if (picks <= 0) return 1;
        let m = 1;
        for (let i = 0; i < picks; i += 1) {
            m *= (total - i) / (total - mines - i);
        }
        return m;
    }

    function updateHud() {
        const roundInfo = el('roundInfo');
        const betInfo = el('betInfo');
        const multInfo = el('multiplierInfo');
        const payoutInfo = el('payoutInfo');

        if (roundInfo) {
            roundInfo.textContent = state.roundActive
                ? `Mines: ${state.mines} • Cases sûres trouvées: ${state.picks}`
                : '';
        }
        if (betInfo) {
            betInfo.textContent = state.roundActive ? `Mise: ${format(state.bet)}` : '';
        }

        const mult = state.currentMultiplier;
        const potential = mult * state.bet;
        multInfo.textContent = state.roundActive ? `Multiplicateur actuel: x${mult.toFixed(3)}` : '';
        payoutInfo.textContent = state.roundActive ? `Paiement possible: ${format(potential)}` : '';

        el('cashoutButton').disabled = !state.cashable;
    }

    function allSafePicks(total, mines) {
        return total - mines;
    }

    function buildGrid() {
        const grid = el('minesGrid');
        grid.innerHTML = '';
        grid.style.setProperty('--grid-size', GRID_SIZE);
        for (let i = 0; i < TOTAL_CELLS; i += 1) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'mine-cell';
            btn.dataset.index = String(i);
            btn.textContent = '';
            btn.addEventListener('click', onCellClick);
            grid.appendChild(btn);
        }
    }

    function randomMines(count) {
        const set = new Set();
        while (set.size < count) {
            set.add(Math.floor(Math.random() * TOTAL_CELLS));
        }
        return set;
    }

    function revealAllMines() {
        const grid = el('minesGrid');
        state.positions.forEach((idx) => {
            const cell = grid.querySelector(`.mine-cell[data-index="${idx}"]`);
            if (cell) {
                cell.classList.add('is-mine');
                cell.textContent = '💣';
            }
        });
    }

    function endRound(win, payout) {
        state.roundActive = false;
        state.cashable = false;
        el('cashoutButton').disabled = true;
        el('newRoundButton').style.display = 'inline-block';

        const msg = el('resultMessage');
        if (win) {
            msg.textContent = `Vous encaissez ${format(payout)} !`;
            dispatchGameEvent('mines:result', { outcome: 'win', bet: state.bet, payout });
            if (window.Balance && payout > 0) {
                window.Balance.gain(Math.floor(payout));
            }
        } else {
            msg.textContent = `Mine trouvée. Vous perdez ${format(state.bet)}.`;
            dispatchGameEvent('mines:result', { outcome: 'lose', bet: state.bet, payout: 0 });
        }
    }

    function onCellClick(e) {
        if (!state.roundActive) return;
        const cell = e.currentTarget;
        const idx = Number(cell.dataset.index);
        if (state.revealed.has(idx)) return;

        state.revealed.add(idx);

        if (state.positions.has(idx)) {
            cell.classList.add('is-mine');
            cell.textContent = '💣';
            revealAllMines();
            endRound(false, 0);
            return;
        }

        cell.classList.add('is-safe');
        cell.textContent = '💎';
        state.picks += 1;
        state.currentMultiplier = multiplierForPicks(state.picks, TOTAL_CELLS, state.mines);
        state.cashable = true;
        updateHud();

        if (state.picks >= allSafePicks(TOTAL_CELLS, state.mines)) {
            const payout = Math.floor(state.bet * state.currentMultiplier);
            endRound(true, payout);
        }
    }

    function startRound() {
        const minesSel = el('minesCount');
        state.mines = Math.max(1, Math.min(24, Number(minesSel?.value || 3)));
        resetRoundState();
        state.roundActive = true;
        state.positions = randomMines(state.mines);
        state.currentMultiplier = 1;
        state.cashable = false;

        buildGrid();
        updateHud();

        el('betContainer').style.display = 'none';
        el('gameMat').style.display = 'block';
        el('newRoundButton').style.display = 'none';
        el('resultMessage').textContent = '';
    }

    function cashout() {
        if (!state.roundActive || !state.cashable) return;
        const payout = Math.floor(state.bet * state.currentMultiplier);
        endRound(true, payout);
    }

    function restart() {
        resetRoundState();
        el('gameMat').style.display = 'none';
        el('betContainer').style.display = 'block';
        el('selectedBet').textContent = $pendingBet;
        el('resultMessage').textContent = '';
    }

    function initMines() {
        const betTokens = document.querySelectorAll('.betToken');
        const selectedBetLabel = el('selectedBet');
        const placeBetButton = el('placeBet');
        const clearBetButton = el('clearBet');
        const cashoutButton = el('cashoutButton');
        const newRoundButton = el('newRoundButton');

        let pendingBet = 0;

        betTokens.forEach((btn) => {
            btn.addEventListener('click', function () {
                pendingBet += parseInt(this.getAttribute('data-value'), 10);
                if (pendingBet > 0) {
                    selectedBetLabel.textContent = `Mise sélectionnée : ${pendingBet}`;
                } else {
                    selectedBetLabel.textContent = 'Aucune mise sélectionnée';
                }
            });
        });

        clearBetButton.addEventListener('click', function () {
            pendingBet = 0;
            selectedBetLabel.textContent = 'Aucune mise sélectionnée';
        });

        placeBetButton.addEventListener('click', function () {
            if (!pendingBet) {
                alert('Veuillez sélectionner un jeton de mise !');
                return;
            }
            if (window.Balance && !window.Balance.canMise(pendingBet)) {
                alert('Solde insuffisant.');
                return;
            }
            if (window.Balance && !window.Balance.miser(pendingBet)) {
                alert('Solde insuffisant.');
                return;
            }
            state.bet = pendingBet;
            startRound();
        });

        cashoutButton.addEventListener('click', cashout);
        newRoundButton.addEventListener('click', restart);
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.Balance) {
            window.Balance.init({
                session: window.gameSession || {},
                displaySelectors: ['#mines-balance']
            });
        }
        initMines();
    });
})();
