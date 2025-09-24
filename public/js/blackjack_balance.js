(function () {
    function settleRound(outcome) {
        if (!window.Balance) return;
        const stake = Number(window.currentBlackjackBet || 0) || 0;
        if (!stake) return;

        let payout = 0;

        switch (outcome) {
            case 'blackjack':
                payout = Math.floor(stake * 5 / 2);
                alert(`Blackjack ! Mise ${stake}, gain ${payout - stake}.`);
                break;
            case 'win':
                payout = stake * 2;
                alert(`Vous gagnez ! Mise ${stake}, gain ${payout - stake}.`);
                break;
            case 'push':
                payout = stake;
                alert(`Égalité. Mise ${stake} rendue.`);
                break;
            case 'dealer-blackjack':
                payout = 0;
                alert(`Le croupier a un blackjack ! Mise ${stake} perdue.`);
                break;
            default:
                alert(`Vous perdez. Mise ${stake} perdue.`);
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
