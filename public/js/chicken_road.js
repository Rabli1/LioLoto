(function () {
        const TOTAL_COLUMNS = 13;
        const GOAL_COLUMNS = 10;
        const VISIBLE_COLUMNS = 10;

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

            elements.cashout.disabled = true;
            elements.newRound.style.display = 'none';
            elements.resultMessage.textContent = '';
            elements.resultMessage.classList.remove('result-win', 'result-loss');
            elements.mat.style.display = 'none';
            elements.betContainer.style.display = 'block';

            if (elements.track) {
                elements.track.innerHTML = '';
            }
            
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
        }
        function updateHud() {
            const difficultyLabel = ['Facile', 'Medium', 'Difficile'][state.mineCount - 1] || `x${state.mineCount}`;
            const hasProgress = state.progress > 0 || state.boomColumn !== null;

            elements.roundInfo.textContent = (state.roundActive || hasProgress)
                ? `Difficulte : ${difficultyLabel} (${state.mineCount} mine${state.mineCount > 1 ? 's' : ''})`
                : '';

            elements.progressInfo.textContent = hasProgress
                ? `Distance parcourue : ${state.progress}`
                : '';

            elements.multiplierInfo.textContent = hasProgress
                ? `Multiplicateur actuel : ${multiplierText(state.multiplier)}`
                : '';

            const nextMultiplier = state.roundActive ? calculMultiplier(state.progress + 1) : null;
            elements.nextMultiplierInfo.textContent = state.roundActive && state.currentColumn < GOAL_COLUMNS
                ? `Prochaine etape : ${multiplierText(nextMultiplier)}`
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

            if (window.Balance) {
                window.Balance.init({
                    displaySelectors: ['#chicken-balance', '[data-balance]'],
                });
            }
        }

        document.addEventListener('DOMContentLoaded', init);
    })();