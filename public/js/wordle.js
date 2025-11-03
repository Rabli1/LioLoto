document.addEventListener("DOMContentLoaded", () => {
    createSquares();
    getNewWord();

    let guessedWords = [[]];
    let availableSpace = 1;
    let guessedWordCount = 0;
    let isSubmitting = false; //pour pas quon envoie un nouveau lors de lanimation/verif si mot valide

    const keys = document.querySelectorAll(".keyboard-row button");

    function getNewWord() {
        fetch("/game/wordle/word")
            .then(res => res.json())
            .then(data => {
                console.log("Nouvelle partie initialisée");
            })
            .catch(error => {
                console.error("Erreur lors de l'initialisation:", error);
            });
    }

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

        fetch("/game/wordle/check?word=" + currentWord)
            .then(res => res.json())
            .then(data => {
                if (!data.valid) {
                    alert("Mot non reconnu !");
                    isSubmitting = false;
                    return;
                }

                const firstLetterId = guessedWordCount * 5 + 1;
                const interval = 200;
                
                currentWordArr.forEach((letter, index) => {
                    setTimeout(() => {
                        const colorMap = {
                            'correct': 'rgb(83, 141, 78)',
                            'present': 'rgb(181, 159, 59)',
                            'absent': 'rgb(58, 58, 60)'
                        };
                        
                        const tileColor = colorMap[data.result[index]];
                        const letterId = firstLetterId + index;
                        const letterEl = document.getElementById(letterId);
                        if (letterEl) {
                            letterEl.classList.add("animate__flipInX");
                            letterEl.style = `background-color:${tileColor};border-color:${tileColor}`;
                        }
                    }, interval * index);
                });

                // Attendre fin animation
                setTimeout(() => {

                    guessedWordCount += 1;
                    if (data.won) {
                        window.alert("Félicitations !");
                        isSubmitting = true; 
                        return;
                    }

                    if (guessedWordCount === 6 && !data.won) {
                        window.alert("Désolé, vous n'avez plus d'essais !");
                        isSubmitting = true; 
                        return;
                    }

                    guessedWords.push([]);
                    isSubmitting = false; 
                }, interval * 5 + 100);
            })
            .catch(error => {
                console.error("Erreur lors de la vérification:", error);
                alert("Erreur de connexion");
                isSubmitting = false;
            });
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

        if (key === "enter") {
            handleSubmitWord();
        } else if (key === "backspace") {
            handleDeleteLetter();
        } else if (/^[a-z]$/.test(key)) {
            updateGuessedWords(key);
        }
    });
});