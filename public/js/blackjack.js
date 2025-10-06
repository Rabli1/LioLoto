(function () {
    const CARD_PATH = '/img/cards/';

    let dealerSum = 0;
    let playerSum = 0;
    let playerSumSplit = 0;
    let hiddenCard;
    let deck = [];
    let canHit = true;
    let canHitSplit = true;
    let hisSplit = false;
    let firstSplit = true;
    let result1 = '';
    let result2 = '';
    document.getElementById('split').disabled = true;
    document.getElementById('playerHand2').style.display = 'none';
    let betAmount = 0;

    let playerHand = [];
    let playerHandSplit = [];
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
        const playerContainerSplit = document.getElementById('playerContainerSplit');

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

        if (!hisSplit) {
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
        else {
            if (firstSplit) {
                playerContainer.innerHTML = '';
                playerContainerSplit.innerHTML = '';
                firstSplit = false;
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

            if (playerContainerSplit) {
                playerHandSplit.forEach((card, index) => {
                    if (!playerContainerSplit.children[index]) {
                        const img = document.createElement('img');
                        img.src = `${CARD_PATH}${card}.png`;
                        img.alt = card;
                        img.classList.add('playerCard');
                        img.style.animationDelay = `${0.25}s`;
                        playerContainerSplit.appendChild(img);
                    }
                });
            }

        }
    }

    function updateScore(showDealerTotal = false) {
        const dealerSumLabel = document.getElementById('dealerSum');
        const playerSumLabel = document.getElementById('playerSum');
        const playerSumSplitLabel = document.getElementById('playerSumSplit');

        if (dealerSumLabel) {
            dealerSumLabel.textContent = showDealerTotal ? dealerSum : getCardValue(dealerHand[0]);
        }
        if (playerSumLabel) {
            playerSumLabel.textContent = playerSum;
        }
        if (playerSumSplitLabel) {
            playerSumSplitLabel.textContent = playerSumSplit;
        }
    }

    function startGame() {
        playerHand = [];
        dealerHand = [];
        canHit = true;
        if (window.Balance.get() < betAmount * 2)
            document.getElementById('double').disabled = true;
        else
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
        if ((getCardValue(playerHand[0]) === getCardValue(playerHand[1])) && (window.Balance.get() >= betAmount * 2)) {
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
        playerSumSplit = 0;
        playerHand = [];
        dealerHand = [];
        playerHandSplit = [];
        hisSplit = false;
        canHitSplit = true;
        firstSplit = true;

        hiddenCard = null;
        canHit = true;
        betAmount = null;
        window.pendingBlackjackBet = 0;
        window.currentBlackjackBet = 0;

        document.getElementById('stayButton').disabled = false;
        document.getElementById('hitButton').disabled = false;
        document.getElementById('double').disabled = false;
        document.getElementById('split').disabled = true;

        const betContainer = document.getElementById('betContainer');
        const gameMat = document.getElementById('gameMat');
        const selectedBetLabel = document.getElementById('selectedBet');
        const betAmountLabel = document.getElementById('betAmount');
        const playerSumLabel = document.getElementById('playerSum');
        const sumContainer = document.getElementById("sumContainer");
        const sumContainerSplit = document.getElementById("sumContainerSplit");
        const plaerHand2 = document.getElementById("playerHand2");


        if (sumContainer) { sumContainer.className = 'mt-3'; sumContainer.innerHTML = `Total : <span id="playerSum">${playerSum}</span>`; }
        if (sumContainerSplit) { sumContainerSplit.className = 'mt-3'; sumContainerSplit.innerHTML = `Total : <span id="playerSumSplit">${playerSumSplit}</span>`; }
        if (betContainer) betContainer.style.display = 'block';
        if (gameMat) gameMat.style.display = 'none';
        if (selectedBetLabel) selectedBetLabel.textContent = '';
        if (betAmountLabel) betAmountLabel.innerHTML = '';
        if (playerSumLabel) playerSumLabel.textContent = '';
        if (plaerHand2) plaerHand2.style.display = 'none';

        const dealerContainer = document.getElementById('dealerContainer');
        const playerContainer = document.getElementById('playerContainer');
        const playerContainerSplit = document.getElementById('playerContainerSplit');
        if (dealerContainer) dealerContainer.innerHTML = '';
        if (playerContainer) playerContainer.innerHTML = '';
        if (playerContainerSplit) playerContainerSplit.innerHTML = '';

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

    function settleAndRestartSplit(outcome1, outcome2) {
        dispatchGameEvent('blackjack:resultSplit', { outcome1, outcome2 });
        setTimeout(() => {
            restartGame();
        }, 4000);
    }

    function dealerDraw() {
        document.getElementById('playerHand1').style.opacity = '1';
        document.getElementById('playerHand2').style.opacity = '1';
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
            if (!hisSplit) {
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
            else {
                setTimeout(() => {
                    if ((dealerSum > 21) && (result1 !== 'lose' && result2 !== 'lose')) {
                        revealDealerHand();
                        settleAndRestartSplit('win', 'win');
                    } else if ((dealerSum > 21) && (result1 !== 'lose' && result2 === 'lose')) {
                        revealDealerHand();
                        settleAndRestartSplit('win', 'lose');
                    } else if ((dealerSum > 21) && (result1 === 'lose' && result2 !== 'lose')) {
                        revealDealerHand();
                        settleAndRestartSplit('lose', 'win');
                    } else if ((dealerSum > 21) && (result1 === 'lose' && result2 === 'lose')) {
                        revealDealerHand();
                        settleAndRestartSplit('lose', 'lose');
                    } else if (playerSum > 21 && playerSumSplit > 21) {
                        revealDealerHand();
                        settleAndRestartSplit('lose', 'lose');
                    } else if (playerSum > 21 && playerSumSplit <= 21) {
                        if (dealerSum < playerSumSplit) {
                            revealDealerHand();
                            settleAndRestartSplit('lose', 'win');
                        } else if (dealerSum > playerSumSplit) {
                            revealDealerHand();
                            settleAndRestartSplit('lose', 'lose');
                        } else {
                            revealDealerHand();
                            settleAndRestartSplit('lose', 'push');
                        }
                    } else if (playerSumSplit > 21 && playerSum <= 21) {
                        if (dealerSum < playerSum) {
                            revealDealerHand();
                            settleAndRestartSplit('win', 'lose');
                        } else if (dealerSum > playerSum) {
                            revealDealerHand();
                            settleAndRestartSplit('lose', 'lose');
                        } else {
                            revealDealerHand();
                            settleAndRestartSplit('push', 'lose');
                        }
                    } else if (dealerSum > playerSum && dealerSum > playerSumSplit) {
                        revealDealerHand();
                        settleAndRestartSplit('lose', 'lose');
                    } else if (dealerSum < playerSum && dealerSum < playerSumSplit) {
                        revealDealerHand();
                        settleAndRestartSplit('win', 'win');
                    } else {
                        if (playerSum > dealerSum) result1 = 'win';
                        else if (playerSum < dealerSum) result1 = 'lose';
                        else result1 = 'push';

                        if (playerSumSplit > dealerSum) result2 = 'win';
                        else if (playerSumSplit < dealerSum) result2 = 'lose';
                        else result2 = 'push';

                        revealDealerHand();
                        settleAndRestartSplit(result1, result2);
                    }
                }, 700);
            }
        }
    }

    function hit() {
        if (!canHit) return;

        if (!hisSplit) {
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
        else {
            if (canHitSplit) {
                playerHand.push(deck.pop());
                playerSum = SommeMain(playerHand);
                document.getElementById('double').disabled = true;

                afficheMains();
                updateScore();

                if (playerSum > 21) {
                    canHitSplit = false;
                    document.getElementById('playerHand1').style.opacity = '0.5';
                    document.getElementById('playerHand2').style.opacity = '1';
                    result1 = 'lose';
                }
            }
            else {
                playerHandSplit.push(deck.pop());
                playerSumSplit = SommeMain(playerHandSplit);

                afficheMains();
                updateScore();

                if (playerSumSplit > 21) {
                    canHit = false;
                    result2 = 'lose';
                    revealDealerHand();
                    dealerDraw();
                }
            }
        }
    }

    function stay() {
        if (!hisSplit) {
            canHit = false;
            document.getElementById('stayButton').disabled = true;
            document.getElementById('hitButton').disabled = true;
            document.getElementById('double').disabled = true;
            document.getElementById('split').disabled = true;
            revealDealerHand();
            dealerDraw();
        }
        else {
            if (canHitSplit) {
                canHitSplit = false;
                document.getElementById('playerHand1').style.opacity = '0.5';
                document.getElementById('playerHand2').style.opacity = '1';
            }
            else {
                canHit = false;
                document.getElementById('stayButton').disabled = true;
                document.getElementById('hitButton').disabled = true;
                document.getElementById('double').disabled = true;
                document.getElementById('split').disabled = true;
                revealDealerHand();
                dealerDraw();
            }
        }
    }

    function split() {
        hisSplit = true;
        canHitSplit = true;
        document.getElementById('split').disabled = true;
        document.getElementById('playerHand2').style.display = 'inline-block';
        document.getElementById('labelMain').textContent = 'Main 1';
        document.getElementById('playerHand1').style.opacity = '1';
        document.getElementById('playerHand2').style.opacity = '0.5';
        window.Balance.miser(betAmount)
        window.currentBlackjackBet = betAmount * 2;
        document.getElementById('betAmount').innerHTML = `<h2>Votre mise : ${betAmount * 2}</h2>`;

        if (window.Balance.get() < betAmount * 2)
            document.getElementById('double').disabled = true;
        else
            document.getElementById('double').disabled = false;
        splitHand();
    }

    function splitHand() {
        playerHandSplit.push(playerHand.pop());
        playerHand.push(deck.pop());
        playerHandSplit.push(deck.pop());
        playerSum = SommeMain(playerHand);
        playerSumSplit = SommeMain(playerHandSplit);
        afficheMains();
        updateScore();
    }

    function double() {
        if (!canHit) return;

        if (!hisSplit) {
            playerHand.push(deck.pop());
            playerSum = SommeMain(playerHand);
            window.currentBlackjackBet *= 2;
            window.Balance.miser(betAmount)
            document.getElementById('betAmount').innerHTML = `<h2>Votre mise : ${window.currentBlackjackBet}</h2>`;

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
        else {
            if (canHitSplit) {
                playerHand.push(deck.pop());
                playerSum = SommeMain(playerHand);
                window.currentBlackjackBet += betAmount;
                window.Balance.miser(betAmount)
                document.getElementById('betAmount').innerHTML = `<h2>Votre mise : ${window.currentBlackjackBet}</h2>`;

                afficheMains();
                updateScore();
                canHitSplit = false;
                document.getElementById('playerHand1').style.opacity = '0.5';
                document.getElementById('playerHand2').style.opacity = '1';
                if (playerSum > 21) {
                    result1 = 'lose';
                }
            }
            else {
                playerHandSplit.push(deck.pop());
                playerSumSplit = SommeMain(playerHandSplit);
                window.currentBlackjackBet += betAmount;
                window.Balance.miser(betAmount)
                document.getElementById('betAmount').innerHTML = `<h2>Votre mise : ${window.currentBlackjackBet}</h2>`;
                afficheMains();
                updateScore();
                canHit = false;
                revealDealerHand();
                if (playerSumSplit > 21) {
                    result2 = 'lose';
                }
                if (playerSum > 21 && playerSumSplit > 21) {
                    settleAndRestartSplit('lose', 'lose');
                } else {
                    dealerDraw();
                }
            }

        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        buildDeck();
        shuffleDeck();
        initBlackjack();
        window.blackjackHit = hit;
        window.blackjackStay = stay;
        window.restartGame = restartGame;
        window.blackjackDouble = double;
        window.blackjackSplit = split;
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

