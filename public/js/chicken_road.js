(function () {
    const TOTAL_COLUMNS = 13;
    const GOAL_COLUMNS = 10;
    const VISIBLE_COLUMNS = 10;

    function playCashout() {
        const audio = new Audio('/sounds/cashout.mp3');
        audio.load();
        audio.play();
    }

    const state = {
        pendingBet: 0,
        bet: 0,
        difficulty: 1,
        mineCount: 1,
        minePositions: new Set(),
        visited: new Set(),
        roundActive: false,
        currentColumn: 0,
        progress: 0,
        multiplier: 1,
        cashable: false,
        visibleStart: 0,
        boomColumn: null,
        chickenColumn: null,
        lastRenderedChickenColumn: null,
        chickenSprite: null,
    };

    const elements = {
        selectedBet: document.getElementById('selectedBet'),
        placeBet: document.getElementById('placeBet'),
        clearBet: document.getElementById('clearBet'),
        tokens: Array.from(document.querySelectorAll('.betToken')),
        difficulty: document.getElementById('difficultyLevel'),
        mat: document.getElementById('roadMat'),
        track: document.getElementById('roadTrack'),
        roundInfo: document.getElementById('roundInfo'),
        progressInfo: document.getElementById('progressInfo'),
        multiplierInfo: document.getElementById('multiplierInfo'),
        nextMultiplierInfo: document.getElementById('nextMultiplierInfo'),
        payoutInfo: document.getElementById('payoutInfo'),
        resultMessage: document.getElementById('resultMessage'),
        cashout: document.getElementById('cashoutButton'),
        newRound: document.getElementById('newRoundButton'),
        betContainer: document.getElementById('betContainer'),
    };

    function createAudioToolkit() {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextClass) {
            return {
                resume() { },
                playJump() { },
                playExplosion() { },
            };
        }

        let context = null;

        function ensureContext() {
            if (!context) {
                context = new AudioContextClass();
            }
            return context;
        }

        function resume() {
            const ctx = ensureContext();
            if (!ctx) return;
            if (ctx.state === 'suspended') {
                ctx.resume();
            }
        }

        function playJump() {
            const ctx = ensureContext();
            if (!ctx) return;

            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'square';
            osc.frequency.setValueAtTime(460, now);
            osc.frequency.exponentialRampToValueAtTime(920, now + 0.08);
            osc.frequency.exponentialRampToValueAtTime(380, now + 0.24);

            gain.gain.setValueAtTime(0, now);
            gain.gain.linearRampToValueAtTime(0.26, now + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.32);

            osc.connect(gain).connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.34);
        }

        function playExplosion() {
            const ctx = ensureContext();
            if (!ctx) return;

            const now = ctx.currentTime;

            const noiseBuffer = ctx.createBuffer(1, ctx.sampleRate * 0.45, ctx.sampleRate);
            const noiseData = noiseBuffer.getChannelData(0);
            for (let i = 0; i < noiseData.length; i += 1) {
                noiseData[i] = (Math.random() * 2 - 1) * (1 - i / noiseData.length);
            }

            const noise = ctx.createBufferSource();
            noise.buffer = noiseBuffer;
            const noiseGain = ctx.createGain();
            noiseGain.gain.setValueAtTime(0, now);
            noiseGain.gain.linearRampToValueAtTime(0.8, now + 0.02);
            noiseGain.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
            noise.connect(noiseGain).connect(ctx.destination);
            noise.start(now);
            noise.stop(now + 0.5);
        }

        let chickenSoundBase = null;

        function getChickenSound() {
            if (!chickenSoundBase) {
                chickenSoundBase = new Audio('/sounds/chicken_noise.mp3');
                chickenSoundBase.preload = 'auto';
                chickenSoundBase.volume = 0.85;
            }
            return chickenSoundBase;
        }

        function playChickenSound() {
            const base = getChickenSound();
            if (!base) return;

            const play = (audioNode) => {
                audioNode.currentTime = 0;
                const promise = audioNode.play();
                if (promise && typeof promise.catch === 'function') {
                    promise.catch(() => { });
                }
            };

            if (base.paused) {
                play(base);
            } else {
                const clone = base.cloneNode(true);
                clone.volume = base.volume;
                play(clone);
            }
        }

        return {
            resume,
            playJump,
            playExplosion,
            playChickenSound,
        };
    }

    const audio = createAudioToolkit();

    function format(value) {
        return Math.floor(value).toLocaleString('fr-FR');
    }

    function displaySelectedBet() {
        if (!state.pendingBet) {
            elements.selectedBet.textContent = 'Aucune mise selectionnee';
            return;
        }
        elements.selectedBet.textContent = `Mise selectionnee : ${format(state.pendingBet)}`;
    }

    function calculMultiplier(steps) {
        if (steps <= 0) return 1;
        const mines = state.mineCount;
        const safeSlots = TOTAL_COLUMNS - mines;
        if (safeSlots <= 0) return 1;
        const effectiveSteps = Math.min(steps, safeSlots);
        let mult = 1;
        for (let i = 0; i < effectiveSteps; i += 1) {
            mult *= (TOTAL_COLUMNS - i) / (TOTAL_COLUMNS - mines - i);
        }
        return mult.toFixed(2);
    }

    function multiplierText(value, digits = 3) {
        return (value)
    }

    function columnMultiplierText(columnIndex) {
        if (columnIndex < 0 || columnIndex >= GOAL_COLUMNS) {
            return '';
        }
        const safeSlots = TOTAL_COLUMNS - state.mineCount;
        if (columnIndex + 1 > safeSlots) {
            return '';
        }
        return multiplierText(calculMultiplier(columnIndex + 1), 2);
    }

    function generateMines(count) {
        const mines = new Set();
        const target = Math.max(1, Math.min(count, TOTAL_COLUMNS - 1));
        while (mines.size < target) {
            mines.add(Math.floor(Math.random() * TOTAL_COLUMNS));
        }
        return mines;
    }

    function getChickenColumn() {
        if (state.boomColumn !== null) {
            return null;
        }
        if (Number.isInteger(state.chickenColumn)) {
            return state.chickenColumn;
        }
        if (state.roundActive) {
            return state.currentColumn;
        }
        return null;
    }

    function ensureChickenSprite() {
        if (!elements.track) return null;

        if (!state.chickenSprite) {
            const sprite = document.createElement('div');
            sprite.className = 'chicken-sprite is-hidden';

            const spriteImage = document.createElement('div');
            spriteImage.className = 'chicken-sprite__img';
            spriteImage.addEventListener('animationend', (event) => {
                if (event.animationName === 'chicken-hop') {
                    sprite.classList.remove('is-jumping');
                }
            });

            sprite.appendChild(spriteImage);
            state.chickenSprite = sprite;
        }

        if (!state.chickenSprite.isConnected) {
            elements.track.appendChild(state.chickenSprite);
        }

        return state.chickenSprite;
    }

    function hideChickenSprite() {
        if (!state.chickenSprite) return;
        state.chickenSprite.classList.add('is-hidden');
    }

    function updateChickenSpritePosition(columnIndex, { immediate = false } = {}) {
        const sprite = ensureChickenSprite();
        if (!sprite) return;

        if (!Number.isInteger(columnIndex)) {
            sprite.classList.add('is-hidden');
            return;
        }

        const cell = elements.track.querySelector(`.road-column[data-column="${columnIndex}"] .road-cell`);
        if (!cell) {
            sprite.classList.add('is-hidden');
            return;
        }

        const offsetX = cell.offsetLeft;
        const offsetY = cell.offsetTop;

        sprite.style.width = `${cell.offsetWidth}px`;
        sprite.style.height = `${cell.offsetHeight}px`;

        if (immediate) {
            sprite.classList.add('no-transition');
        } else {
            sprite.classList.remove('no-transition');
        }

        sprite.style.transform = `translate3d(${offsetX}px, ${offsetY}px, 0)`;
        sprite.classList.remove('is-hidden');

        if (immediate) {
            requestAnimationFrame(() => {
                if (state.chickenSprite) {
                    state.chickenSprite.classList.remove('no-transition');
                }
            });
        }
    }

    function triggerChickenJump() {
        const sprite = ensureChickenSprite();
        if (!sprite) return;

        audio.resume();
        audio.playJump();

        sprite.classList.remove('is-jumping');
        void sprite.offsetWidth;
        sprite.classList.add('is-jumping');
    }


    function resetRoundState() {
        state.bet = 0;
        state.difficulty = parseInt(elements.difficulty.value, 10) || 1;
        state.mineCount = Math.max(1, Math.min(state.difficulty, TOTAL_COLUMNS - 1));
        state.minePositions = new Set();
        state.visited = new Set();
        state.roundActive = false;
        state.currentColumn = 0;
        state.progress = 0;
        state.multiplier = 1;
        state.cashable = false;
        state.visibleStart = 0;
        state.boomColumn = null;
        state.chickenColumn = null;
        state.lastRenderedChickenColumn = null;

        elements.cashout.disabled = true;
        elements.newRound.style.display = 'none';
        elements.resultMessage.textContent = '';
        elements.resultMessage.classList.remove('result-win', 'result-loss');
        elements.mat.style.display = 'none';
        elements.betContainer.style.display = 'block';

        if (elements.track) {
            elements.track.innerHTML = '';
        }

        hideChickenSprite();

    }

    function renderGrid() {
        if (!elements.track) return;

        const fragment = document.createDocumentFragment();
        const chickenColumn = getChickenColumn();
        const nextColumn = state.currentColumn;
        const start = 0;
        state.visibleStart = start;
        const end = Math.min(start + VISIBLE_COLUMNS, GOAL_COLUMNS);

        for (let columnIndex = start; columnIndex < end; columnIndex += 1) {
            const wrapper = document.createElement('div');
            wrapper.className = 'road-column';
            wrapper.dataset.column = String(columnIndex);

            if (state.visited.has(columnIndex)) {
                wrapper.classList.add('is-passed');
            }
            if (columnIndex === chickenColumn) {
                wrapper.classList.add('is-current', 'has-chicken');
            } else if (state.roundActive && state.boomColumn === null && columnIndex === nextColumn) {
                wrapper.classList.add('is-next');
            }

            const cell = document.createElement('button');
            cell.type = 'button';
            cell.className = 'road-cell';
            cell.dataset.column = String(columnIndex);

            if (state.visited.has(columnIndex)) {
                cell.classList.add('safe');
            }
            if (state.boomColumn === columnIndex) {
                cell.classList.add('is-boom');
            }

            const clickable = state.roundActive && state.boomColumn === null && columnIndex === nextColumn;
            cell.disabled = !clickable;
            if (clickable) {
                cell.classList.add('is-active');
                cell.addEventListener('click', selectionCellule);
            }

            wrapper.appendChild(cell);

            const multiplier = document.createElement('div');
            multiplier.className = 'column-multiplier';
            multiplier.textContent = columnMultiplierText(columnIndex);
            wrapper.appendChild(multiplier);

            fragment.appendChild(wrapper);
        }

        elements.track.innerHTML = '';
        elements.track.appendChild(fragment);

        const previousColumn = state.lastRenderedChickenColumn;
        const isChickenColumnInt = Number.isInteger(chickenColumn);
        const shouldAnimate = Number.isInteger(previousColumn) && isChickenColumnInt && chickenColumn !== previousColumn;

        updateChickenSpritePosition(chickenColumn, { immediate: !shouldAnimate });

        if (shouldAnimate) {
            triggerChickenJump();
        }

        state.lastRenderedChickenColumn = isChickenColumnInt ? chickenColumn : null;
    }
    function updateHud() {
        const difficultyLabel = ['Facile', 'Medium', 'Difficile'][state.mineCount - 1] || `x${state.mineCount}`;
        const hasProgress = state.progress > 0 || state.boomColumn !== null;

        elements.roundInfo.textContent = (state.roundActive || hasProgress)
            ? `Difficulte : ${difficultyLabel}`
            : '';

        elements.multiplierInfo.textContent = hasProgress
            ? `Multiplicateur actuel : ${multiplierText(state.multiplier)}`
            : '';

        const potential = state.bet * state.multiplier;
        elements.payoutInfo.textContent = (state.roundActive && state.cashable) || (!state.roundActive && hasProgress)
            ? `Paiement potentiel : ${Number.isFinite(potential) ? format(potential) : 'Jackpot'}`
            : '';

        elements.cashout.disabled = !state.cashable || !state.roundActive;
    }

    function selectionCellule(event) {
        if (!state.roundActive || state.boomColumn !== null) return;

        const column = Number(event.currentTarget.dataset.column);
        if (column !== state.currentColumn) return;
        if (column >= GOAL_COLUMNS) return;

        if (state.minePositions.has(column)) {
            state.boomColumn = column;
            state.roundActive = false;
            state.cashable = false;
            state.currentColumn = column;
            audio.resume();
            audio.playExplosion();
            audio.playChickenSound();
            renderGrid();
            updateHud();
            endRound(false, 0);
            return;
        }

        state.visited.add(column);
        state.progress += 1;
        state.progress = Math.min(state.progress, GOAL_COLUMNS);
        state.currentColumn = Math.min(column + 1, GOAL_COLUMNS);
        state.chickenColumn = column;
        state.multiplier = calculMultiplier(state.progress);
        state.cashable = true;

        const reachedGoal = state.currentColumn >= GOAL_COLUMNS;
        if (reachedGoal) {
            state.roundActive = false;
        }

        renderGrid();
        updateHud();

        if (reachedGoal) {
            const payout = Math.floor(state.bet * state.multiplier);
            endRound(true, payout);
        }
    }

    function endRound(win, payout) {
        state.roundActive = false;
        state.cashable = false;
        elements.cashout.disabled = true;
        elements.newRound.style.display = 'inline-block';

        if (win) {
            elements.resultMessage.textContent = `Vous encaissez ${format(payout)} !`;
            if (window.Balance && payout > 0) {
                window.Balance.gain(Math.floor(payout));
            }
        } else {
            elements.resultMessage.textContent = `Mine detectee. Vous perdez ${format(state.bet)}.`;
        }

        updateHud();
    }

    function startRound() {
        state.bet = state.pendingBet;
        state.difficulty = parseInt(elements.difficulty.value, 10) || 1;
        state.mineCount = Math.max(1, Math.min(state.difficulty, TOTAL_COLUMNS - 1));
        state.minePositions = generateMines(state.mineCount);
        state.visited = new Set();
        state.roundActive = true;
        state.currentColumn = 0;
        state.progress = 0;
        state.multiplier = 1;
        state.cashable = false;
        state.visibleStart = 0;
        state.boomColumn = null;
        state.chickenColumn = state.currentColumn;
        state.lastRenderedChickenColumn = null;

        elements.betContainer.style.display = 'none';
        elements.mat.style.display = 'block';
        elements.newRound.style.display = 'none';
        elements.resultMessage.textContent = '';
        elements.resultMessage.classList.remove('result-win', 'result-loss');
        renderGrid();
        updateHud();
    }

    function handleTokenClick(event) {
        const value = parseInt(event.currentTarget.dataset.value, 10);
        state.pendingBet += value;
        displaySelectedBet();
    }

    function initTokens() {
        elements.tokens.forEach((token) => {
            token.addEventListener('click', handleTokenClick);
        });
    }

    function handlePlaceBet() {
        if (state.roundActive) return;
        if (!state.pendingBet) {
            elements.resultMessage.textContent = 'Selectionnez une mise avant de commencer.';
            return;
        }
        if (window.Balance && !window.Balance.canMise(state.pendingBet)) {
            elements.resultMessage.textContent = 'Solde insuffisant pour cette mise.';
            return;
        }
        if (window.Balance && !window.Balance.miser(state.pendingBet)) {
            elements.resultMessage.textContent = 'Impossible de placer la mise.';
            return;
        }
        startRound();
    }

    function clearBet() {
        if (state.roundActive) return;
        state.pendingBet = 0;
        displaySelectedBet();
    }

    function cashout() {
        if (!state.roundActive || !state.cashable) return;
        playCashout();
        const payout = Math.floor(state.bet * state.multiplier);
        state.roundActive = false;
        state.cashable = false;
        renderGrid();
        endRound(true, payout);
    }

    function newRound() {
        const keepBet = state.pendingBet;
        resetRoundState();
        state.pendingBet = keepBet;
        displaySelectedBet();
    }

    function init() {
        resetRoundState();
        displaySelectedBet();
        initTokens();
        elements.difficulty.addEventListener('change', () => {
            if (!state.roundActive) {
                state.difficulty = parseInt(elements.difficulty.value, 10) || 1;
                state.mineCount = Math.max(1, Math.min(state.difficulty, GOAL_COLUMNS - 1));
            }
        });
        elements.placeBet.addEventListener('click', handlePlaceBet);
        elements.clearBet.addEventListener('click', clearBet);
        elements.cashout.addEventListener('click', cashout);
        elements.newRound.addEventListener('click', newRound);

        window.addEventListener('resize', () => {
            updateChickenSpritePosition(getChickenColumn(), { immediate: true });
        });

        if (window.Balance) {
            window.Balance.init({
                displaySelectors: ['#chicken-balance', '[data-balance]'],
            });
        }
    }

    document.addEventListener('DOMContentLoaded', init);
})();
