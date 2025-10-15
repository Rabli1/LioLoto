(function () {
    const GRID_SIZE = 5; // 5x5
    const TOTAL_CELLS = GRID_SIZE * GRID_SIZE;

    const audioFX = (() => {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) {
            return {
                prime: () => null,
                safe: () => {},
                mine: () => {}
            };
        }

        let ctx = null;

        function ensureContext() {
            if (!ctx) {
                ctx = new AudioContext();
            }
            if (ctx.state === 'suspended') {
                ctx.resume();
            }
            return ctx;
        }

        function beep({ frequency, duration, type = 'sine', volume = 0.22, delay = 0 }) {
            const context = ensureContext();
            if (!context) {
                return;
            }

            const start = context.currentTime + delay;
            const oscillator = context.createOscillator();
            const gain = context.createGain();

            oscillator.type = type;
            oscillator.frequency.setValueAtTime(frequency, start);

            gain.gain.setValueAtTime(0.0001, start);
            gain.gain.linearRampToValueAtTime(volume, start + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, start + Math.max(duration - 0.04, 0.05));

            oscillator.connect(gain).connect(context.destination);
            oscillator.start(start);
            oscillator.stop(start + duration + 0.08);
        }

        return {
            prime: ensureContext,
            safe() {
                beep({ frequency: 520, duration: 0.18, type: "triangle", volume: 0.24 });
                beep({ frequency: 700, duration: 0.16, type: "triangle", volume: 0.2, delay: 0.12 });
            },
            mine() {
                beep({ frequency: 180, duration: 0.32, type: "sawtooth", volume: 0.28 });
                beep({ frequency: 90, duration: 0.4, type: "square", volume: 0.22, delay: 0.08 });
            }
        };
    })();

    if (typeof audioFX.prime === 'function') {
        window.addEventListener('pointerdown', () => audioFX.prime(), { once: true });
    }

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

    function multiplicateur(picks, total, mines) {
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
                ? `Mines: ${state.mines} | Cases sures trouvees: ${state.picks}`
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

    function animateCell(cell, animationClass) {
        if (!cell) {
            return;
        }
        cell.classList.remove('reveal-safe', 'reveal-mine');
        void cell.offsetWidth;
        cell.classList.add(animationClass);
        cell.addEventListener('animationend', function handler() {
            cell.classList.remove(animationClass);
            cell.removeEventListener('animationend', handler);
        }, { once: true });
    }

    function gridBuilder() {
        const grid = el('minesGrid');
        grid.innerHTML = '';
        grid.style.setProperty('--grid-size', GRID_SIZE);
        for (let i = 0; i < TOTAL_CELLS; i += 1) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'mine-cell';
            btn.dataset.index = String(i);
            btn.textContent = '';
            btn.addEventListener('click', onClickCellule);
            grid.appendChild(btn);
        }
    }

    function minesPlacement(count) {
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
                cell.innerHTML = '<img src="/img/mines/mine.png" alt="Mine" class="mine-icon">';
                animateCell(cell, 'reveal-mine');
            }
        });
    }

    function finRound(win, payout) {
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
            msg.textContent = `Mine detectee. Vous perdez ${format(state.bet)}.`;
            dispatchGameEvent('mines:result', { outcome: 'lose', bet: state.bet, payout: 0 });
            
        }
    }

    function onClickCellule(e) {
        if (!state.roundActive) return;
        const cell = e.currentTarget;
        const idx = Number(cell.dataset.index);
        if (state.revealed.has(idx)) return;

        state.revealed.add(idx);

        if (state.positions.has(idx)) {
            cell.classList.add('is-mine');
            cell.innerHTML = '<img src="/img/mines/mine.png" alt="Mine" class="mine-icon">';
            animateCell(cell, 'reveal-mine');
            audioFX.mine();
            revealAllMines();
            finRound(false, 0);
            return;
        }

        cell.classList.add('is-safe');
        cell.innerHTML = '<img src="/img/mines/gem.png" alt="Safe" class="mine-icon">';
        animateCell(cell, 'reveal-safe');
        audioFX.safe();

        state.picks += 1;
        state.currentMultiplier = multiplicateur(state.picks, TOTAL_CELLS, state.mines);
        state.cashable = true;
        updateHud();

        if (state.picks >= allSafePicks(TOTAL_CELLS, state.mines)) {
            const payout = Math.floor(state.bet * state.currentMultiplier);
            finRound(true, payout);
        }
    }

    function startRound() {
        const minesCell = el('minesCount');
        state.mines = Math.max(1, Math.min(24, Number(minesCell.value)));
        resetRoundState();
        state.roundActive = true;
        state.positions = minesPlacement(state.mines);
        state.currentMultiplier = 1;
        state.cashable = false;

        gridBuilder();
        updateHud();

        el('betContainer').style.display = 'none';
        el('gameMat').style.display = 'block';
        el('newRoundButton').style.display = 'none';
        el('resultMessage').textContent = '';
    }

    function paiement() {
        if (!state.roundActive || !state.cashable) return;
        const payout = Math.floor(state.bet * state.currentMultiplier);
        finRound(true, payout);
    }

    function restart() {
        resetRoundState();
        state.bet = 0;
        el('gameMat').style.display = 'none';
        el('betContainer').style.display = 'block';
        el('selectedBet').textContent = `Mise selectionnee : ${pendingBet}` ?? 'Aucune mise selectionnee';
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
                    selectedBetLabel.textContent = `Mise selectionnee : ${pendingBet}`;
                } else {
                    selectedBetLabel.textContent = 'Aucune mise selectionnee';
                }
                audioFX.prime();
            });
        });

        clearBetButton.addEventListener('click', function () {
            pendingBet = 0;
            selectedBetLabel.textContent = 'Aucune mise selectionnee';
        });

        placeBetButton.addEventListener('click', function () {
            if (!pendingBet) {
                alert('Veuillez selectionner un jeton de mise !');
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
            audioFX.prime();
            startRound();
        });

        cashoutButton.addEventListener('click', paiement);
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
