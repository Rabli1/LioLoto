(function () {
    const SYMBOLS = [
        { name: 'DIAMOND', label: 'Diamant', weight: 3, icon: '/img/slot/diamond.svg', payouts: { 3: 100 } },
        { name: 'SEVEN', label: 'Sept', weight: 7, icon: '/img/slot/seven.svg', payouts: { 3: 45 } },
        { name: 'BAR', label: 'Bar', weight: 15, icon: '/img/slot/bar.svg', payouts: { 3: 25, 2: 4 } },
        { name: 'CHERRY', label: 'Cerise', weight: 25, icon: '/img/slot/cherry.svg', payouts: { 3: 15, 2: 2 } },
        { name: 'ORANGE', label: 'Orange', weight: 20, icon: '/img/slot/orange.svg', payouts: { 3: 12 } },
        { name: 'LEMON', label: 'Citron', weight: 30, icon: '/img/slot/lemon.svg', payouts: { 3: 10 } },
    ];

    const SYMBOL_LOOKUP = SYMBOLS.reduce((acc, symbol) => {
        acc[symbol.name] = symbol;
        return acc;
    }, {});

    const TOTAL_WEIGHT = SYMBOLS.reduce((sum, symbol) => sum + symbol.weight, 0);
    const formatter = new Intl.NumberFormat('fr-FR');
    const PLACEHOLDER_HTML = '<span class="slot-symbol-placeholder">---</span>';

    const state = {
        bet: 0,
        spinning: false,
        activeTimers: [],
        reels: [],
        lastWin: 0,
    };

    function formatAmount(value) {
        const numeric = Number(value);
        if (!Number.isFinite(numeric)) {
            return '0';
        }
        return formatter.format(Math.max(0, Math.floor(numeric)));
    }

    const audioFX = (() => {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) {
            return {
                prime: () => null,
                spin: () => {},
                tick: () => {},
                win: () => {},
                lose: () => {},
            };
        }

        let ctx = null;

        function ensureContext() {
            if (!ctx) {
                ctx = new AudioCtx();
            }
            if (ctx.state === 'suspended') {
                ctx.resume();
            }
            return ctx;
        }

        function tone({ frequency, duration, type = 'sine', volume = 0.2, delay = 0 }) {
            const context = ensureContext();
            if (!context) return;

            const safeDuration = Math.max(duration, 0.05);
            const start = context.currentTime + delay;
            const oscillator = context.createOscillator();
            const gain = context.createGain();

            oscillator.type = type;
            oscillator.frequency.setValueAtTime(frequency, start);

            gain.gain.setValueAtTime(0.0001, start);
            gain.gain.linearRampToValueAtTime(volume, start + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, start + safeDuration);

            oscillator.connect(gain).connect(context.destination);
            oscillator.start(start);
            oscillator.stop(start + safeDuration + 0.05);
        }

        return {
            prime: ensureContext,
            spin() {
                tone({ frequency: 420, duration: 0.28, type: 'square', volume: 0.18 });
                tone({ frequency: 260, duration: 0.4, type: 'sawtooth', volume: 0.12, delay: 0.18 });
            },
            tick() {
                tone({ frequency: 760, duration: 0.08, type: 'square', volume: 0.12 });
            },
            win() {
                tone({ frequency: 660, duration: 0.45, type: 'triangle', volume: 0.22 });
                tone({ frequency: 880, duration: 0.35, type: 'square', volume: 0.18, delay: 0.2 });
                tone({ frequency: 1180, duration: 0.25, type: 'triangle', volume: 0.15, delay: 0.38 });
            },
            lose() {
                tone({ frequency: 190, duration: 0.38, type: 'sine', volume: 0.14 });
            },
        };
    })();

    if (typeof audioFX.prime === 'function') {
        window.addEventListener('pointerdown', () => audioFX.prime(), { once: true });
    }

    function dispatchGameEvent(name, detail = {}) {
        document.dispatchEvent(new CustomEvent(name, { detail }));
    }

    function renderSymbolContent(node, symbol) {
        if (!node) return;
        if (!symbol) {
            node.innerHTML = PLACEHOLDER_HTML;
            return;
        }
        node.innerHTML = `<img src="${symbol.icon}" alt="${symbol.label}" loading="lazy">`;
    }

    function randomSymbol() {
        let roll = Math.random() * TOTAL_WEIGHT;
        for (let i = 0; i < SYMBOLS.length; i += 1) {
            roll -= SYMBOLS[i].weight;
            if (roll < 0) {
                return SYMBOLS[i];
            }
        }
        return SYMBOLS[SYMBOLS.length - 1];
    }

    function clearReelStates() {
        state.reels.forEach((reel) => {
            reel.classList.remove('is-win');
            reel.classList.remove('is-spinning');
            reel.dataset.symbol = '';
            const symbolNode = reel.querySelector('.slot-symbol');
            if (symbolNode) {
                renderSymbolContent(symbolNode, null);
            }
        });
    }

    function highlightWinningReels(symbolName) {
        if (!symbolName) return;
        state.reels.forEach((reel) => {
            if (reel.dataset.symbol === symbolName) {
                reel.classList.add('is-win');
            }
        });
    }

    function evaluateCombo(combo) {
        const counts = new Map();
        combo.forEach((symbol) => {
            counts.set(symbol.name, (counts.get(symbol.name) || 0) + 1);
        });

        let best = { multiplier: 0, symbol: null, count: 0 };

        counts.forEach((count, name) => {
            const symbol = SYMBOL_LOOKUP[name];
            if (!symbol) return;
            const multiplier = symbol.payouts[count];
            if (multiplier && multiplier > best.multiplier) {
                best = { multiplier, symbol, count };
            }
        });

        const result = {
            payout: 0,
            message: 'Perdu, retentez votre chance.',
            winningSymbol: best.symbol,
        };

        if (best.multiplier > 0) {
            result.payout = Math.max(0, Math.floor(state.bet * best.multiplier));
            const label = best.symbol.label;
            if (best.count === 3) {
                result.message = `Triple ${label}! Gain de ${formatAmount(result.payout)}.`;
            } else if (best.count === 2) {
                result.message = `Paire de ${label}! Gain de ${formatAmount(result.payout)}.`;
            }
        }

        return result;
    }

    function spinReel(reelElement, finalSymbol, reelIndex, onComplete) {
        const symbolNode = reelElement.querySelector('.slot-symbol');
        if (!symbolNode) {
            onComplete();
            return;
        }

        const intervalTime = 90;
        const spinDuration = 1200 + reelIndex * 400;
        let elapsed = 0;
        let tickCount = 0;

        reelElement.classList.add('is-spinning');

        const timer = setInterval(() => {
            elapsed += intervalTime;
            tickCount += 1;

            const interim = randomSymbol();
            renderSymbolContent(symbolNode, interim);
            reelElement.dataset.symbol = interim.name;

            if (tickCount % 2 === 0) {
                audioFX.tick();
            }

            if (elapsed >= spinDuration) {
                clearInterval(timer);
                renderSymbolContent(symbolNode, finalSymbol);
                reelElement.dataset.symbol = finalSymbol.name;
                reelElement.classList.remove('is-spinning');
                onComplete();
            }
        }, intervalTime);

        state.activeTimers.push(timer);
    }

    function setupSlotMachine() {
        const spinButton = document.getElementById('slot-spin');
        if (!spinButton) return;

        const betTokens = document.querySelectorAll('.slot-bet-token');
        const clearButton = document.getElementById('slot-clear-bet');
        const betLabel = document.getElementById('slot-selected-bet');
        const statusLabel = document.getElementById('slot-status');
        const currentBetLabel = document.getElementById('slot-current-bet');
        const lastWinLabel = document.getElementById('slot-last-win');

        state.reels = Array.from(document.querySelectorAll('.slot-reel'));
        state.reels.forEach((reel) => {
            const symbolNode = reel.querySelector('.slot-symbol');
            if (symbolNode) {
                renderSymbolContent(symbolNode, null);
            }
        });

        const defaultSpinLabel = spinButton.textContent.trim() || 'Lancer';

        function updateBetUI() {
            const formatted = formatAmount(state.bet);
            if (betLabel) {
                betLabel.textContent = state.bet > 0
                    ? `Mise selectionnee : ${formatted}`
                    : 'Aucune mise selectionnee';
            }
            if (currentBetLabel) {
                currentBetLabel.textContent = formatted;
            }
        }

        function resetSpinButton() {
            spinButton.disabled = false;
            spinButton.textContent = defaultSpinLabel;
        }

        function finishSpin(results) {
            state.activeTimers.forEach((timerId) => clearInterval(timerId));
            state.activeTimers = [];
            state.spinning = false;
            resetSpinButton();

            const outcome = evaluateCombo(results);
            const balanceApi = window.Balance;

            if (outcome.payout > 0 && balanceApi && typeof balanceApi.gain === 'function') {
                balanceApi.gain(outcome.payout);
            }

            state.lastWin = outcome.payout;
            if (lastWinLabel) {
                lastWinLabel.textContent = formatAmount(outcome.payout);
            }
            if (statusLabel) {
                statusLabel.textContent = outcome.message;
            }

            highlightWinningReels(outcome.winningSymbol ? outcome.winningSymbol.name : null);

            dispatchGameEvent('slotmachine:result', {
                bet: state.bet,
                payout: outcome.payout,
                symbols: results.map((symbol) => symbol.name),
            });

            if (outcome.payout > 0) {
                audioFX.win();
            } else {
                audioFX.lose();
            }
        }

        function startSpin() {
            if (state.spinning) return;
            if (state.bet <= 0) {
                if (statusLabel) {
                    statusLabel.textContent = 'Choisissez une mise avant de lancer.';
                }
                return;
            }

            const balanceApi = window.Balance;
            if (balanceApi && typeof balanceApi.miser === 'function') {
                if (typeof balanceApi.canMise === 'function' && !balanceApi.canMise(state.bet)) {
                    if (statusLabel) {
                        statusLabel.textContent = 'Solde insuffisant pour cette mise.';
                    }
                    return;
                }
                if (!balanceApi.miser(state.bet)) {
                    if (statusLabel) {
                        statusLabel.textContent = 'Solde insuffisant.';
                    }
                    return;
                }
            }

            state.spinning = true;
            state.lastWin = 0;
            state.activeTimers.forEach((timerId) => clearInterval(timerId));
            state.activeTimers = [];
            clearReelStates();
            state.reels.forEach((reel) => {
                const symbolNode = reel.querySelector('.slot-symbol');
                if (symbolNode) {
                    renderSymbolContent(symbolNode, null);
                }
            });

            if (lastWinLabel) {
                lastWinLabel.textContent = '0';
            }

            if(statusLabel) {
                statusLabel.textContent = '';
            }

            spinButton.disabled = true;
            spinButton.textContent = 'En cours...';

            audioFX.spin();
            dispatchGameEvent('slotmachine:spin', { bet: state.bet });

            const results = [randomSymbol(), randomSymbol(), randomSymbol()];
            let completed = 0;

            state.reels.forEach((reel, index) => {
                spinReel(reel, results[index], index, () => {
                    completed += 1;
                    if (completed === state.reels.length) {
                        finishSpin(results);
                    }
                });
            });
        }

        betTokens.forEach((token) => {
            token.addEventListener('click', () => {
                const value = parseInt(token.getAttribute('data-value'), 10);
                if (!Number.isFinite(value) || value <= 0) {
                    return;
                }
                state.bet += value;
                updateBetUI();
                dispatchGameEvent('slotmachine:bet-change', { bet: state.bet });
            });
        });

        if (clearButton) {
            clearButton.addEventListener('click', () => {
                state.bet = 0;
                updateBetUI();
                if (statusLabel) {
                    statusLabel.textContent = 'Mise reinitialisee.';
                }
                dispatchGameEvent('slotmachine:bet-change', { bet: state.bet });
            });
        }

        spinButton.addEventListener('click', startSpin);

        updateBetUI();
        if (statusLabel) {
            statusLabel.textContent = 'Selectionnez une mise pour commencer.';
        }
        if (lastWinLabel) {
            lastWinLabel.textContent = '0';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.Balance && typeof window.Balance.init === 'function') {
            window.Balance.init({
                session: window.gameSession || {},
                displaySelectors: ['#slot-balance'],
            });
        }
        setupSlotMachine();
    });
})();
