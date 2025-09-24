(function () {
    const session = window.plinkoSession || {};

    const elements = {};
    const state = {
        rows: 8,
        difficulty: "medium",
        balance: Number(session.balance ?? 0) || 0,
        selectedBet: 0,
        dropping: false,
        multipliers: [],
        history: [],
    };

    const config = {
        minRows: 6,
        maxRows: 12,
        dropDuration: 1200,
        edgeBias: {
            easy: 0.0,
            medium: 0.6,
            hard: 1.1,
        },
        difficultyProfiles: {
            easy: { center: 0.9, edge: 5.5, curve: 1.0, label: "Facile" },
            medium: { center: 0.6, edge: 9.0, curve: 1.18, label: "Moyen" },
            hard: { center: 0.35, edge: 14.0, curve: 1.32, label: "Difficile" },
        },
        historyLimit: 12,
    };

    const api = {
        saveBalance: session.endpoints?.saveBalance || "/game/balance",
        csrfToken: session.csrfToken || document.querySelector('meta[name="csrf-token"]').content,
        userId: session.userId || null,
    };

    document.addEventListener("DOMContentLoaded", initialise);

    function initialise() {
        cacheElements();
        wireInputs();
        buildBoard();
        updateMultipliers();
        updateBalanceDisplay();
        updateBetPickers();
        updateDropButton();
        setStatus("Selectionnez une mise puis ajustez la difficulte ou la taille avant de lancer.");
    }

    function cacheElements() {
        elements.balance = document.getElementById("plinko-balance");
        elements.difficulty = document.getElementById("plinko-difficulty");
        elements.size = document.getElementById("plinko-size");
        elements.sizeLabel = document.getElementById("plinko-size-label");
        elements.betInput = document.getElementById("plinko-bet");
        elements.betChips = Array.from(document.querySelectorAll(".bet-chip"));
        elements.drop = document.getElementById("plinko-drop");
        elements.board = document.getElementById("plinko-board");
        elements.multipliers = document.getElementById("plinko-multipliers");
        elements.status = document.getElementById("plinko-status");
        elements.history = document.getElementById("plinko-history");
    }

    function wireInputs() {
        if (elements.difficulty) {
            elements.difficulty.addEventListener("change", () => {
                state.difficulty = elements.difficulty.value;
                updateMultipliers();
            });
        }

        if (elements.size) {
            elements.size.addEventListener("input", () => {
                state.rows = Number(elements.size.value);
                if (elements.sizeLabel) {
                    elements.sizeLabel.textContent = state.rows;
                }
                buildBoard();
                updateMultipliers();
            });
        }

        if (elements.betInput) {
            elements.betInput.addEventListener("input", () => {
                const value = Math.max(0, Math.floor(Number(elements.betInput.value) || 0));
                state.selectedBet = value;
                setActiveChip(null);
                updateBetPickers();
                updateDropButton();
            });
        }

        elements.betChips.forEach((chip) => {
            chip.addEventListener("click", () => {
                if (state.dropping) {
                    return;
                }
                const value = Number(chip.dataset.bet || 0);
                state.selectedBet = value;
                if (elements.betInput) {
                    elements.betInput.value = "";
                }
                setActiveChip(chip);
                updateBetPickers();
                updateDropButton();
            });
        });

        if (elements.drop) {
            elements.drop.addEventListener("click", dropBall);
        }
    }

    function setActiveChip(activeChip) {
        elements.betChips.forEach((chip) => {
            chip.classList.toggle("active", chip === activeChip);
        });
    }

    function updateBetPickers() {
        if (elements.betInput && !elements.betInput.matches(":focus")) {
            const activeChip = elements.betChips.find((chip) => chip.classList.contains("active"));
            if (activeChip) {
                elements.betInput.value = "";
            } else if (state.selectedBet > 0) {
                elements.betInput.value = state.selectedBet;
            } else {
                elements.betInput.value = "";
            }
        }

        if (elements.status && !state.dropping) {
            if (state.selectedBet > 0) {
                setStatus("Mise selectionnee : " + state.selectedBet + ". Ajustez les parametres ou lancez la bille.");
            } else {
                setStatus("Selectionnez une mise puis ajustez la difficulte ou la taille avant de lancer.");
            }
        }
    }

    function buildBoard() {
        if (!elements.board) {
            return;
        }
        elements.board.innerHTML = "";
        for (let row = 0; row < state.rows; row += 1) {
            const rowEl = document.createElement("div");
            rowEl.className = "pin-row";
            rowEl.style.marginTop = row === 0 ? "0" : "18px";
            for (let pinIndex = 0; pinIndex <= row; pinIndex += 1) {
                const pin = document.createElement("div");
                pin.className = "pin";
                rowEl.appendChild(pin);
            }
            elements.board.appendChild(rowEl);
        }
    }

    function updateMultipliers() {
        if (!elements.multipliers) {
            return;
        }
        const multipliers = generateMultipliers(state.rows, state.difficulty);
        state.multipliers = multipliers;
        elements.multipliers.style.gridTemplateColumns = "repeat(" + multipliers.length + ", 1fr)";
        elements.multipliers.innerHTML = multipliers
            .map((value, index) => "<div class=\"multiplier-slot\" data-slot=\"" + index + "\" data-multiplier=\"" + value + "\">" + value.toFixed(2) + "x</div>")
            .join("");
    }

    function generateMultipliers(rows, difficulty) {
        const profile = config.difficultyProfiles[difficulty] || config.difficultyProfiles.medium;
        const slots = rows + 1;
        const center = rows / 2;
        const weights = computeSlotWeights(rows, difficulty);
        const totalWeight = weights.reduce((sum, w) => sum + w, 0);
        const result = [];

        for (let slot = 0; slot < slots; slot += 1) {
            const distance = Math.abs(slot - center);
            const normalized = Math.pow(distance / Math.max(center, 1), profile.curve);
            const baseMultiplier = profile.center + (profile.edge - profile.center) * normalized;
            const probabilityFactor = totalWeight > 0 ? totalWeight / Math.max(weights[slot], 1) : 1;
            const adjusted = baseMultiplier * probabilityFactor;
            result.push(Math.max(0.1, Number(adjusted.toFixed(2))));
        }

        return result;
    }

    function computeSlotWeights(rows, difficulty) {
        const bias = config.edgeBias[difficulty] ?? config.edgeBias.medium;
        const center = rows / 2;
        const weights = [];
        for (let k = 0; k <= rows; k += 1) {
            const base = combination(rows, k);
            const distance = Math.abs(k - center);
            const weight = base * (1 + bias * distance);
            weights.push(weight);
        }
        return weights;
    }

    function updateBalanceDisplay() {
        if (elements.balance) {
            elements.balance.textContent = formatNumber(Math.max(0, Math.floor(state.balance)));
        }
        window.plinkoSession = window.plinkoSession || {};
        window.plinkoSession.balance = state.balance;
    }

    function updateDropButton() {
        if (!elements.drop) {
            return;
        }
        const canDrop = !state.dropping && state.selectedBet > 0 && state.balance >= state.selectedBet;
        elements.drop.disabled = !canDrop;
    }

    function dropBall() {
        if (state.dropping) {
            return;
        }

        const bet = state.selectedBet;
        if (!bet || state.balance < bet) {
            setStatus("Solde insuffisant pour cette mise.");
            updateDropButton();
            return;
        }

        state.dropping = true;
        updateDropButton();

        state.balance -= bet;
        updateBalanceDisplay();
        persistBalance();

        const boardRect = elements.board.getBoundingClientRect();
        const slots = state.rows + 1;
        const slotSpacing = boardRect.width / slots;
        const stepHeight = Math.max((boardRect.height - 80) / state.rows, 18);
        const weights = computeSlotWeights(state.rows, state.difficulty);
        const finalSlot = sampleSlot(weights);
        const steps = buildPath(finalSlot, state.rows);

        const ballSize = 22;
        const startSlot = state.rows / 2;
        const startX = slotSpacing * startSlot + slotSpacing / 2 - ballSize / 2;

        const ball = document.createElement("div");
        ball.className = "ball";
        ball.style.left = startX + "px";
        ball.style.top = "-24px";
        elements.board.appendChild(ball);

        highlightSlot(-1);
        setStatus("La bille descend...");

        const moveInterval = Math.max(40, Math.floor(config.dropDuration / Math.max(state.rows, 1)));
        animateBall(ball, startSlot, slotSpacing, stepHeight, steps, ballSize, startX, moveInterval, () => {
            const finalMultiplier = state.multipliers[finalSlot];
            const payout = Number((bet * finalMultiplier).toFixed(2));
            state.balance += payout;
            updateBalanceDisplay();
            persistBalance();

            const net = payout - bet;
            if (net >= 0) {
                setStatus("Gain: " + payout.toFixed(2) + " (multiplicateur " + finalMultiplier.toFixed(2) + "x).");
            } else {
                setStatus("Perte: " + Math.abs(net).toFixed(2) + " (multiplicateur " + finalMultiplier.toFixed(2) + "x).");
            }

            pushHistory({
                multiplier: finalMultiplier,
                payout: payout,
                bet: bet,
                slot: finalSlot,
                rows: state.rows,
                difficulty: state.difficulty,
            });

            highlightSlot(finalSlot);
            elements.board.removeChild(ball);
            state.dropping = false;
            updateDropButton();
        });
    }

    function animateBall(ball, startSlot, slotSpacing, stepHeight, steps, ballSize, startX, moveInterval, done) {
        let currentSlot = startSlot;
        let index = 0;

        function advance() {
            if (index >= steps.length) {
                done();
                return;
            }
            currentSlot += steps[index] * 0.5;
            const x = slotSpacing * currentSlot + slotSpacing / 2 - ballSize / 2;
            const y = stepHeight * (index + 1);
            ball.style.transform = "translate(" + (x - startX) + "px, " + y + "px)";
            index += 1;
            setTimeout(advance, moveInterval);
        }

        advance();
    }

    function buildPath(finalSlot, rows) {
        const steps = [];
        for (let i = 0; i < rows; i += 1) {
            steps.push(i < finalSlot ? 1 : -1);
        }
        shuffleArray(steps);
        return steps;
    }

    function sampleSlot(weights) {
        const total = weights.reduce((sum, w) => sum + w, 0);
        let pick = Math.random() * total;
        for (let i = 0; i < weights.length; i += 1) {
            pick -= weights[i];
            if (pick <= 0) {
                return i;
            }
        }
        return weights.length - 1;
    }

    function highlightSlot(index) {
        if (!elements.multipliers) {
            return;
        }
        const slots = elements.multipliers.querySelectorAll(".multiplier-slot");
        slots.forEach((slot, slotIndex) => {
            slot.classList.toggle("win", slotIndex === index);
        });
    }

    function pushHistory(entry) {
        state.history.unshift(entry);
        if (state.history.length > config.historyLimit) {
            state.history.pop();
        }

        if (!elements.history) {
            return;
        }

        elements.history.innerHTML = state.history
            .map((item) => {
                const biasLabel = config.difficultyProfiles[item.difficulty]?.label || item.difficulty;
                const net = item.payout - item.bet;
                const netLabel = net >= 0 ? "+" + net.toFixed(2) : net.toFixed(2);
                const outcomeClass = net >= 0 ? "win" : "loss";
                return "<div class=\"history-entry " + outcomeClass + "\"><span>" + biasLabel + " ? " + item.rows + " rangees ? " + item.multiplier.toFixed(2) + "x</span><span>" + netLabel + "</span></div>";
            })
            .join("");
    }

    function persistBalance() {
        if (!api.userId) {
            return;
        }

        fetch(api.saveBalance, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": api.csrfToken,
            },
            body: JSON.stringify({
                balance: Math.max(0, Math.floor(state.balance)),
                game: "plinko",
            }),
        }).catch((error) => {
            console.warn("Impossible de sauvegarder le solde Plinko", error);
        });
    }

    function setStatus(message) {
        if (elements.status) {
            elements.status.textContent = message;
        }
    }

    function formatNumber(value) {
        return value.toLocaleString("fr-CA");
    }

    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i -= 1) {
            const j = Math.floor(Math.random() * (i + 1));
            const tmp = array[i];
            array[i] = array[j];
            array[j] = tmp;
        }
    }

    function combination(n, k) {
        if (k < 0 || k > n) {
            return 0;
        }
        if (k === 0 || k === n) {
            return 1;
        }
        k = Math.min(k, n - k);
        let result = 1;
        for (let i = 1; i <= k; i += 1) {
            result = result * (n - k + i) / i;
        }
        return result;
    }
})();
