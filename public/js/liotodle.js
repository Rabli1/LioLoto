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

    function showResultModal(won, attempts, points) {
        const modal = document.getElementById('resultModal');

        const pointsDiv = document.getElementById('resultPoints');

        if (won) {
            pointsDiv.textContent = `+${points} points`;
            pointsDiv.classList.remove('lost');
        } else {
            pointsDiv.textContent = 'Aucun point';
            pointsDiv.classList.add('lost');
        }

        modal.classList.add('show');
        setTimeout(() => {
            modal.classList.remove('show');
        }, 2000);
    }

    function shakeRow(rowIndex) {
        const start = rowIndex * 5 + 1;

        for (let i = 0; i < 5; i++) {
            const tile = document.getElementById(start + i);

            tile.classList.add("animate__shakeX");

            tile.addEventListener("animationend", () => {
                tile.classList.remove("animate__shakeX");
            });
        }
    }

    async function initGame() {
        try {
            const wordsResponse = await fetch("/game/liotodle/list");
            const words = await wordsResponse.json();
            validWords = words.map(w => w.toUpperCase());

            secretWord = validWords[Math.floor(Math.random() * validWords.length)];
        } catch (error) {
            console.error("Erreur", error);
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

        for (let i = 0; i < 5; i++) {
            if (guessLetters[i] === secretLetters[i]) {
                guessResult[i] = 'correct';
                secretUsed[i] = true;
            }
        }

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
            return;
        }

        isSubmitting = true;
        const currentWord = currentWordArr.join("").toUpperCase();

        if (!validWords.includes(currentWord)) {
            shakeRow(guessedWordCount);
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
                }

                showResultModal(true, guessedWordCount, points);

                fetch('/game/liotodle/finish', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ finished: true })
                });

                 const soundUrl = window.soundAssets?.rouletteWin || '../sounds/rouletteWin.wav';
                wordleWinSoundBase = new Audio(soundUrl);
                wordleWinSoundBase.preload = 'auto';
                wordleWinSoundBase.volume = 0.5;
                wordleWinSoundBase.load();
                wordleWinSoundBase.play();

                isSubmitting = true;
                return;
            }

            if (guessedWordCount === 6 && !won) {
                showResultModal(false, guessedWordCount, 0);

                fetch('/game/liotodle/finish', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ finished: true })
                });

                const soundUrl = window.soundAssets?.rouletteWin || '../sounds/sadTrombone.mp3';
                wordleLoseSoundBase = new Audio(soundUrl);
                wordleLoseSoundBase.preload = 'auto';
                wordleLoseSoundBase.volume = 0.5;
                wordleLoseSoundBase.load();
                wordleLoseSoundBase.play();

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

        if (event.ctrlKey && event.shiftKey && key === 's') {
            event.preventDefault();
            alert("Mot secret: " + secretWord);
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