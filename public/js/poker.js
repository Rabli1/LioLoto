const balanceUI = $("#balanceUI");
const playerSeats = $(".playerSeat");
const pot = $("#pot");
const joinButton = $("#join-button");
const requiredCall = $("#required-call");
const communityCards = $('#community-cards');
const gameMessage = $("#game-message");
const pokerError = $("#poker-error");
const betAmount = $("#betAmount");
const betRange = $("#betRange");
const betSection = $("#bet-section");
const foldButton = $("#fold-button");
const checkButton = $("#check-button");
const callButton = $("#call-button");
const raiseButton = $("#raise-button");


const intervalTime = 2 * 1000;
const turnTime = 15 * 1000;
let turnStart = Date.now();
let gameState = {};
let currentEtag = "";
const csrfToken = window.gameSession.csrfToken;
let deck = [];
const roundSteps = ['pre-flop', 'flop', 'turn', 'river', 'showdown'];
let currentRound = 0;
let gameTerminated = false;


joinButton.on('click', () => {
    joinGame();
    joinButton.hide();
    gameMessage.text("Vous avez rejoint la partie. Vous êtes en file d'attente...");
});

foldButton.on('click', () => {
    placeBet(-1);
});

checkButton.on('click', () => {
    placeBet(0);
});

callButton.on('click', () => {
    const amountToCall = gameState.requiredBet - gameState.players.find(p => p.id === window.gameSession.userId).currentBet;
    placeBet(amountToCall);
});
raiseButton.on('click', () => {
    placeBet(parseInt(betAmount.val()));
});


function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function startRefreshInterval() {
    setInterval(async () => {
        const newEtag = await getEtag();
        if (newEtag !== currentEtag) {
            currentEtag = newEtag;
            getGameState();
        }
        if (turnStart + turnTime < Date.now()) {
            gameState.players[gameState.playersTurn].toKick = true;
            gameState.players[gameState.playersTurn].hasFolded = true;
            gameState.playersTurn = (gameState.playersTurn + 1) % gameState.players.length;
        }
    }, intervalTime);
}

function getGameState() {
    $.ajax({
        url: '/game/getPokerState',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function (response) {
            gameState = response.gameState;
            updateUI();
        },
        error: function (xhr, status, error) {
            console.error('Error getting game state:', error);
        }
    });
}

async function getEtag() {
    const res = await fetch('/etag.php');
    const data = await res.json();
    return data.Etag;
}

function updateEtag() {
    $.ajax({
        url: '/game/updateEtag',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function () {

        },
        error: function (xhr, status, error) {
            console.error('Error updating Etag', error);
        }
    });
}

function updateUI() {
    if (!gameState) return;
    let playersList = gameState.players;
    const potValue = Number(gameState.pot) || 0;
    pot.text(`Pot : ${potValue.toLocaleString('en-US').replace(/,/g, ' ')}`);
    if (gameState.players.length < 2) {
        gameMessage.text("En attente de joueurs pour démarrer la partie...");
    }
    let maxBet = Infinity;
    playerSeats.each(function (index) {
        const seat = $(this);
        const playerData = playersList[index];

        if (playerData) {
            const color = playerData.profileColor || 'black';
            const name = playerData.name || 'Joueur';
            const balance = Number(playerData.balance) || 0;

            const playerIcon = seat.find('.player-icon');
            const playerNameDiv = seat.find('.player-name');
            const playerBalanceDiv = seat.find('.player-balance');
            const playerCards = seat.find('.player-cards');
            if (playerData.id === window.gameSession.userId) {
                joinButton.hide();

            }
            if (playerIcon.length)
                playerIcon.attr('class', `fa-solid ${playerData.profileImage || 'fa-user'} pfp-${color}`);
            if (playerNameDiv.length)
                playerNameDiv.text(name);
            if (playerBalanceDiv.length)
                playerBalanceDiv.text(`Solde : ${balance.toLocaleString('en-US').replace(/,/g, ' ')}`);
            playerCards.empty();
            if (gameState.roundStep === 'showdown' || window.gameSession.userId === playerData.id) {
                playerCards.append(
                    (playerData.cards).map(card =>
                        `<img src="/img/cards/${card}.png" class="img-fluid" alt="Card">`
                    )
                );
            }
            else {
                playerCards.append(
                    `<img src="/img/cards/BACK.png" class="img-fluid" alt="Card"> <img src="/img/cards/BACK.png" class="img-fluid" alt="Card">`
                );
            }
            seat.toggleClass('activeTurn', index === Number(gameState.playersTurn));
            seat.toggleClass('dimmed', playerData.hasFolded);
            seat.removeClass('emptySeat');
            if(playerData.hasWon){
                gameTerminated = true;
            }
            seat.toggleClass('winner', playerData.hasWon);
            if (playerData.balance < maxBet) {
                maxBet = playerData.balance;
            }


        } else {
            seat.removeClass('dimmed activeTurn').addClass('emptySeat');
        }
    });
    if (gameState.players[gameState.playersTurn].id === window.gameSession.userId && gameState.roundStep !== 'showdown' && gameState.players.length >= 2 && !gameTerminated) {
        if (gameState.requiredBet === 0) {
            checkButton.toggleClass('disabled', false);
            callButton.toggleClass('disabled', true);
        }
        else {
            checkButton.toggleClass('disabled', true);
            callButton.toggleClass('disabled', false);
        }
        betSection.show();
        callAmount = gameState.requiredBet - gameState.players[gameState.playersTurn].currentBet
        requiredCall.text(`(${callAmount} pour call)`);
        betRange.attr('max', maxBet);
        betAmount.attr('max', maxBet);
        betRange.attr('min', callAmount);
        betAmount.attr('min', callAmount);
        betRange.val(callAmount);
        betAmount.val(callAmount);
    }
    else {
        betSection.hide();
    }
    communityCards.empty();
    communityCards.append(
        (gameState.communityCards).map(card =>
            `<img src="/img/cards/${card}.png" class="img-fluid" alt="Card">`
        )
    );
}

function joinGame() {
    $.ajax({
        url: '/game/joinPoker',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function () {
        },
        error: function (xhr, status, error) {
            console.error('Error joining poker game:', error);
        }
    });
}

function quitGame() {
    $.ajax({
        url: '/game/quitPoker',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: { playerId: window.gameSession.userId },
        success: function (response) {
            gameMessage.text("Vous allez quitter la table à la fin de cette manche");
        },
        error: function (xhr, status, error) {
            console.error('Error quitting game:', error);
        }
    });
}

function initRound() {
    gameTerminated = false;
    $.ajax({
        url: '/game/initRound',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function (response) {
        },
        error: function (xhr, status, error) {
            console.error('Error initializing round:', error);
        }
    });
}

function placeBet(bet) {
    pokerError.text("");
    $.ajax({
        url: '/game/placeBet',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: {
            amount: bet,
            playerId: window.gameSession.userId
        },
        success: function (response) {
            balanceUI.text(`Solde : ${response.newBalance.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).replace(/,/g, ' ')}`);
            console.log("new balance: " + response.newBalance);
        },
        error: function (xhr, status, error) {
            console.error('Error placing bet:', error);
            pokerError.text("Error placing bet");
        }
    });
}

startRefreshInterval();
initRound();