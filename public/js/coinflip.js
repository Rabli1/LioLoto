(function () {
    const progressBar = document.getElementById('progressBar');
    const rolledValue = document.getElementById('rolledValue');
    const resultMessage = document.getElementById('resultMessage');
    const startButton = document.getElementById('startRound');
    const clearBetButton = document.getElementById('clearBet');
    const playAnotherButton = document.getElementById('playAnother');
    const choiceSummary = document.getElementById('choiceSummary');
    const targetInput = document.getElementById('targetInput');
    const targetSlider = document.getElementById('targetSlider');
    const selectedBetLabel = document.getElementById('selectedBet');
    const betTokens = document.querySelectorAll('.betToken');
    const chooseButtons = document.querySelectorAll('[data-choice]');
    const targetMarker = document.getElementById('targetMarker');

    if (!progressBar || !rolledValue || !startButton) {
        return;
    }

    const state = {
        pendingBet: 0,
        bet: 0,
        target: 50,
        choice: 'under',
        inRound: false,
        animationId: null,
        multiplier: 2
    };

    function format(value) {
        return Math.round(value).toLocaleString('fr-FR');
    }

    function calculateMultiplier(choice, target) {
        const chance = choice === 'under' ? target : (100 - target);
        if (chance <= 0) return 0;
        const precise = 100 / chance;
        return Math.max(1, Math.round(precise * 100) / 100);
    }

    function formatMultiplier(value) {
        return value.toFixed(2);
    }

    function clampTarget(value) {
        const n = Number(value);
        if (!Number.isFinite(n)) return state.target;
        return Math.min(99, Math.max(1, Math.floor(n)));
    }

    function updateBetLabel() {
        if (state.pendingBet > 0) {
            selectedBetLabel.textContent = 'Mise selectionnee : ' + format(state.pendingBet);
        } else {
            selectedBetLabel.textContent = 'Aucune mise selectionnee';
        }
    }

    function resetProgressBar() {
        cancelAnimationFrame(state.animationId);
        state.animationId = null;
        progressBar.style.transition = 'none';
        progressBar.style.width = '0%';
        progressBar.setAttribute('aria-valuenow', '0');
        progressBar.textContent = '0';
        resultMessage.textContent = '';
        rolledValue.textContent = 'Valeur tiree : 0';
        progressBar.classList.remove('bg-danger');
        progressBar.classList.add('bg-success');
        void progressBar.offsetWidth;
    }

    function updateChoiceSummary() {
        state.multiplier = calculateMultiplier(state.choice, state.target);
        const label = state.choice === 'under' ? 'UNDER' : 'OVER';
        choiceSummary.textContent = 'Miser sur ' + label + ' ' + state.target;
        choiceSummary.textContent += ' (x' + formatMultiplier(state.multiplier) + ')';
    }

    function setChoice(choice) {
        state.choice = choice;
        chooseButtons.forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.choice === choice);
        });
        updateChoiceSummary();
    }

    function updateTargetMarker() {
        if (!targetMarker) return;
        const clamped = Math.min(99, Math.max(1, state.target));
        targetMarker.style.left = clamped + '%';
    }

    function setTarget(value) {
        state.target = clampTarget(value);
        targetInput.value = state.target;
        targetSlider.value = state.target;
        updateTargetMarker();
        updateChoiceSummary();
    }

    function playRound() {
        if (state.inRound) return;
        if (!state.pendingBet) {
            alert('Veuillez selectionner une mise.');
            return;
        }
        if (!window.Balance) {
            alert('Balance non disponible.');
            return;
        }
        if (!window.Balance.canMise(state.pendingBet)) {
            alert('Solde insuffisant.');
            return;
        }
        if (!window.Balance.miser(state.pendingBet)) {
            alert('Solde insuffisant.');
            return;
        }

        state.bet = state.pendingBet;
        state.pendingBet = 0;
        updateBetLabel();
        resetProgressBar();
        resultMessage.textContent = 'Tirage en cours...';
        playAnotherButton.style.display = 'none';
        startButton.disabled = true;
        state.inRound = true;
        state.multiplier = calculateMultiplier(state.choice, state.target);

        const roll = Math.floor(Math.random() * 101);
        const duration = 1200;
        let startTime = null;

        function animate(timestamp) {
            if (!startTime) startTime = timestamp;
            const elapsed = timestamp - startTime;
            const progress = Math.min(1, elapsed / duration);
            const currentValue = Math.round(progress * roll);
            progressBar.style.transition = 'none';
            progressBar.style.width = currentValue + '%';
            progressBar.setAttribute('aria-valuenow', String(currentValue));
            progressBar.textContent = String(currentValue);
            rolledValue.textContent = 'Valeur tiree : ' + currentValue;

            if (progress < 1) {
                state.animationId = requestAnimationFrame(animate);
                return;
            }

            finishRound(roll);
        }

        state.animationId = requestAnimationFrame(animate);
    }

    function finishRound(roll) {
        state.inRound = false;
        startButton.disabled = false;
        rolledValue.textContent = 'Valeur tiree : ' + roll;
        progressBar.style.width = roll + '%';
        progressBar.setAttribute('aria-valuenow', String(roll));
        progressBar.textContent = String(roll);

        const underWin = roll < state.target;
        const overWin = roll > state.target;
        const win = state.choice === 'under' ? underWin : overWin;

        if (win) {
            const payout = Math.round(state.bet * state.multiplier);
            resultMessage.textContent = 'Gagne ! Paiement : ' + format(payout) + ' (x' + formatMultiplier(state.multiplier) + ')';
            progressBar.classList.remove('bg-danger');
            progressBar.classList.add('bg-success');
            if (window.Balance) {
                window.Balance.gain(payout);
            }
        } else {
            resultMessage.textContent = 'Perdu...';
            progressBar.classList.remove('bg-success');
            progressBar.classList.add('bg-danger');
        }

        state.bet = 0;
        playAnotherButton.style.display = 'inline-block';
    }

    function resetRound() {
        if (state.inRound) return;
        state.bet = 0;
        state.pendingBet = 0;
        updateBetLabel();
        resetProgressBar();
        playAnotherButton.style.display = 'none';
        resultMessage.textContent = '';
        updateTargetMarker();
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.Balance) {
            window.Balance.init({
                session: window.gameSession || {},
                displaySelectors: ['#coinflip-balance']
            });
        }

        betTokens.forEach((btn) => {
            btn.addEventListener('click', function () {
                const value = parseInt(this.getAttribute('data-value'), 10);
                if (!Number.isFinite(value)) return;
                state.pendingBet += value;
                updateBetLabel();
            });
        });

        clearBetButton.addEventListener('click', () => {
            state.pendingBet = 0;
            updateBetLabel();
        });

        chooseButtons.forEach((btn) => {
            btn.addEventListener('click', () => setChoice(btn.dataset.choice));
        });

        targetInput.addEventListener('change', () => setTarget(targetInput.value));
        targetSlider.addEventListener('input', () => setTarget(targetSlider.value));

        startButton.addEventListener('click', playRound);
        playAnotherButton.addEventListener('click', resetRound);

        setTarget(state.target);
        setChoice(state.choice);
        resetProgressBar();
        updateBetLabel();
        updateTargetMarker();
    });
})();
