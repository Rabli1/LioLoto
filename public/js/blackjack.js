(function () {
    const CARD_PATH = '/img/cards/';

    let dealerSum = 0;
    let playerSum = 0;
    let hiddenCard;
    let deck = [];
    let canHit = true;
    document.getElementById('split').disabled = true;
    let hasSplit = false;
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
            dealerHand.forEach((card, index) => {
                let img;
                if (dealerContainer.children[index]) {
                    img = dealerContainer.children[index];
                } else {
                    img = document.createElement('img');
                    img.classList.add('dealerCard');
                    img.style.animationDelay = `${0.25}s`;
                    dealerContainer.appendChild(img);
                }
                if (!showHidden && index === 1) {
                    img.src = `${CARD_PATH}BACK.png`;
                    img.alt = 'Carte cachée';
                } else {
                    img.classList.add('dealerCard');
                    img.style.animationDelay = `${0.25}s`;
                    img.src = `${CARD_PATH}${card}.png`;
                    img.alt = card;
                }
            });
        }

        if (playerContainer) {
            playerHand.forEach((card, index) => {
                if (!playerContainer.children[index]) {
                    const img = document.createElement('img');
                    img.src = `${CARD_PATH}${card}.png`;
                    img.alt = card;
                    img.classList.add('playerCard');
                    img.style.animationDelay = `${0.25}s`;
                    playerContainer.appendChild(img);
                }
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
        document.getElementById('double').disabled = false;

        playerHand.push(deck.pop());
        dealerHand.push(deck.pop());
        playerHand.push(deck.pop());
        hiddenCard = deck.pop();
        dealerHand.push(hiddenCard);

        playerSum = SommeMain(playerHand);
        dealerSum = SommeMain(dealerHand);

        afficheMains();
        updateScore();

        if(getCardValue(playerHand[0]) === getCardValue(playerHand[1])) {
            document.getElementById('split').disabled = false;
        }

        const betAmountLabel = document.getElementById('betAmount');
        if (betAmountLabel) {
            betAmountLabel.innerHTML = `<h2>Votre mise : ${betAmount}</h2>`;
        }

        if (dealerSum === 21 || playerSum === 21) {
            revealDealerHand();
            setTimeout(() => {
                if (dealerSum === 21 && playerSum === 21) {
                    settleAndRestart('push');
                } else if (dealerSum === 21) {
                    settleAndRestart('dealer-blackjack');
                } else {
                    settleAndRestart('blackjack');
                }
                
            }, 500);
        }
    }

    function revealDealerHand() {
        setTimeout(() => {
            afficheMains(true);
        }, 250);
        setTimeout(() => {
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
        const sumContainer = document.getElementById("sumContainer");

        if (sumContainer) {sumContainer.className = 'mt-3'; sumContainer.innerHTML = `Total : <span id="playerSum">${playerSum}</span>`;}
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
        setTimeout(() => {
            restartGame();
        }, 4000);
    }

    function dealerDraw() {
        if (dealerSum < 17) {
            const card = deck.pop();
            dealerHand.push(card);
            dealerSum = SommeMain(dealerHand);
            setTimeout(() => {
                afficheMains(true);
                updateScore(true);
            }, 500);
            setTimeout(dealerDraw, 700);
        } else {
            setTimeout(() => {
                if (dealerSum > 21) {
                    revealDealerHand();
                    settleAndRestart(`win`);
                } else if (dealerSum > playerSum) {
                    revealDealerHand();
                    settleAndRestart('lose');
                } else if (dealerSum < playerSum) {
                    revealDealerHand();
                    settleAndRestart('win');
                } else {
                    revealDealerHand();
                    settleAndRestart('push');
                }
            }, 700);
        }
    }

    function hit() {
        if (!canHit) return;

        playerHand.push(deck.pop());
        playerSum = SommeMain(playerHand);
        document.getElementById('double').disabled = true;

        afficheMains();
        updateScore();

        if (playerSum > 21) {
            canHit = false;
            revealDealerHand();
            setTimeout(() => {
                settleAndRestart('lose');
            }, 500);
        }
    }

    function stay() {
        canHit = false;
        revealDealerHand();

        dealerDraw();
    }

    function split() {
        
    }

    function double() {
        if (!canHit) return;

        playerHand.push(deck.pop());
        playerSum = SommeMain(playerHand);

        afficheMains();
        updateScore();
        canHit = false;
        revealDealerHand();

        if (playerSum > 21) {
            setTimeout(() => {
                settleAndRestart('lose');
            }, 500);
        } else {
            dealerDraw();
        }
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
        const splitButton = document.getElementById('split');
        const doubleButton = document.getElementById('double');
        const clearBet = document.getElementById('clearBet');
        const gameMat = document.getElementById('gameMat');
        const betContainer = document.getElementById('betContainer');
        const selectedBetLabel = document.getElementById('selectedBet');
        const placeBetButton = document.getElementById('placeBet');

        if (!hitButton || !stayButton || !gameMat || !betContainer || !selectedBetLabel || !placeBetButton || !splitButton || !doubleButton) {
            console.warn('Blackjack UI is incomplete.');
            return;
        }

        hitButton.addEventListener('click', hit);
        stayButton.addEventListener('click', stay);
        splitButton.addEventListener('click', split);
        doubleButton.addEventListener('click', double);
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

