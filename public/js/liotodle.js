document.addEventListener("DOMContentLoaded", () => {
    createSquares();

    let guessedWords = [[]];
    let availableSpace = 1;
    let guessedWordCount = 0;
    let isSubmitting = false;
    let validWords = [];
    let secretWord = '';
    let keyboardState = {};
    let isGameLocked = false;
    let savedStateMeta = {
        completed: false,
        result: null,
        completedAt: null
    };

    const keys = document.querySelectorAll(".keyboard-row button");
    const gameSession = window.gameSession || {};
    const isAuthenticated = gameSession.userId !== undefined && gameSession.userId !== null;
    const userId = isAuthenticated ? gameSession.userId : "guest";
    const canPlayDaily = gameSession.dailyAvailable === true;
    const configuredLockMessage = typeof gameSession.lockMessage === "string" ? gameSession.lockMessage.trim() : "";
    const dailyLockMessages = {
        completed: "Vous avez déjà complété le Liotodle du jour. Reviens après le prochain reset !",
        guest: "Connecte-toi pour jouer au Liotodle du jour."
    };
    const STATE_STORAGE_KEY = `liotodle_game_state_${userId}`;
    const TILE_COLORS = {
        correct: "rgb(83, 141, 78)",
        present: "rgb(181, 159, 59)",
        absent: "rgb(58, 58, 60)"
    };
    const dailyLockMessageEl = document.getElementById("dailyLockMessage");

    function syncStateMeta(state) {
        if (!state) {
            savedStateMeta = {
                completed: false,
                result: null,
                completedAt: null
            };
            return;
        }

        savedStateMeta = {
            completed: Boolean(state.completed),
            result: state.result ?? null,
            completedAt: state.completedAt ?? null
        };
    }

    function showDailyLockMessage(message) {
        if (!dailyLockMessageEl) {
            return;
        }

        dailyLockMessageEl.textContent = message;
        dailyLockMessageEl.classList.add("show");
    }

    function setKeyboardEnabled(enabled) {
        for (let i = 0; i < keys.length; i++) {
            keys[i].disabled = !enabled;
        }
    }

    function lockGameForToday(message) {
        isGameLocked = true;
        setKeyboardEnabled(false);

        const fallbackMessage = isAuthenticated ? dailyLockMessages.completed : dailyLockMessages.guest;
        const finalMessage = message || configuredLockMessage || fallbackMessage;

        showDailyLockMessage(finalMessage);
    }

    function loadGameState() {
        if (typeof window === "undefined" || !window.localStorage) {
            return null;
        }

        try {
            const stored = window.localStorage.getItem(STATE_STORAGE_KEY);
            if (!stored) {
                syncStateMeta(null);
                return null;
            }

            const state = JSON.parse(stored);
            syncStateMeta(state);
            return state;
        } catch (error) {
            console.warn("Unable to load Liotodle state", error);
            syncStateMeta(null);
            return null;
        }
    }

    function persistGameState(overrides = {}) {
        if (typeof window === "undefined" || !window.localStorage || !secretWord) {
            return;
        }

        const state = {
            secretWord,
            guessedWords,
            guessedWordCount,
            completed: overrides.completed ?? savedStateMeta.completed ?? false,
            result: overrides.result ?? savedStateMeta.result ?? null,
            completedAt: overrides.completedAt ?? savedStateMeta.completedAt ?? null
        };

        try {
            window.localStorage.setItem(STATE_STORAGE_KEY, JSON.stringify(state));
            syncStateMeta(state);
        } catch (error) {
            console.warn("Unable to persist Liotodle state", error);
        }
    }

    function clearGameState() {
        if (typeof window === "undefined" || !window.localStorage) {
            return;
        }

        try {
            window.localStorage.removeItem(STATE_STORAGE_KEY);
            syncStateMeta(null);
        } catch (error) {
            console.warn("Unable to clear Liotodle state", error);
        }
    }

    function restoreGameFromState(state) {
        if (!state) {
            return;
        }

        syncStateMeta(state);
        const savedGuesses = Array.isArray(state.guessedWords)
            ? state.guessedWords.map(row => Array.isArray(row) ? row : String(row).split(""))
            : [[]];

        guessedWords = savedGuesses.length ? savedGuesses : [[]];
        guessedWordCount = typeof state.guessedWordCount === "number" ? state.guessedWordCount : 0;

        if (guessedWords.length === 0) {
            guessedWords.push([]);
        }

        if (guessedWords.length <= guessedWordCount) {
            guessedWords.push([]);
        }

        keyboardState = {};

        guessedWords.forEach((letters, rowIndex) => {
            letters.forEach((letter, columnIndex) => {
                const tileId = rowIndex * 5 + columnIndex + 1;
                const tile = document.getElementById(String(tileId));
                if (tile) {
                    tile.textContent = letter;
                    tile.style = "";
                    tile.classList.remove("animate__flipInX");
                }
            });

            if (rowIndex < guessedWordCount && letters.length === 5) {
                const guessWord = letters.join("").toUpperCase();
                const results = checkWordColors(guessWord, secretWord);

                results.forEach((status, columnIndex) => {
                    const tileId = rowIndex * 5 + columnIndex + 1;
                    const tile = document.getElementById(String(tileId));
                    if (tile) {
                        const tileColor = TILE_COLORS[status];
                        tile.style = `background-color:${tileColor};border-color:${tileColor}`;
                    }
                });

                updateKeyboardColors(guessWord, results);
            }
        });

        const currentWordArr = getCurrentWordArr();
        const currentWordLength = currentWordArr ? currentWordArr.length : 0;
        availableSpace = guessedWordCount * 5 + 1 + currentWordLength;
    }

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
        const savedState = loadGameState();

        if (!canPlayDaily) {
            if (savedState && savedState.secretWord) {
                secretWord = savedState.secretWord;
                restoreGameFromState(savedState);
            }

            lockGameForToday();
            return;
        }

        try {
            const wordsResponse = await fetch("/game/liotodle/list");
            const words = await wordsResponse.json();
            validWords = words.map(w => w.toUpperCase());

            if (
                savedState &&
                savedState.secretWord &&
                validWords.includes(savedState.secretWord) &&
                !savedState.completed
            ) {
                secretWord = savedState.secretWord;
                restoreGameFromState(savedState);
                return;
            }

            if (savedState && savedState.completed) {
                clearGameState();
            }

            secretWord = validWords[Math.floor(Math.random() * validWords.length)];
            guessedWords = [[]];
            guessedWordCount = 0;
            availableSpace = 1;
            persistGameState({ completed: false, result: null, completedAt: null });
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
        if (isGameLocked) {
            return;
        }

        const currentWordArr = getCurrentWordArr();

        if (currentWordArr && currentWordArr.length < 5) {
            currentWordArr.push(letter);

            const availableSpaceEl = document.getElementById(String(availableSpace));
            if (availableSpaceEl) {
                availableSpace = availableSpace + 1;
                availableSpaceEl.textContent = letter;
            }

            persistGameState();
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
        if (isSubmitting || isGameLocked) {
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
                const tileColor = TILE_COLORS[result[index]];
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

                const completionTimestamp = new Date().toISOString();
                persistGameState({ completed: true, result: 'win', completedAt: completionTimestamp });
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
                    }
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

                const completionTimestamp = new Date().toISOString();
                persistGameState({ completed: true, result: 'lose', completedAt: completionTimestamp });
                fetch('/game/liotodle/finish', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
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
            persistGameState();
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
        if (isGameLocked) {
            return;
        }

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
        persistGameState();
    }

    for (let i = 0; i < keys.length; i++) {
        keys[i].onclick = ({ target }) => {
            if (isSubmitting || isGameLocked) {
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
        if (isSubmitting || isGameLocked) {
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
