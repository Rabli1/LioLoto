let dealerSum = 0;
let playerSum = 0;

let hidden;
let deck;
let canHit = true;

window.onload = function () {
    buildDeck();
    shuffleDeck();
    startGame();
}

function buildDeck() {
    let values = ["A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K"];
    let types = ["C", "D", "H", "S"];
    deck = [];

    for (let i = 0; i < types.length; i++) {
        for (let j = 0; j < values.length; j++) {
            deck.push(values[j] + "-" + types[i]);
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
    console.log(deck);
}

function startGame() {
    hidden = deck.pop();
}

function getCardValue(card) {
    card.split('-');

    if (isNaN(card[0])) {
        if(card[0] == "A")
            return 11;
        else
            return 10;
    }
    else {
        return parseInt(card[0]);
    }
}