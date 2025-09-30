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
                resultMessage.textContent +=` - Blackjack ! Mise ${stake}, gain ${payout - stake}.`;
                break;
            case 'win':
                payout = stake * 2;
                for (let card of images) {
                    card.classList.add("win");
                }
                resultMessage.classList.add("win");
                resultMessage.textContent +=` - Vous gagner. Mise ${stake} gagné.`;
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
                resultMessage.textContent +=` - Le croupier a un blackjack ! Mise ${stake} perdue.`;
                break;
            default:
                for (let card of images) {
                    card.classList.add("lose");
                }
                resultMessage.classList.add("lose");
                resultMessage.textContent +=` - Vous perdez. Mise ${stake} perdue.`;
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

        document.addEventListener('blackjack:bet-reset', () => {
            window.currentBlackjackBet = 0;
        });
    });
})();
