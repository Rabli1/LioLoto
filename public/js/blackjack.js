(function () {
    const CARD_PATH = '/img/cards/';

    let dealerSum = 0;
    let playerSum = 0;
    let hiddenCard;
    let deck = [];
    let canHit = true;
    let betAmount = 0;

    let playerHand = [];
    let dealerHand = [];

    function dispatchGameEvent(name, detail = {}) {
        document.dispatchEvent(new CustomEvent(name, { detail }));
    }

    function buildDeck() {
        const values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
        const suits = ['C', 'D', 'H', 'S'];
        deck = [];

        suits.forEach((suit) => {
            values.forEach((value) => {
                deck.push(`${value}-${suit}`);
            });
        });

        suits.forEach((suit) => {
            values.forEach((value) => {
                deck.push(`${value}-${suit}`);
            });
        });
    }

    function shuffleDeck() {
        for (let i = deck.length - 1; i > 0; i -= 1) {
            const j = Math.floor(Math.random() * (i + 1));
            [deck[i], deck[j]] = [deck[j], deck[i]];
        }
    }

    function getCardValue(card) {
        const value = card.split('-')[0];
        if (value === 'A') return 11;
        if (['K', 'Q', 'J'].includes(value)) return 10;
        return parseInt(value, 10);
    }

    function SommeMain(hand) {
        let sum = 0;
        let aceCount = 0;
        hand.forEach(card => {
            const value = card.split('-')[0];
            if (value === 'A') {
                sum += 11;
                aceCount += 1;
            } else if (['K', 'Q', 'J'].includes(value)) {
                sum += 10;
            } else {
                sum += parseInt(value, 10);
            }
        });
        while (sum > 21 && aceCount > 0) {
            sum -= 10;
            aceCount -= 1;
        }
        return sum;
    }

    function afficheMains(showHidden = false) {
        const dealerContainer = document.getElementById('dealerContainer');
        const playerContainer = document.getElementById('playerContainer');

        if (dealerContainer) {
            dealerContainer.innerHTML = '';
            dealerHand.forEach((card, index) => {
                const img = document.createElement('img');
                if (!showHidden && index === 1) {
                    img.src = `${CARD_PATH}BACK.png`;
                    img.alt = 'Carte cachée';
                } else {
                    img.src = `${CARD_PATH}${card}.png`;
                    img.alt = card;
                }
                dealerContainer.appendChild(img);
            });
        }

        if (playerContainer) {
            playerContainer.innerHTML = '';
            playerHand.forEach((card) => {
                const img = document.createElement('img');
                img.src = `${CARD_PATH}${card}.png`;
                img.alt = card;
                playerContainer.appendChild(img);
            });
        }
    }

    function updateScore(showDealerTotal = false) {
        const dealerSumLabel = document.getElementById('dealerSum');
        const playerSumLabel = document.getElementById('playerSum');

        if (dealerSumLabel) {
            dealerSumLabel.textContent = showDealerTotal ? dealerSum : getCardValue(dealerHand[0]);
        }
        if (playerSumLabel) {
            playerSumLabel.textContent = playerSum;
        }
    }

    function startGame() {
        playerHand = [];
        dealerHand = [];
        canHit = true;

        afficheMains();
        updateScore();

        function dealCard(toHand, show = true, delay = 0) {
            setTimeout(() => {
                const card = deck.pop();
                toHand.push(card);
                afficheMains(show);
                updateScore();
            }, delay);
        }
        dealCard(playerHand, true, 500);
        dealCard(dealerHand, false, 1000);
        dealCard(playerHand, true, 1500);
        dealCard(dealerHand, true, 2000);

        setTimeout(() => {
            playerSum = SommeMain(playerHand);
            dealerSum = SommeMain(dealerHand);

            const betAmountLabel = document.getElementById('betAmount');
            if (betAmountLabel) {
                betAmountLabel.innerHTML = `<h2>Votre mise : ${betAmount}</h2>`;
            }

            // Vérifier blackjack immédiat
            if (dealerSum === 21 || playerSum === 21) {
                revealDealerHand();
                setTimeout(() => {
                    if (dealerSum === 21 && playerSum === 21) {
                        dispatchGameEvent('blackjack:result', { outcome: 'push' });
                    } else if (dealerSum === 21) {
                        dispatchGameEvent('blackjack:result', { outcome: 'dealer-blackjack' });
                    } else {
                        dispatchGameEvent('blackjack:result', { outcome: 'blackjack' });
                    }
                    restartGame();
                }, 500);
            }
        }, 2200);
    }

    function revealDealerHand() {
        setTimeout(() => {
            afficheMains(true);
            updateScore(true);
        }, 500);
    }

    function restartGame() {
        dealerSum = 0;
        playerSum = 0;
        playerHand = [];
        dealerHand = [];
        hiddenCard = null;
        canHit = true;
        betAmount = null;
        window.pendingBlackjackBet = 0;
        window.currentBlackjackBet = 0;

        const betContainer = document.getElementById('betContainer');
        const gameMat = document.getElementById('gameMat');
        const selectedBetLabel = document.getElementById('selectedBet');
        const betAmountLabel = document.getElementById('betAmount');
        const playerSumLabel = document.getElementById('playerSum');

        if (betContainer) betContainer.style.display = 'block';
        if (gameMat) gameMat.style.display = 'none';
        if (selectedBetLabel) selectedBetLabel.textContent = '';
        if (betAmountLabel) betAmountLabel.innerHTML = '';
        if (playerSumLabel) playerSumLabel.textContent = '';

        const dealerContainer = document.getElementById('dealerContainer');
        const playerContainer = document.getElementById('playerContainer');
        if (dealerContainer) dealerContainer.innerHTML = '';
        if (playerContainer) playerContainer.innerHTML = '';

        dispatchGameEvent('blackjack:bet-reset');

        buildDeck();
        shuffleDeck();
    }

    function settleAndRestart(outcome) {
        dispatchGameEvent('blackjack:result', { outcome });
        restartGame();
    }

    function hit() {
        if (!canHit) return;

        playerHand.push(deck.pop());
        playerSum = SommeMain(playerHand);

        afficheMains();
        updateScore();

        if (playerSum > 21) {
            canHit = false;
            setTimeout(() => {
                settleAndRestart('lose');
            }, 500);
        }
    }

    function stay() {
        canHit = false;
        revealDealerHand();

        function dealerDraw() {
            if (dealerSum < 17) {
                setTimeout(() => {
                    const card = deck.pop();
                    dealerHand.push(card);
                    dealerSum = SommeMain(dealerHand);
                    afficheMains(true);
                    updateScore(true);
                }, 700);
            } else {
                setTimeout(() => {
                    if (dealerSum > 21) {
                        settleAndRestart(`win`);
                    } else if (dealerSum > playerSum) {
                        settleAndRestart('lose');
                    } else if (dealerSum < playerSum) {
                        settleAndRestart('win');
                    } else {
                        settleAndRestart('push');
                    }
                }, 700);
            }
        }
        dealerDraw();
    }

    document.addEventListener('DOMContentLoaded', () => {
        buildDeck();
        shuffleDeck();
        initBlackjack();
        window.blackjackHit = hit;
        window.blackjackStay = stay;
        window.restartGame = restartGame;
    });

    function initBlackjack() {
        const hitButton = document.getElementById('hitButton');
        const stayButton = document.getElementById('stayButton');
        const gameMat = document.getElementById('gameMat');
        const betContainer = document.getElementById('betContainer');
        const selectedBetLabel = document.getElementById('selectedBet');
        const placeBetButton = document.getElementById('placeBet');

        if (!hitButton || !stayButton || !gameMat || !betContainer || !selectedBetLabel || !placeBetButton) {
            console.warn('Blackjack UI is incomplete.');
            return;
        }

        hitButton.addEventListener('click', hit);
        stayButton.addEventListener('click', stay);
        gameMat.style.display = 'none';

        document.querySelectorAll('.betToken').forEach((btn) => {
            btn.addEventListener('click', function onBetTokenClick() {
                betAmount += parseInt(this.getAttribute('data-value'), 10);
                window.pendingBlackjackBet = betAmount;
                if (betAmount > 0) {
                    selectedBetLabel.textContent = `Mise sélectionnée : ${betAmount}`;
                } else {
                    selectedBetLabel.textContent = 'Aucune mise sélectionnée';
                }
            });
        });
        clearBet.addEventListener('click', function onClearBet() {
            betAmount = 0;
            window.pendingBlackjackBet = 0;
            selectedBetLabel.textContent = 'Aucune mise sélectionnée';
        });

        placeBetButton.addEventListener('click', function onPlaceBet() {
            if (!betAmount) {
                alert('Veuillez sélectionner un jeton de mise !');
                return;
            }

            if (window.Balance && !window.Balance.canMise(betAmount)) {
                alert('Solde insuffisant.');
                return;
            }

            if (window.Balance && !window.Balance.miser(betAmount)) {
                alert('Solde insuffisant.');
                return;
            }

            window.currentBlackjackBet = betAmount;
            betContainer.style.display = 'none';
            gameMat.style.display = 'block';
            startGame();
        });
    }
})();

