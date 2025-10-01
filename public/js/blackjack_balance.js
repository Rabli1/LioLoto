(function () {
    function settleRound(outcome) {
        if (!window.Balance) return;
        const stake = Number(window.currentBlackjackBet || 0) || 0;
        if (!stake) return;

        let payout = 0;
        let images = document.getElementsByClassName("playerCard");
        let resultMessage = document.getElementById("sumContainer");

        switch (outcome) {
            case 'blackjack':
                payout = Math.floor(stake * 5 / 2);
                for (let card of images) {
                    card.classList.add("blackjack");
                }
                resultMessage.classList.add("blackjack");
                resultMessage.textContent += ` - Blackjack ! Mise ${stake}, gain ${payout - stake}.`;
                break;
            case 'win':
                payout = stake * 2;
                for (let card of images) {
                    card.classList.add("win");
                }
                resultMessage.classList.add("win");
                resultMessage.textContent += ` - Vous gagner. Mise ${stake} gagné.`;
                break;
            case 'push':
                payout = stake;
                for (let card of images) {
                    card.classList.add("draw");
                }
                resultMessage.classList.add("draw");
                resultMessage.textContent += ` - Égalité. Mise ${stake} rendue.`;
                break;
            case 'dealer-blackjack':
                payout = 0;
                for (let card of images) {
                    card.classList.add("lose");
                }
                resultMessage.classList.add("lose");
                resultMessage.textContent += ` - Le croupier a un blackjack ! Mise ${stake} perdue.`;
                break;
            default:
                for (let card of images) {
                    card.classList.add("lose");
                }
                resultMessage.classList.add("lose");
                resultMessage.textContent += ` - Vous perdez. Mise ${stake} perdue.`;
        }

        if (payout > 0) {
            window.Balance.gain(payout);
        }
        window.currentBlackjackBet = 0;

    }

    document.addEventListener('DOMContentLoaded', () => {
        const session = window.blackjackSession || {};
        window.currentBlackjackBet = Number(window.currentBlackjackBet || 0) || 0;
        window.pendingBlackjackBet = Number(window.pendingBlackjackBet || 0) || 0;

        if (window.Balance) {
            window.Balance.init({
                session,
                displaySelectors: ['#blackjack-balance'],
            });
        }

        document.addEventListener('blackjack:result', (event) => {
            settleRound(event.detail?.outcome);
        });

        document.addEventListener('blackjack:resultSplit', (event) => {
            settleRoundSplit(event.detail?.outcome1, event.detail?.outcome2);
        });

        document.addEventListener('blackjack:bet-reset', () => {
            window.currentBlackjackBet = 0;
        });
    });

    function settleRoundSplit(outcome1, outcome2) {
        if (!window.Balance) return;
        const stake = Number(window.currentBlackjackBet || 0) || 0;
        if (!stake) return;

        const stakePerHand = stake/2;

        let payout1 = 0;
        let images1 = document.querySelectorAll("#playerContainer .playerCard");
        let resultMessage1 = document.getElementById("sumContainer");
        switch (outcome1) {
            case 'blackjack':
                payout1 = Math.floor(stakePerHand * 5 / 2);
                images1.forEach(card => card.classList.add("blackjack"));
                resultMessage1.classList.add("blackjack");
                resultMessage1.textContent += ` - Main 1 : Blackjack ! Mise ${stakePerHand}, gain ${payout1 - stakePerHand}.`;
                break;
            case 'win':
                payout1 = stakePerHand * 2;
                images1.forEach(card => card.classList.add("win"));
                resultMessage1.classList.add("win");
                resultMessage1.textContent += ` - Main 1 : Vous gagnez. Mise ${stakePerHand} gagnée.`;
                break;
            case 'push':
                payout1 = stakePerHand;
                images1.forEach(card => card.classList.add("draw"));
                resultMessage1.classList.add("draw");
                resultMessage1.textContent += ` - Main 1 : Égalité. Mise ${stakePerHand} rendue.`;
                break;
            case 'dealer-blackjack':
                payout1 = 0;
                images1.forEach(card => card.classList.add("lose"));
                resultMessage1.classList.add("lose");
                resultMessage1.textContent += ` - Main 1 : Le croupier a un blackjack ! Mise ${stakePerHand} perdue.`;
                break;
            default:
                images1.forEach(card => card.classList.add("lose"));
                resultMessage1.classList.add("lose");
                resultMessage1.textContent += ` - Main 1 : Vous perdez. Mise ${stakePerHand} perdue.`;
        }

        let payout2 = 0;
        let images2 = document.querySelectorAll("#playerContainerSplit .playerCard");
        let resultMessage2 = document.getElementById("sumContainerSplit");
        switch (outcome2) {
            case 'blackjack':
                payout2 = Math.floor(stakePerHand * 5 / 2);
                images2.forEach(card => card.classList.add("blackjack"));
                resultMessage2.classList.add("blackjack");
                resultMessage2.textContent += ` - Main 2 : Blackjack ! Mise ${stakePerHand}, gain ${payout2 - stakePerHand}.`;
                break;
            case 'win':
                payout2 = stakePerHand * 2;
                images2.forEach(card => card.classList.add("win"));
                resultMessage2.classList.add("win");
                resultMessage2.textContent += ` - Main 2 : Vous gagnez. Mise ${stakePerHand} gagnée.`;
                break;
            case 'push':
                payout2 = stakePerHand;
                images2.forEach(card => card.classList.add("draw"));
                resultMessage2.classList.add("draw");
                resultMessage2.textContent += ` - Main 2 : Égalité. Mise ${stakePerHand} rendue.`;
                break;
            case 'dealer-blackjack':
                payout2 = 0;
                images2.forEach(card => card.classList.add("lose"));
                resultMessage2.classList.add("lose");
                resultMessage2.textContent += ` - Main 2 : Le croupier a un blackjack ! Mise ${stakePerHand} perdue.`;
                break;
            default:
                images2.forEach(card => card.classList.add("lose"));
                resultMessage2.classList.add("lose");
                resultMessage2.textContent += ` - Main 2 : Vous perdez. Mise ${stakePerHand} perdue.`;
        }

        if (payout1 > 0) window.Balance.gain(payout1);
        if (payout2 > 0) window.Balance.gain(payout2);

        window.currentBlackjackBet = 0;
    }
})();
