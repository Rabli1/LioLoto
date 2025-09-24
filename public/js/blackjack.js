(function () {
    const CARD_PATH = '/img/cards/';

    let dealerSum = 0;
    let playerSum = 0;
    let hiddenCard;
    let deck = [];
    let canHit = true;
    let betAmount = null;

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

    function ajusteAces(hand, sum) {
        let adjustedSum = sum;
        let aceCount = hand.filter((card) => card.startsWith('A')).length;
        while (adjustedSum > 21 && aceCount > 0) {
            adjustedSum -= 10;
            aceCount -= 1;
        }
        return adjustedSum;
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

        playerHand.push(deck.pop());
        dealerHand.push(deck.pop());
        playerHand.push(deck.pop());
        hiddenCard = deck.pop();
        dealerHand.push(hiddenCard);

        playerSum = ajusteAces(playerHand, getCardValue(playerHand[0]) + getCardValue(playerHand[1]));
        dealerSum = ajusteAces(dealerHand, getCardValue(dealerHand[0]) + getCardValue(hiddenCard));

        afficheMains();
        updateScore();

        const betAmountLabel = document.getElementById('betAmount');
        if (betAmountLabel) {
            betAmountLabel.innerHTML = `<h2>Votre mise : ${betAmount}</h2>`;
        }

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
    }

    function revealDealerHand() {
        afficheMains(true);
        updateScore(true);
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
        playerSum += getCardValue(playerHand[playerHand.length - 1]);
        playerSum = ajusteAces(playerHand, playerSum);

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
                const card = deck.pop();
                dealerHand.push(card);
                dealerSum += getCardValue(card);
                dealerSum = ajusteAces(dealerHand, dealerSum);
                afficheMains(true);
                updateScore(true);
                setTimeout(dealerDraw, 700);
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
                if (betAmount > 0){
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

