const playerSeats = $(".playerSeat");
const pot = $("#pot");
const joinButton = $("#join-button");
const requiredCall = $("#required-call");
const centerCards = $('#poker-center img');

const intervalTime = 10 * 1000; // 10 seconds
let gameState = {};
let currentEtag = "";
const csrfToken = window.gameSession.csrfToken;
let deck = [];
joinButton.on('click', () => {
    joinGame();
    joinButton.hide();
});



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
            gameState = response.gameState || {};
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
        } else {
            seat.removeClass('dimmed activeTurn').addClass('emptySeat');
        }
    });


    if (gameState.requiredBet)
        requiredCall.text(`(${gameState.requiredBet} pour call)`);

    (gameState.cardsShown || []).forEach((card, idx) => {
        if (idx < centerCards.length)
            centerCards.eq(idx).attr('src', `/img/cards/${card}.png`);
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

function buildDeck() {
    const values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
    const suits = ['C', 'D', 'H', 'S'];
    deck = [];

    suits.forEach((suit) => {
        values.forEach((value) => {
            deck.push(`${value}-${suit}`);
        });
    });
    // Shuffle
    for (let i = deck.length - 1; i > 0; i -= 1) {
        const j = Math.floor(Math.random() * (i + 1));
        [deck[i], deck[j]] = [deck[j], deck[i]];
    }
}

startRefreshInterval();