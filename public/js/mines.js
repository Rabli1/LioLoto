(function () {
    const GRID_ROWS = 5;
    const GRID_COLS = 5;
    const GRID_SIZE = GRID_ROWS * GRID_COLS;
    const MINE_ICON = '&#128163;';
    const DIAMOND_ICON = '&#128142;';
    const REVEAL_DELAY_MS = 400;

    const state = {
        bet: 0,
        difficulty: 5,
        mineIndices: [],
        mineSet: new Set(),
        revealed: new Set(),
        safeRevealed: 0,
        started: false,
    };

    const elements = {};

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        elements.form = document.getElementById('mines-form');
        elements.betInput = document.getElementById('mines-bet');
        elements.difficultyInput = document.getElementById('mines-difficulty');
        elements.setupSection = document.getElementById('mines-setup');
        elements.gameSection = document.getElementById('mines-game');
        elements.board = document.getElementById('mines-board');
        elements.message = document.getElementById('mines-message');
        elements.currentBet = document.getElementById('mines-current-bet');
        elements.currentDifficulty = document.getElementById('mines-current-difficulty');
        elements.restartButton = document.getElementById('mines-restart');
        elements.clearButton = document.getElementById('mines-clear');

        if (!elements.form || !elements.board || !elements.setupSection || !elements.gameSection) {
            console.warn('Mines view is missing required elements.');
            return;
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
        updateStatus('Choisissez votre mise et votre difficulte pour commencer.');
    }

    function bindEvents() {
        elements.form.addEventListener('submit', onStartGame);
        if (elements.restartButton) {
            elements.restartButton.addEventListener('click', resetToSetup);
        }
        if (elements.clearButton) {
            elements.clearButton.addEventListener('click', () => {
                setTimeout(() => {
                    updateStatus('Choisissez votre mise et votre difficulte pour commencer.');
                }, 0);
            });
        }
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

    function onStartGame(event) {
        event.preventDefault();

        const betValue = parseInt(elements.betInput.value, 10);
        const difficultyValue = parseInt(elements.difficultyInput.value, 10);

        if (!Number.isFinite(betValue) || betValue <= 0) {
            updateStatus('Entrez une mise valide (minimum 1).');
            return;
        }

        if (!Number.isFinite(difficultyValue) || difficultyValue < 1 || difficultyValue >= GRID_SIZE) {
            updateStatus('Choisissez une difficulte entre 1 et 24.');
            return;
        }

        if (window.Balance && !window.Balance.canMise(betValue)) {
            updateStatus('Solde insuffisant pour cette mise.');
            return;
        }

        if (window.Balance && !window.Balance.miser(betValue)) {
            updateStatus('Solde insuffisant pour cette mise.');
            return;
        }

        state.bet = betValue;
        state.difficulty = Math.min(difficultyValue, GRID_SIZE - 1);
        elements.difficultyInput.value = state.difficulty.toString();
        state.mineIndices = generateMines(state.difficulty);
        state.mineSet = new Set(state.mineIndices);
        state.revealed = new Set();
        state.safeRevealed = 0;
        state.started = true;

        resetBoard();
        toggleSections(true);

        if (elements.currentBet) {
            elements.currentBet.textContent = betValue.toString();
        }
        if (elements.currentDifficulty) {
            elements.currentDifficulty.textContent = state.difficulty.toString();
        }

        const safeRemaining = GRID_SIZE - state.mineIndices.length;
        updateStatus('Bonne chance ! Il reste ' + safeRemaining + ' cases sures a trouver.');
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

    function resetBoard() {
        if (!elements.cells) return;
        elements.cells.forEach((button) => {
            button.innerHTML = '';
            button.disabled = false;
            button.classList.remove('btn-danger', 'btn-outline-danger', 'btn-success');
        });
    }

    function toggleSections(showGame) {
        if (showGame) {
            elements.setupSection.classList.add('d-none');
            elements.gameSection.classList.remove('d-none');
        } else {
            elements.setupSection.classList.remove('d-none');
            elements.gameSection.classList.add('d-none');
        }
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

        const safeTotal = GRID_SIZE - state.mineIndices.length;
        const remaining = safeTotal - state.safeRevealed;

        if (remaining <= 0) {
            handleWin();
        } else {
            updateStatus('Bien joue ! Encore ' + remaining + ' case' + (remaining > 1 ? 's' : '') + ' sure' + (remaining > 1 ? 's' : '') + '.');
        }
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
        disableBoard();
        updateStatus('Une mine a explose. La mise est perdue.');
        setTimeout(() => {
            revealMines(triggerIndex);
        }, REVEAL_DELAY_MS);
    }

    function handleWin() {
        state.started = false;
        disableBoard();
        revealMines();

        const multiplier = 1 + state.difficulty / 10;
        const payout = Math.floor(state.bet * multiplier);
        updateStatus('Bravo ! Vous remportez ' + payout + ' $.');

        if (window.Balance) {
            window.Balance.gain(payout);
        }
    }

    function revealMines(triggerIndex) {
        elements.cells.forEach((button, index) => {
            if (state.mineSet.has(index)) {
                showMine(button, index === triggerIndex);
            }
            button.disabled = true;
        });
    }

    function disableBoard() {
        elements.cells.forEach((button) => {
            button.disabled = true;
        });
    }

    function resetToSetup() {
        if (state.bet > 0 && state.started && window.Balance) {
            window.Balance.gain(state.bet);
        }

        state.bet = 0;
        state.difficulty = Number(elements.difficultyInput?.value || 5) || 5;
        state.mineIndices = [];
        state.mineSet = new Set();
        state.revealed = new Set();
        state.safeRevealed = 0;
        state.started = false;

        elements.form.reset();
        resetBoard();
        toggleSections(false);
        updateStatus('Choisissez votre mise et votre difficulte pour commencer.');
    }

    function updateStatus(message) {
        if (!elements.message) return;
        elements.message.textContent = message || '';
    }
})();