const balanceUI = $("#balanceUI");
const playerSeats = $(".playerSeat");
const pot = $("#pot");
const joinButton = $("#join-button");
const quitButton = $("#quit-button");
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
const timeProgressBar = $(".progress-bar")


const intervalTime = 2 * 1000;
const turnTime = 15 * 1000;
const RESTART_DELAY = 10000; // 10 seconds in milliseconds
let turnStart = Date.now();
let betNotPlaced = true;
let gameState = {};
let currentEtag = "";
const csrfToken = window.gameSession.csrfToken;
let deck = [];
const roundSteps = ['pre-flop', 'flop', 'turn', 'river', 'showdown'];
let currentRound = 0;
let gameTerminated = false;
let restartTimeout = null;


joinButton.on('click', () => {
    joinGame();
    joinButton.hide();
    gameMessage.text("Vous avez rejoint la partie. Vous êtes en file d'attente...");
});

quitButton.on('click', () => {
    quitGame(window.gameSession.userId, false)
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
    gameMessage.text("");
    if (gameState.players.length < 2) {
        gameMessage.text("En attente de joueurs pour démarrer la partie...");
        return;
    }
    if (!gameTerminated && restartTimeout) {
        clearTimeout(restartTimeout);
        restartTimeout = null;
    }

    let maxBet = Infinity;
    gameTerminated = false;
    if (gameState.roundStep === 'showdown') {
        gameTerminated = true;
        
        if (gameTerminated && !restartTimeout) {
            let secondsLeft = RESTART_DELAY / 1000;
            gameMessage.text(`Nouvelle partie dans ${secondsLeft} secondes...`);
            
            const countdownInterval = setInterval(() => {
                secondsLeft--;
                gameMessage.text(`Nouvelle partie dans ${secondsLeft} secondes...`);
            }, 1000);

            restartTimeout = setTimeout(() => {
                clearInterval(countdownInterval);
                gameMessage.text("Démarrage d'une nouvelle partie...");
                initRound();
            }, RESTART_DELAY);
        }
    }

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
            const handName = seat.find('.hand-name');
            if (playerData.id === window.gameSession.userId) {
                joinButton.hide();
            }
            if (playerIcon.length)
                playerIcon.attr('class', `fa-solid ${playerData.profileImage || 'fa-user'} pfp-${color}`);
            if (playerNameDiv.length)
                playerNameDiv.text(name);
            if (playerBalanceDiv.length)
                playerBalanceDiv.text(`Solde : ${balance.toLocaleString('en-US').replace(/,/g, ' ')}`);
            handName.text("");
            playerCards.empty();
            if (gameTerminated || window.gameSession.userId === playerData.id) {
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
            if (!gameTerminated && gameState.roundStep !== 'winByFold') {
                seat.toggleClass('activeTurn', index === Number(gameState.playersTurn));
            }
            else {
                seat.toggleClass('activeTurn', false);
            }
            seat.toggleClass('dimmed', playerData.hasFolded);
            seat.removeClass('emptySeat');
            seat.toggleClass('winner', playerData.hasWon);
            if (playerData.balance < maxBet) {
                maxBet = playerData.balance;
            }
            if (gameTerminated && playerData.hasFolded === false) {
                handName.text(playerData.hand?.split(',')[0] || '');
            }

        } else {
            seat.removeClass('dimmed activeTurn').addClass('emptySeat');
        }
    });
    if (gameState.players[gameState.playersTurn].id === window.gameSession.userId && gameState.players.length >= 2 && !gameTerminated && gameState.roundStep !== 'winByFold') {
        quitButton.show();
        if (gameState.requiredBet === 0) {
            checkButton.toggleClass('disabled', false);
            callButton.toggleClass('disabled', true);
        }
        else {
            checkButton.toggleClass('disabled', true);
            callButton.toggleClass('disabled', false);
        }
        callAmount = gameState.requiredBet - gameState.players[gameState.playersTurn].currentBet
        requiredCall.text(`(${callAmount} pour call)`);
        betRange.attr('max', maxBet);
        betAmount.attr('max', maxBet);
        betRange.attr('min', callAmount);
        betAmount.attr('min', callAmount);
        betRange.val(Math.max(callAmount, 50));
        betAmount.val(Math.max(callAmount, 50));
        timeProgressBar.css("width", "0%")
        startCountDown();
        betSection.show();
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

function quitGame(id, force) {
    if (typeof id === 'undefined' || id === null) {
        console.warn('quitGame called without player id, aborting');
        return;
    }

    $.ajax({
        url: '/game/quitPoker',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        contentType: 'application/json',
        data: JSON.stringify({ playerId: Number(id), force: !!force }),
        success: function (response) {
            if (!force) {
                gameMessage.text("Vous allez quitter la table à la fin de cette manche");
            }
            else{
                joinButton.show();
            }
        },
        error: function (xhr, status, error) {
                console.error('Error quitting game:', status, error, xhr.responseText);
        }
    });
}

function initRound() {
    gameTerminated = false;
    if (restartTimeout) {
        clearTimeout(restartTimeout);
        restartTimeout = null;
    }
    
    $.ajax({
        url: '/game/initRound',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function (response) {
            gameMessage.text("");
        },
        error: function (xhr, status, error) {
            console.error('Error initializing round:', error);
            gameMessage.text("Erreur lors du démarrage de la partie");
        }
    });
}

function placeBet(bet) {
    pokerError.text("");
    betNotPlaced = false;
    $.ajax({
        url: '/game/placeBet',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: {
            amount: bet
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

async function startCountDown() {
    const duration = turnTime;
    const start = Date.now();
    betNotPlaced = true;
    while (betNotPlaced) {
        const elapsed = Date.now() - start;
        if (elapsed > duration) break;

        const progress = (elapsed / duration) * 100;
        timeProgressBar.css("width", `${progress}%`);
        await sleep(50);
    }
    if (betNotPlaced) {
        const currentPlayer = gameState?.players?.[gameState.playersTurn];
        if (currentPlayer?.id) {
            quitGame(currentPlayer.id, true);
        }
    }
}
function handlePlayerExit() {
    if (window.gameSession && window.gameSession.userId) {
        navigator.sendBeacon(
            "/game/quitPoker",
            JSON.stringify({
                playerId: Number(window.gameSession.userId),
                force: true
            })
        );
    }
}
window.addEventListener("beforeunload", handlePlayerExit);

document.addEventListener("visibilitychange", () => {
    if (document.visibilityState === "hidden") {
        handlePlayerExit();
    }
});

startRefreshInterval();