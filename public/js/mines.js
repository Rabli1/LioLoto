
(function () {
    const GRID_ROWS = 5;
    const GRID_COLS = 5;
    const GRID_SIZE = GRID_ROWS * GRID_COLS;
    const DEFAULT_DIFFICULTY = 3;
    const MINE_ICON = '&#128163;';
    const DIAMOND_ICON = '&#128142;';
    const REVEAL_DELAY_MS = 400;
    const MULTIPLIER_EXPONENT = 1.05;
    const NO_BET_TEXT = 'Aucune mise sélectionnée';

    const state = {
        bet: 0,
        difficulty: DEFAULT_DIFFICULTY,
        mineIndices: [],
        mineSet: new Set(),
        revealed: new Set(),
        safeRevealed: 0,
        started: false,
    };

    let betAmount = 0;

    const elements = {};

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        elements.betContainer = document.getElementById('betContainer');
        elements.gameSection = document.getElementById('mines-game');
        elements.board = document.getElementById('mines-board');
        elements.message = document.getElementById('mines-message');
        elements.currentBet = document.getElementById('mines-current-bet');
        elements.currentDifficulty = document.getElementById('mines-current-difficulty');
        elements.currentMultiplier = document.getElementById('mines-current-multiplier');
        elements.potentialPayout = document.getElementById('mines-potential-payout');
        elements.restartButton = document.getElementById('mines-restart');
        elements.cashOutButton = document.getElementById('mines-cashout');
        elements.selectedBetLabel = document.getElementById('selectedBet');
        elements.clearBetButton = document.getElementById('clearBet');
        elements.placeBetButton = document.getElementById('placeBet');
        elements.difficultyInput = document.getElementById('mines-difficulty');
        elements.betTokens = document.querySelectorAll('.betToken');

        if (!elements.betContainer || !elements.board || !elements.gameSection) {
            console.warn('Mines view is missing required elements.');
            return;
        }

        betAmount = Number(window.pendingMinesBet || 0) || 0;
        window.pendingMinesBet = betAmount;
        window.currentMinesBet = 0;

        state.difficulty = clampDifficulty(Number(elements.difficultyInput?.value || state.difficulty));
        if (elements.difficultyInput) {
            elements.difficultyInput.value = state.difficulty.toString();
        }

        if (elements.selectedBetLabel) {
            elements.selectedBetLabel.textContent = betAmount > 0
                ? `Mise sélectionnée : ${betAmount}`
                : NO_BET_TEXT;
        }

        if (window.Balance) {
            window.Balance.init({
                session: window.minesSession || {},
                displaySelectors: ['#mines-balance', '[data-balance]'],
            });
        }

        buildBoard();
        bindEvents();
        toggleSections(false);
        updatePayoutDisplay();
        updateStatus('Sélectionnez votre mise pour commencer.');
    }

    function bindEvents() {
        setupBetTokenHandlers();

        if (elements.clearBetButton) {
            elements.clearBetButton.addEventListener('click', onClearBet);
        }

        if (elements.placeBetButton) {
            elements.placeBetButton.addEventListener('click', onPlaceBet);
        }

        if (elements.restartButton) {
            elements.restartButton.addEventListener('click', resetToSetup);
        }

        if (elements.cashOutButton) {
            elements.cashOutButton.addEventListener('click', onCashOut);
        }

        if (elements.difficultyInput) {
            elements.difficultyInput.addEventListener('change', onDifficultyChange);
        }
    }

    function setupBetTokenHandlers() {
        if (!elements.betTokens) {
            return;
        }

        elements.betTokens.forEach((btn) => {
            btn.addEventListener('click', function onBetTokenClick() {
                const value = parseInt(this.getAttribute('data-value'), 10);
                if (!Number.isFinite(value)) {
                    return;
                }

                betAmount += value;
                window.pendingMinesBet = betAmount;

                if (elements.selectedBetLabel) {
                    if (betAmount > 0) {
                        elements.selectedBetLabel.textContent = `Mise sélectionnée : ${betAmount}`;
                    } else {
                        elements.selectedBetLabel.textContent = NO_BET_TEXT;
                    }
                }
            });
        });
    }

    function onClearBet() {
        betAmount = 0;
        window.pendingMinesBet = 0;
        if (elements.selectedBetLabel) {
            elements.selectedBetLabel.textContent = NO_BET_TEXT;
        }
        updateStatus('Mise effacée. Choisissez à nouveau vos jetons.');
    }

    function onDifficultyChange() {
        if (!elements.difficultyInput) {
            return;
        }
        const parsed = parseInt(elements.difficultyInput.value, 10);
        state.difficulty = clampDifficulty(parsed);
        elements.difficultyInput.value = state.difficulty.toString();
        updateStatus(`Nombre de mines défini sur ${state.difficulty}.`);
    }

    function onPlaceBet() {
        if (!betAmount || betAmount <= 0) {
            updateStatus('Sélectionnez au moins une mise.');
            return;
        }

        const difficultyValue = elements.difficultyInput ? parseInt(elements.difficultyInput.value, 10) : state.difficulty;

        if (!startRound(betAmount, difficultyValue)) {
            return;
        }

        betAmount = 0;
        window.pendingMinesBet = 0;
        if (elements.selectedBetLabel) {
            elements.selectedBetLabel.textContent = NO_BET_TEXT;
        }
    }

    function startRound(betValue, difficultyValue) {
        const bet = Number.parseInt(betValue, 10);
        if (!Number.isFinite(bet) || bet <= 0) {
            updateStatus('Entrez une mise valide (minimum 1).');
            return false;
        }

        const difficulty = clampDifficulty(Number.parseInt(difficultyValue, 10));

        if (!Number.isFinite(difficulty) || difficulty < 1 || difficulty >= GRID_SIZE) {
            updateStatus('Choisissez une difficulté entre 1 et 24.');
            return false;
        }

        if (window.Balance && !window.Balance.canMise(bet)) {
            updateStatus('Solde insuffisant pour cette mise.');
            return false;
        }

        if (window.Balance && !window.Balance.miser(bet)) {
            updateStatus('Solde insuffisant pour cette mise.');
            return false;
        }

        state.bet = bet;
        state.difficulty = difficulty;
        state.mineIndices = generateMines(state.difficulty);
        state.mineSet = new Set(state.mineIndices);
        state.revealed = new Set();
        state.safeRevealed = 0;
        state.started = true;

        window.pendingMinesBet = 0;
        window.currentMinesBet = state.bet;

        if (elements.difficultyInput) {
            elements.difficultyInput.value = state.difficulty.toString();
        }

        resetBoard();
        toggleSections(true);

        if (elements.currentBet) {
            elements.currentBet.textContent = state.bet.toString();
        }
        if (elements.currentDifficulty) {
            elements.currentDifficulty.textContent = state.difficulty.toString();
        }

        updatePayoutDisplay();

        const safeRemaining = GRID_SIZE - state.mineIndices.length;
        updateStatus(`Bonne chance ! Il reste ${safeRemaining} cases sûres à trouver.`);
        return true;
    }

    function clampDifficulty(value) {
        const parsed = Number.isFinite(value) ? value : DEFAULT_DIFFICULTY;
        return Math.max(1, Math.min(GRID_SIZE - 1, parsed));
    }

    function buildBoard() {
        elements.board.innerHTML = '';
        const fragment = document.createDocumentFragment();
        elements.cells = [];

        for (let index = 0; index < GRID_SIZE; index += 1) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'grid-square btn btn-light';
            button.dataset.index = index.toString();
            button.style.width = '60px';
            button.style.height = '60px';
            button.style.fontSize = '24px';
            button.addEventListener('click', onCellClick);
            elements.cells.push(button);
            fragment.appendChild(button);
        }

        elements.board.appendChild(fragment);
    }

    function onCellClick(event) {
        if (!state.started) {
            return;
        }

        const button = event.currentTarget;
        const index = Number(button.dataset.index);
        if (state.revealed.has(index)) {
            return;
        }

        state.revealed.add(index);

        if (state.mineSet.has(index)) {
            showMine(button, true);
            handleLoss(index);
            return;
        }

        showDiamond(button);
        state.safeRevealed += 1;
        updatePayoutDisplay();

        const safeTotal = GRID_SIZE - state.mineIndices.length;
        const remaining = safeTotal - state.safeRevealed;

        if (remaining <= 0) {
            handleWin();
        } else {
            updateStatus(`Bien joué ! Encore ${remaining} case${remaining > 1 ? 's' : ''} sûre${remaining > 1 ? 's' : ''}.`);
        }
    }

    function onCashOut() {
        if (!state.started) {
            updateStatus('Aucune partie en cours.');
            return;
        }

        if (state.safeRevealed <= 0) {
            updateStatus('Révélez au moins une case avant dencaisser.');
            return;
        }

        const payout = calculateCurrentPayout();
        awardPayout(payout, `Encaissement réussi ! Vous gagnez ${payout} $.`);
    }

    function calculateCurrentPayout() {
        if (!state.bet || state.safeRevealed <= 0) {
            return 0;
        }
        const multiplier = calculateMultiplier(state.safeRevealed, state.difficulty);
        return Math.floor(state.bet * multiplier);
    }

    function calculateMultiplier(picks, mines) {
        if (picks <= 0) {
            return 1;
        }
        const safeCount = GRID_SIZE - mines;
        if (safeCount <= 0) {
            return 1;
        }

        let multiplier = 1;
        for (let step = 0; step < picks; step += 1) {
            const remainingTotal = GRID_SIZE - step;
            const remainingSafe = safeCount - step;
            if (remainingSafe <= 0) {
                break;
            }
            multiplier *= remainingTotal / remainingSafe;
        }

        return Math.pow(multiplier, MULTIPLIER_EXPONENT);
    }

    function updatePayoutDisplay() {
        const picks = state.safeRevealed;
        const multiplier = calculateMultiplier(picks, state.difficulty);
        if (elements.currentMultiplier) {
            const displayMultiplier = picks > 0 ? multiplier : 1;
            elements.currentMultiplier.textContent = `${displayMultiplier.toFixed(2)}x`;
        }
        if (elements.potentialPayout) {
            const payout = picks > 0 ? Math.floor(state.bet * multiplier) : 0;
            elements.potentialPayout.textContent = payout.toString();
        }
    }

    function awardPayout(amount, message) {
        if (amount <= 0) {
            updateStatus("Impossible d'encaisser pour le moment.");
            return;
        }

        state.started = false;
        window.currentMinesBet = 0;
        disableBoard();
        revealMines();

        if (window.Balance) {
            window.Balance.gain(amount);
        }

        updateStatus(message);

        state.bet = 0;
        state.safeRevealed = 0;
        updatePayoutDisplay();
        if (elements.currentBet) {
            elements.currentBet.textContent = '0';
        }
    }

    function generateMines(count) {
        const limit = Math.min(count, GRID_SIZE - 1);
        const mineSet = new Set();

        while (mineSet.size < limit) {
            const index = Math.floor(Math.random() * GRID_SIZE);
            mineSet.add(index);
        }

        return Array.from(mineSet);
    }

    function showMine(button, isTrigger) {
        button.innerHTML = MINE_ICON;
        button.classList.remove('btn-success');
        button.classList.add(isTrigger ? 'btn-danger' : 'btn-outline-danger');
        button.disabled = true;
    }

    function showDiamond(button) {
        button.innerHTML = DIAMOND_ICON;
        button.classList.remove('btn-danger', 'btn-outline-danger');
        button.classList.add('btn-success');
        button.disabled = true;
    }

    function handleLoss(triggerIndex) {
        state.started = false;
        window.currentMinesBet = 0;
        disableBoard();
        updateStatus('Une mine a explosé. La mise est perdue.');
        setTimeout(() => {
            revealMines(triggerIndex);
        }, REVEAL_DELAY_MS);
        state.bet = 0;
        state.safeRevealed = 0;
        updatePayoutDisplay();
        if (elements.currentBet) {
            elements.currentBet.textContent = '0';
        }
    }

    function handleWin() {
        const payout = calculateCurrentPayout();
        awardPayout(payout, `Bravo ! Vous remportez ${payout} $.`);
    }

    function revealMines(triggerIndex) {
        if (!elements.cells) {
            return;
        }
        elements.cells.forEach((button, index) => {
            if (state.mineSet.has(index)) {
                showMine(button, index === triggerIndex);
            }
            button.disabled = true;
        });
    }

    function disableBoard() {
        if (!elements.cells) {
            return;
        }
        elements.cells.forEach((button) => {
            button.disabled = true;
        });
    }

    function resetBoard() {
        if (!elements.cells) {
            return;
        }
        elements.cells.forEach((button) => {
            button.innerHTML = '';
            button.disabled = false;
            button.classList.remove('btn-danger', 'btn-outline-danger', 'btn-success');
        });
    }

    function resetToSetup() {
        if (state.bet > 0 && state.started && window.Balance) {
            window.Balance.gain(state.bet);
        }

        state.bet = 0;
        state.difficulty = clampDifficulty(Number(elements.difficultyInput?.value || DEFAULT_DIFFICULTY));
        state.mineIndices = [];
        state.mineSet = new Set();
        state.revealed = new Set();
        state.safeRevealed = 0;
        state.started = false;

        betAmount = 0;
        window.pendingMinesBet = 0;
        window.currentMinesBet = 0;

        resetBoard();
        toggleSections(false);
        updatePayoutDisplay();
        updateStatus('Sélectionnez votre mise pour commencer.');

        if (elements.selectedBetLabel) {
            elements.selectedBetLabel.textContent = NO_BET_TEXT;
        }
        if (elements.currentBet) {
            elements.currentBet.textContent = '0';
        }
        if (elements.currentDifficulty) {
            elements.currentDifficulty.textContent = state.difficulty.toString();
        }
    }

    function toggleSections(showGame) {
        if (!elements.betContainer || !elements.gameSection) {
            return;
        }

        if (showGame) {
            elements.betContainer.classList.add('d-none');
            elements.gameSection.classList.remove('d-none');
        } else {
            elements.betContainer.classList.remove('d-none');
            elements.gameSection.classList.add('d-none');
        }
    }

    function updateStatus(message) {
        if (!elements.message) {
            return;
        }
        elements.message.textContent = message || '';
    }
})();
