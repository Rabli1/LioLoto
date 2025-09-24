(function () {
    const session = window.blackjackSession || null;
    const csrfToken = session ? document.querySelector('meta[name="csrf-token"]').content : null;

    const elements = {};
    const state = {
        deck: [],
        playerHand: [],
        dealerHand: [],
        hiddenCard: null,
        playerTotal: 0,
        dealerTotal: 0,
        balance: 0,
        betAmount: 0,
        roundActive: false,
        dealerRevealed: false,
    };

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        cacheElements();
        state.balance = deriveInitialBalance();
        updateBalanceDisplay(state.balance);
        if (!elements.hit || !elements.stay) {
            console.warn('Blackjack UI not found for blackjack_alt.js');
            return;
        }
        wireBetControls();
        wireActionButtons();
        resetView();
        buildDeck();
        shuffleDeck();
    }

    function cacheElements() {
        elements.hit = document.getElementById('hit');
        elements.stay = document.getElementById('stay');
        elements.betPanel = document.getElementById('betContainer');
        elements.betChips = Array.from(document.querySelectorAll('.betToken'));
        elements.clearBet = document.getElementById('clearBet');
        elements.placeBet = document.getElementById('placeBet');
        elements.betAmountLabel = document.getElementById('betAmount');
        elements.selectedBetLabel = document.getElementById('selectedBet');
        elements.balanceLabel = document.getElementById('playerBalance');
        elements.gameMat = document.getElementById('gameMat');
        elements.dealerZone = document.getElementById('dealerContainer');
        elements.playerZone = document.getElementById('playerContainer');
        elements.dealerSum = document.getElementById('dealerSum');
        elements.playerSum = document.getElementById('playerSum');
    }

    function wireBetControls() {
        elements.betChips.forEach((chip) => {
            chip.addEventListener('click', () => {
                if (state.roundActive) {
                    return;
                }
                elements.betChips.forEach((btn) => btn.classList.remove('active'));
                chip.classList.add('active');
                state.betAmount = parseInt(chip.dataset.value, 10) || 0;
                updateBetDisplays();
            });
        });

        if (elements.clearBet) {
            elements.clearBet.addEventListener('click', () => {
                if (state.roundActive) {
                    return;
                }
                elements.betChips.forEach((btn) => btn.classList.remove('active'));
                state.betAmount = 0;
                updateBetDisplays();
            });
        }

        if (elements.placeBet) {
            elements.placeBet.addEventListener('click', placeBet);
        }
    }

    function wireActionButtons() {
        elements.hit.addEventListener('click', hit);
        elements.stay.addEventListener('click', stay);
        toggleActions(false);
    }

    function placeBet() {
        if (state.roundActive) {
            return;
        }
        if (!state.betAmount) {
            alert('Choisissez une mise avant de valider.');
            return;
        }

        const balance = getCurrentBalance();
        if (balance < state.betAmount) {
            alert('Solde insuffisant.');
            return;
        }

        const newBalance = balance - state.betAmount;
        updateBalanceDisplay(newBalance);
        persistBalance(newBalance);

        if (elements.betPanel) elements.betPanel.style.display = 'none';
        if (elements.gameMat) elements.gameMat.style.display = 'block';

        startRound();
    }

    function startRound() {
        state.roundActive = true;
        state.playerHand = [];
        state.dealerHand = [];
        state.dealerRevealed = false;

        if (state.deck.length < 15) {
            buildDeck();
            shuffleDeck();
        }

        state.playerHand.push(drawCard());
        state.dealerHand.push(drawCard());
        state.playerHand.push(drawCard());
        state.hiddenCard = drawCard();
        state.dealerHand.push(state.hiddenCard);

        state.playerTotal = calculateTotal(state.playerHand);
        state.dealerTotal = calculateTotal([state.dealerHand[0], state.hiddenCard]);

        renderHands();
        updateTotals();
        updateBetDisplays();
        toggleActions(true);

        if (state.dealerTotal === 21 || state.playerTotal === 21) {
            state.roundActive = false;
            toggleActions(false);
            revealDealer();
            setTimeout(() => {
                if (state.dealerTotal === 21 && state.playerTotal === 21) {
                    alert('Egalite !');
                    refundBet();
                } else if (state.dealerTotal === 21) {
                    alert('Blackjack du croupier.');
                } else {
                    alert('Blackjack ! Vous gagnez.');
                    payWin(1.5);
                }
                resetTable();
            }, 600);
        }
    }

    function hit() {
        if (!state.roundActive) {
            return;
        }
        state.playerHand.push(drawCard());
        state.playerTotal = calculateTotal(state.playerHand);
        renderHands();
        updateTotals();

        if (state.playerTotal > 21) {
            state.roundActive = false;
            setTimeout(() => {
                alert('Vous avez depasse 21.');
                resetTable();
            }, 500);
        }
    }

    function stay() {
        if (!state.roundActive) {
            return;
        }
        state.roundActive = false;
        revealDealer();
        playDealer();
    }

    function playDealer() {
        const step = () => {
            if (state.dealerTotal < 17) {
                state.dealerHand.push(drawCard());
                state.dealerTotal = calculateTotal(state.dealerHand);
                renderHands();
                updateTotals();
                setTimeout(step, 600);
            } else {
                setTimeout(resolveRound, 600);
            }
        };
        step();
    }

    function resolveRound() {
        if (state.dealerTotal > 21) {
            alert('Le croupier depasse 21.');
            payWin(1);
        } else if (state.dealerTotal > state.playerTotal) {
            alert('Le croupier gagne.');
        } else if (state.dealerTotal < state.playerTotal) {
            alert('Vous gagnez !');
            payWin(1);
        } else {
            alert('Egalite.');
            refundBet();
        }
        resetTable();
    }

    function revealDealer() {
        state.dealerRevealed = true;
        renderHands();
        updateTotals();
    }

    function renderHands() {
        if (elements.dealerZone) {
            elements.dealerZone.innerHTML = '';
            state.dealerHand.forEach((card, index) => {
                const img = document.createElement('img');
                if (!state.dealerRevealed && index === 1) {
                    img.src = '/img/cards/BACK.png';
                    img.alt = 'Hidden';
                } else {
                    img.src = `/img/cards/${card}.png`;
                    img.alt = card;
                }
                elements.dealerZone.appendChild(img);
            });
        }

        if (elements.playerZone) {
            elements.playerZone.innerHTML = '';
            state.playerHand.forEach((card) => {
                const img = document.createElement('img');
                img.src = `/img/cards/${card}.png`;
                img.alt = card;
                elements.playerZone.appendChild(img);
            });
        }
    }

    function updateTotals() {
        if (elements.dealerSum) {
            if (state.dealerRevealed) {
                elements.dealerSum.textContent = state.dealerTotal;
            } else if (state.dealerHand.length) {
                elements.dealerSum.textContent = calculateTotal([state.dealerHand[0]]);
            } else {
                elements.dealerSum.textContent = '0';
            }
        }

        if (elements.playerSum) {
            elements.playerSum.textContent = state.playerTotal || 0;
        }
    }

    function resetTable() {
        toggleActions(false);
        state.roundActive = false;
        state.playerHand = [];
        state.dealerHand = [];
        state.playerTotal = 0;
        state.dealerTotal = 0;
        state.hiddenCard = null;
        state.betAmount = 0;
        state.dealerRevealed = false;

        renderHands();
        updateTotals();
        updateBetDisplays();

        if (Array.isArray(elements.betChips)) {
            elements.betChips.forEach((btn) => btn.classList.remove('active'));
        }

        if (elements.betPanel) elements.betPanel.style.display = 'block';
        if (elements.gameMat) elements.gameMat.style.display = 'none';
    }

    function toggleActions(enabled) {
        elements.hit.disabled = !enabled;
        elements.stay.disabled = !enabled;
    }

    function updateBetDisplays() {
        if (elements.selectedBetLabel) {
            elements.selectedBetLabel.textContent = state.betAmount
                ? `Mise actuelle: ${state.betAmount}`
                : 'Aucune mise';
        }
        if (elements.betAmountLabel) {
            elements.betAmountLabel.textContent = state.betAmount
                ? `Mise actuelle: ${state.betAmount}`
                : 'Mise actuelle: 0';
        }
    }

    function buildDeck() {
        const values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
        const suits = ['C', 'D', 'H', 'S'];
        state.deck = [];
        for (let r = 0; r < 2; r += 1) {
            suits.forEach((suit) => values.forEach((value) => state.deck.push(`${value}-${suit}`)));
        }
    }

    function drawCard() {
        if (!state.deck.length) {
            buildDeck();
            shuffleDeck();
        }
        return state.deck.pop();
    }

    function shuffleDeck() {
        for (let i = state.deck.length - 1; i > 0; i -= 1) {
            const j = Math.floor(Math.random() * (i + 1));
            [state.deck[i], state.deck[j]] = [state.deck[j], state.deck[i]];
        }
    }

    function calculateTotal(hand) {
        let total = 0;
        let aces = 0;
        hand.forEach((card) => {
            const value = card.split('-')[0];
            if (value === 'A') {
                total += 11;
                aces += 1;
            } else if (['K', 'Q', 'J'].includes(value)) {
                total += 10;
            } else {
                total += parseInt(value, 10);
            }
        });
        while (total > 21 && aces > 0) {
            total -= 10;
            aces -= 1;
        }
        return total;
    }

    function parseBalanceFromLabel() {
        if (!elements.balanceLabel) {
            return 0;
        }
        const raw = elements.balanceLabel.textContent || '';
        const normalized = raw.replace(/[^0-9-]/g, '');
        return parseInt(normalized, 10) || 0;
    }

    function deriveInitialBalance() {
        if (session && session.balance !== undefined && session.balance !== null) {
            const numericBalance = Number(session.balance);
            if (Number.isFinite(numericBalance)) {
                return numericBalance;
            }
        }
        return parseBalanceFromLabel();
    }

    function getCurrentBalance() {
        return state.balance;
    }

    function updateBalanceDisplay(amount) {
        const numericAmount = Number(amount);
        const safeAmount = Number.isFinite(numericAmount) ? numericAmount : 0;
        state.balance = safeAmount;
        if (elements.balanceLabel) {
            elements.balanceLabel.textContent = safeAmount.toLocaleString('fr-FR');
        }
    }

    function payWin(multiplier) {
        const balance = getCurrentBalance();
        const win = Math.round(state.betAmount * (1 + multiplier));
        const newBalance = balance + win;
        updateBalanceDisplay(newBalance);
        persistBalance(newBalance);
    }

    function refundBet() {
        const balance = getCurrentBalance();
        const newBalance = balance + state.betAmount;
        updateBalanceDisplay(newBalance);
        persistBalance(newBalance);
    }

    function persistBalance(amount) {
        if (!session || !csrfToken) {
            return;
        }
        fetch(session.endpoints.saveBalance, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                userId: session.userId,
                balance: amount,
                game: 'blackjack',
            }),
        }).catch((error) => console.warn('blackjack_alt.js: balance persist failed', error));
    }

    function resetView() {
        if (elements.betPanel) elements.betPanel.style.display = 'block';
        if (elements.gameMat) elements.gameMat.style.display = 'none';
        state.betAmount = 0;
        if (Array.isArray(elements.betChips)) {
            elements.betChips.forEach((btn) => btn.classList.remove('active'));
        }
        updateBetDisplays();
        toggleActions(false);
    }
})();
