let dealerSum = 0;
let playerSum = 0;

let hiddenCard;
let deck;
let canHit = true;

window.onload = function () {
    buildDeck();
    shuffleDeck();
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

function startGame() {
    let playerHand = [];
    let dealerHand = [];
    
    playerHand.push(deck.pop());
    dealerHand.push(deck.pop());
    playerHand.push(deck.pop());
    hiddenCard = deck.pop();
    dealerHand.push(hiddenCard);
    playerSum = getCardValue(playerHand[0]) + getCardValue(playerHand[1]);

    document.getElementById("dealerContainer").innerHTML = `<img src="/img/cards/${dealerHand[0]}.png" alt="${dealerHand[0]}"> <img src="/img/cards/BACK.png" alt="Hidden Card">`;
    document.getElementById("playerContainer").innerHTML = `<img src="/img/cards/${playerHand[0]}.png" alt="${playerHand[0]}"> <img src="/img/cards/${playerHand[1]}.png" alt="${playerHand[1]}">`;
    document.getElementById("betAmount").innerHTML = `<h2>Votre mise: ${betAmount}</h2>`;
    document.getElementById("playerSum").innerText = playerSum;
    
    if(getCardValue(dealerHand[0]) + getCardValue(hiddenCard) == 21){
        document.getElementById("dealerContainer").innerHTML = `<img src="/img/cards/${dealerHand[0]}.png" alt="${dealerHand[0]}"> <img src="/img/cards/${dealerHand[1]}.png" alt="${dealerHand[1]}">`;
    }
}

function getCardValue(card) {
    card.split('-');

    if (isNaN(card[0])) {
        if (card[0] == "A")
            return 11;
        return 10;
    }

    return parseInt(card[0]);
}