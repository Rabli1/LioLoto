let dealerSum = 0;
let playerSum = 0;

let hiddenCard;
let deck;
let canHit = true;
let betAmount = null;

let playerHand = [];
let dealerHand = [];


window.onload = function () {
    buildDeck();
    shuffleDeck();
    document.getElementById("hitButton").addEventListener("click", hit);
    document.getElementById("stayButton").addEventListener("click", stay);
    document.getElementById("gameMat").style.display = "none";

    document.querySelectorAll('.betToken').forEach(btn => {
        btn.addEventListener('click', function () {
            betAmount = parseInt(this.getAttribute('data-value'));
            document.getElementById('selectedBet').textContent = `Mise sélectionnée : ${betAmount}`;
        });
    });

    document.getElementById("placeBet").addEventListener("click", function () {
        if (!betAmount) {
            alert("Veuillez sélectionner un jeton de mise !");
            return;
        }
        document.getElementById("betContainer").style.display = "none";
        document.getElementById("gameMat").style.display = "block";
        startGame();
    });
}

function buildDeck() {
    let values = ["A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K"];
    let types = ["C", "D", "H", "S"];
    deck = [];

    for (let i = 0; i < types.length; i++) {
        for (let j = 0; j < values.length; j++) {
            deck.push(values[j] + '-' + types[i]);
        }
    }

    for (let i = 0; i < types.length; i++) {
        for (let j = 0; j < values.length; j++) {
            deck.push(values[j] + '-' + types[i]);
        }
    }
}

function shuffleDeck() {
    for (let i = 0; i < deck.length; i++) {
        let j = Math.floor(Math.random() * deck.length);
        let shuffledCard = deck[i];
        deck[i] = deck[j];
        deck[j] = shuffledCard;
    }
}

function getCardValue(card) {
    let value = card.split('-')[0];
    if (value === "A") return 11;
    if (["K", "Q", "J"].includes(value)) return 10;
    return parseInt(value);
}

function aceAdjustment(hand, sum) {
    let aceCount = hand.filter(card => card.startsWith("A")).length;
    while (sum > 21 && aceCount > 0) {
        sum -= 10;
        aceCount--;
    }
    return sum;
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

    playerSum = getCardValue(playerHand[0]) + getCardValue(playerHand[1]);
    playerSum = aceAdjustment(playerHand, playerSum);
    dealerSum = getCardValue(dealerHand[0]) + getCardValue(hiddenCard);
    dealerSum = aceAdjustment(dealerHand, dealerSum); 

    document.getElementById("dealerContainer").innerHTML = `<img src="/img/cards/${dealerHand[0]}.png" alt="${dealerHand[0]}"> <img src="/img/cards/BACK.png" alt="Hidden Card">`;
    document.getElementById("playerContainer").innerHTML = `<img src="/img/cards/${playerHand[0]}.png" alt="${playerHand[0]}"> <img src="/img/cards/${playerHand[1]}.png" alt="${playerHand[1]}">`;
    document.getElementById("betAmount").innerHTML = `<h2>Votre mise: ${betAmount}</h2>`;
    document.getElementById("playerSum").innerText = playerSum;

    if (dealerSum == 21) {
        document.getElementById("dealerContainer").innerHTML = `<img src="/img/cards/${dealerHand[0]}.png" alt="${dealerHand[0]}"> <img src="/img/cards/${dealerHand[1]}.png" alt="${dealerHand[1]}">`;
        setTimeout(function () {
            if (playerSum == 21) {
                alert("Égalité !");
            } else {
                alert("Le croupier a un Blackjack ! Vous perdez.");
            }
            restartGame();
        }, 500);
    }

    else if (playerSum == 21) {
        setTimeout(function () {
            alert("Blackjack ! Vous gagnez !");
            restartGame();
        }, 500);
    }
}

function restartGame() {
    dealerSum = 0;
    playerSum = 0;
    playerHand = [];
    dealerHand = [];
    hiddenCard = null;
    canHit = true;
    betAmount = null;
    document.getElementById("betContainer").style.display = "block";
    document.getElementById("gameMat").style.display = "none";
    document.getElementById('selectedBet').textContent = '';
    document.getElementById("dealerContainer").innerHTML = "";
    document.getElementById("playerContainer").innerHTML = "";
    document.getElementById("betAmount").innerHTML = "";
    document.getElementById("playerSum").innerText = "";
    buildDeck();
    shuffleDeck();
}

function hit() {
    if (canHit) {
        playerHand.push(deck.pop());
        playerSum += getCardValue(playerHand[playerHand.length - 1]);
        playerSum = aceAdjustment(playerHand, playerSum); 
        document.getElementById("playerContainer").innerHTML += `<img src="/img/cards/${playerHand[playerHand.length - 1]}.png" alt="${playerHand[playerHand.length - 1]}">`;
        document.getElementById("playerSum").innerText = playerSum;
        if (playerSum > 21) {
            setTimeout(function () {
                alert("Vous avez dépassé 21 ! Vous perdez.");
                canHit = false;
                restartGame();
            }, 500);
        }
    }
}

function stay() {
    canHit = false;
    document.getElementById("dealerContainer").innerHTML = `<img src="/img/cards/${dealerHand[0]}.png" alt="${dealerHand[0]}"> <img src="/img/cards/${dealerHand[1]}.png" alt="${dealerHand[1]}">`;

    function dealerDraw(index) {
        if (dealerSum < 17) {
            let card = deck.pop();
            dealerHand.push(card);
            dealerSum += getCardValue(card);
            dealerSum = aceAdjustment(dealerHand, dealerSum);
            setTimeout(function () {
                document.getElementById("dealerContainer").innerHTML += `<img src="/img/cards/${card}.png" alt="${card}">`;
                dealerDraw(index + 1);
            }, 700);
        } else {
            setTimeout(function () {
                if (dealerSum > 21) {
                    alert("Le croupier dépasse 21 ! Vous gagnez !");
                } else if (dealerSum > playerSum) {
                    alert("Le croupier gagne !");
                } else if (dealerSum < playerSum) {
                    alert("Vous gagnez !");
                } else {
                    alert("Égalité !");
                }
                restartGame();
            }, 700);
        }
    }

    dealerDraw(2);
}