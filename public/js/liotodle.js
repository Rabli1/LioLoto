document.addEventListener("DOMContentLoaded", () => {
    createSquares();

    let guessedWords = [[]];
    let availableSpace = 1;
    let guessedWordCount = 0;
    let isSubmitting = false;
    let validWords = [];
    let secretWord = '';
    let keyboardState = {};

    const keys = document.querySelectorAll(".keyboard-row button");

    // tester ctrl+shift+s 
    window.revealSecret = () => {
        console.log("🔍 Mot secret:", secretWord);
        alert("Mot secret: " + secretWord);
        return secretWord;
    };

    async function initGame() {
        try {
            const wordsResponse = await fetch("/game/liotodle/list");
            const words = await wordsResponse.json();
            validWords = words.map(w => w.toUpperCase());

            secretWord = validWords[Math.floor(Math.random() * validWords.length)];
        } catch (error) {
            console.error("Erreur lors de l'initialisation:", error);
        }
    }

    initGame();

    function getCurrentWordArr() {
        const numberOfGuessedWords = guessedWords.length;
        return guessedWords[numberOfGuessedWords - 1];
    }

    function updateGuessedWords(letter) {
        const currentWordArr = getCurrentWordArr();

        if (currentWordArr && currentWordArr.length < 5) {
            currentWordArr.push(letter);

            const availableSpaceEl = document.getElementById(String(availableSpace));
            if (availableSpaceEl) {
                availableSpace = availableSpace + 1;
                availableSpaceEl.textContent = letter;
            }
        }
    }

    function checkWordColors(guessWord, secretWord) {
        const result = [];
        const secretLetters = secretWord.split('');
        const guessLetters = guessWord.split('');

        const secretUsed = new Array(5).fill(false);
        const guessResult = new Array(5).fill('absent');

        //lettre verte (correcte)
        for (let i = 0; i < 5; i++) {
            if (guessLetters[i] === secretLetters[i]) {
                guessResult[i] = 'correct';
                secretUsed[i] = true;
            }
        }

        //lettre jaune (presente)
        for (let i = 0; i < 5; i++) {
            if (guessResult[i] === 'correct') continue;

            for (let j = 0; j < 5; j++) {
                if (!secretUsed[j] && guessLetters[i] === secretLetters[j]) {
                    guessResult[i] = 'present';
                    secretUsed[j] = true;
                    break;
                }
            }
        }

        return guessResult;
    }

    function updateKeyboardColors(word, results) {
        const letters = word.split('');

        letters.forEach((letter, index) => {
            const currentStatus = results[index];
            const previousStatus = keyboardState[letter];

            if (previousStatus === 'correct') return;
            if (previousStatus === 'present' && currentStatus === 'absent') return;

            keyboardState[letter] = currentStatus;

            const keyButton = document.querySelector(`button[data-key="${letter.toLowerCase()}"]`);
            if (keyButton) {
                keyButton.classList.remove('key-correct', 'key-present', 'key-absent');

                if (currentStatus === 'correct') {
                    keyButton.classList.add('key-correct');
                } else if (currentStatus === 'present') {
                    keyButton.classList.add('key-present');
                } else if (currentStatus === 'absent') {
                    keyButton.classList.add('key-absent');
                }
            }
        });
    }

    function handleSubmitWord() {
        if (isSubmitting) {
            return;
        }

        const currentWordArr = getCurrentWordArr();
        if (currentWordArr.length !== 5) {
            window.alert("Le mot doit contenir 5 lettres");
            return;
        }

        isSubmitting = true;
        const currentWord = currentWordArr.join("").toUpperCase();

        if (!validWords.includes(currentWord)) {
            alert(currentWord + " est invalide !");
            isSubmitting = false;
            return;
        }

        const result = checkWordColors(currentWord, secretWord);
        const won = currentWord === secretWord;

        const firstLetterId = guessedWordCount * 5 + 1;
        const interval = 200;

        currentWordArr.forEach((letter, index) => {
            setTimeout(() => {
                const colorMap = {
                    'correct': 'rgb(83, 141, 78)',
                    'present': 'rgb(181, 159, 59)',
                    'absent': 'rgb(58, 58, 60)'
                };

                const tileColor = colorMap[result[index]];
                const letterId = firstLetterId + index;
                const letterEl = document.getElementById(letterId);
                if (letterEl) {
                    letterEl.classList.add("animate__flipInX");
                    letterEl.style = `background-color:${tileColor};border-color:${tileColor}`;
                }
            }, interval * index);
        });

        setTimeout(() => {
            updateKeyboardColors(currentWord, result);

            guessedWordCount += 1;
            if (won) {
                let points = 0;
                switch (guessedWordCount) {
                    case 1: points = 1000; break;
                    case 2: points = 800; break;
                    case 3: points = 600; break;
                    case 4: points = 500; break;
                    case 5: points = 400; break;
                    case 6: points = 300; break;
                    default: points = 0;
                }
                Balance.init({ session: window.gameSession });
                if (window.Balance) {
                    Balance.gain(points, { persist: true });
                    alert(`Félicitations ! Vous avez gagné ${points} points 🎉`);
                } else {
                    alert("Félicitations !");
                    console.warn("Balance non trouvée : impossible d'ajouter les points");
                }

                fetch('/game/liotodle/finish', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ finished: true })
                });

                isSubmitting = true;
                return;
            }

            if (guessedWordCount === 6 && !won) {
                window.alert("Vous avez perdu. Le mot était : " + secretWord);

                fetch('/game/liotodle/finish', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ finished: true })
                });
                
                isSubmitting = true;
                return;
            }

            guessedWords.push([]);
            isSubmitting = false;
        }, interval * 5 + 100);
    }

    function createSquares() {
        const gameBoard = document.getElementById("board");

        for (let index = 0; index < 30; index++) {
            let square = document.createElement("div");
            square.classList.add("square");
            square.classList.add("animate__animated");
            square.setAttribute("id", index + 1);
            gameBoard.appendChild(square);
        }
    }

    function handleDeleteLetter() {
        const currentWordArr = getCurrentWordArr();

        if (currentWordArr.length === 0) {
            return;
        }

        const removedLetter = currentWordArr.pop();
        guessedWords[guessedWords.length - 1] = currentWordArr;

        const currentRowStart = guessedWordCount * 5 + 1;
        const lastLetterPosition = currentRowStart + currentWordArr.length;
        const lastLetterEl = document.getElementById(String(lastLetterPosition));

        if (lastLetterEl) {
            lastLetterEl.textContent = "";
        }

        availableSpace = lastLetterPosition;
    }

    for (let i = 0; i < keys.length; i++) {
        keys[i].onclick = ({ target }) => {
            if (isSubmitting) {
                return;
            }

            const letter = target.getAttribute("data-key");

            if (letter === "enter") {
                handleSubmitWord();
                return;
            }

            if (letter === "del") {
                handleDeleteLetter();
                return;
            }

            updateGuessedWords(letter);
        };
    }

    document.addEventListener("keydown", (event) => {
        if (isSubmitting) {
            return;
        }

        const key = event.key.toLowerCase();

        // Ctrl + Shift + S pour tester
        if (event.ctrlKey && event.shiftKey && key === 's') {
            event.preventDefault();
            alert("🔍 Mot secret: " + secretWord);
            console.log("Mot secret:", secretWord);
            return;
        }

        if (key === "enter") {
            handleSubmitWord();
        } else if (key === "backspace") {
            handleDeleteLetter();
        } else if (/^[a-z]$/.test(key)) {
            updateGuessedWords(key);
        }
    });
});