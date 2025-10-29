const playerSeats = $(".playerSeat");
const pot = $("#pot");
const joinButton = $("#join-button");
const requiredCall = $("#required-call");
const centerCards = $('#poker-center img');
const gameMessage = $("#game-message");
const pokerError = $("#poker-error");
const betAmount = $("#betAmount");
const betRange = $("#betRange");
const betSection = $("#bet-section");

const intervalTime = 10 * 1000;
const turnTime = 15 * 1000;
let turnStart = Date.now();
let gameState = {};
let currentEtag = "";
const csrfToken = window.gameSession.csrfToken;
let deck = [];
const roundSteps = ['pre-flop', 'flop', 'turn', 'river', 'showdown'];
let currentRound = 0;


joinButton.on('click', () => {
    joinGame();
    joinButton.hide();
    gameMessage.text("Vous avez rejoint la partie. Vous êtes en file d'attente...");
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
    if(turnStart + turnTime < Date.now()){
        gameState.players[gameState.playersTurn].toKick = true;
        gameState.players[gameState.playersTurn].hasFolded = true;
        gameState.playersTurn = (gameState.playersTurn + 1) % gameState.players.length;
    }
    let playersList = gameState.players;
    const potValue = Number(gameState.pot) || 0;
    pot.text(`Pot : ${potValue.toLocaleString('en-US').replace(/,/g, ' ')}`);
    let maxBet = Infinity;
    let toNextRound = true;
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

            seat.toggleClass('dimmed', !!playerData.hasFolded);
            seat.removeClass('emptySeat');
            if(playerData.balance < maxBet){
                maxBet = playerData.balance;
            }
            if(index === Number(gameState.playersTurn)){
                betSection.show();
            }
            else{
                betSection.hide();
            }

            if(toNextRound){
                if(!playerData.hasPlayed || playerData.currentBet !== gameState.requiredBet || !playerData.hasFolded){
                    toNextRound = false;
                }
            }
        } else {
            seat.removeClass('dimmed activeTurn').addClass('emptySeat');
        }
    });
    requiredCall.text(`(${gameState.requiredBet} pour call)`);
    betRange.attr('max', maxBet);
    betAmount.attr('max', maxBet);
    betRange.attr('min', gameState.requiredBet);
    betAmount.attr('min', gameState.requiredBet);
    (gameState.communityCards || []).forEach((card, idx) => {
        if (idx < centerCards.length)
            centerCards.eq(idx).attr('src', `/img/cards/${card}.png`);
    });
    if(toNextRound){
        nextRound();
    }
}

function updateBalance(amount) {
    let player = gameState.players.find(p => p.id === window.gameSession.userId);
    const newBalance = player.balance + amount;
    $.ajax({
        url: '/game/balance',
        method: 'POST',
        data: { balance: parseInt(newBalance) },
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function (response) {
            player.balance = response.balance;
            updateUI();
            updateEtag();
        },
        error: function (xhr, status, error) {
            console.error('Error saving balance:', error);
        }
    });
}

function joinGame() {
    $.ajax({
        url: '/game/joinPoker',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function () {
            updateEtag();
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
        success: function(response) {
            gameMessage.text("Vous allez quitter la table à la fin de cette manche");
            updateEtag();
        },
        error: function(xhr, status, error) {
            console.error('Error quitting game:', error);
        }
    });
}

function initRound() {
    $.ajax({
        url: '/game/initRound',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function(response) {
            updateEtag();
        },
        error: function(xhr, status, error) {
            console.error('Error initializing round:', error);
        }
    });
}

function placeBet(amount) {
    pokerError.text("");
    $.ajax({
        url: '/game/placeBet',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: {
            amount: amount,
            playerId: window.gameSession.userId
        },
        success: function(response) {
            updateBalance(-amount);
            updateEtag();
        },
        error: function(xhr, status, error) {
            console.error('Error placing bet:', error);
            pokerError.text("Error placing bet");
        }
    });
}

function nextRound() {
    $.ajax({
        url: '/game/nextRound',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function(response) {
            updateEtag();
        },
        error: function(xhr, status, error) {
            console.error('Error moving to next round:', error);
        }
    });
}

function addCommunityCard() {
    gameState.communityCards.push(deck.pop());
}
async function settleRound() {
    await sleep(5000);
}

startRefreshInterval();