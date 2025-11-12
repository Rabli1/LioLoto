(function () {
    function createAudioToolkit() {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextClass) {
            return {
                resume() { },
            };
        }

        let context = null;

        function ensureContext() {
            if (!context) {
                context = new AudioContextClass();
            }
            return context;
        }

        function resume() {
            const ctx = ensureContext();
            if (!ctx) return;
            if (ctx.state === 'suspended') {
                ctx.resume();
            }
        }

        let bjWinSound = null;

        function getBjWinSound() {
            if (!bjWinSound) {
                const soundUrl = window.soundAssets?.bjWin || '../sounds/bjWin.wav';
                bjWinSound = new Audio(soundUrl);
                bjWinSound.preload = 'auto';
                bjWinSound.volume = 0.5;
                bjWinSound.load();
            }
            return bjWinSound;
        }

        function playBjWinSound() {
            const base = getBjWinSound();
            if (!base) return;

            const play = (audioNode) => {
                audioNode.currentTime = 0;
                const promise = audioNode.play();
                if (promise && typeof promise.catch === 'function') {
                    promise.catch(() => { });
                }
            };

            if (base.paused && base.readyState >= 3) {
                play(base);
            } else {
                const clone = base.cloneNode(true);
                clone.volume = base.volume;
                if (clone.readyState >= 3) {
                    play(clone);
                } else {
                    clone.addEventListener('canplaythrough', () => play(clone), { once: true });
                }
            }
        }

        let bjNormalSound = null;

        function getBjNormalSound() {
            if (!bjNormalSound) {
                const soundUrl = window.soundAssets?.normalWin || '../sounds/normalWin.wav';
                bjNormalSound = new Audio(soundUrl);
                bjNormalSound.preload = 'auto';
                bjNormalSound.volume = 0.5;
                bjNormalSound.load();
            }
            return bjNormalSound;
        }

        function playBjNormalSound() {
            const base = getBjNormalSound();
            if (!base) return;

            const play = (audioNode) => {
                audioNode.currentTime = 0;
                const promise = audioNode.play();
                if (promise && typeof promise.catch === 'function') {
                    promise.catch(() => { });
                }
            };

            if (base.paused && base.readyState >= 3) {
                play(base);
            } else {
                const clone = base.cloneNode(true);
                clone.volume = base.volume;
                if (clone.readyState >= 3) {
                    play(clone);
                } else {
                    clone.addEventListener('canplaythrough', () => play(clone), { once: true });
                }
            }
        }

        let tieGameSound = null;

        function getTieGameSound() {
            if (!tieGameSound) {
                const soundUrl = window.soundAssets?.tieGame || '../sounds/tieGame.mp3';
                tieGameSound = new Audio(soundUrl);
                tieGameSound.preload = 'auto';
                tieGameSound.volume = 0.5;
                tieGameSound.load();
            }
            return tieGameSound;
        }

        function playTieGameSound() {
            const base = getTieGameSound();
            if (!base) return;

            const play = (audioNode) => {
                audioNode.currentTime = 0;
                const promise = audioNode.play();
                if (promise && typeof promise.catch === 'function') {
                    promise.catch(() => { });
                }
            };

            if (base.paused && base.readyState >= 3) {
                play(base);
            } else {
                const clone = base.cloneNode(true);
                clone.volume = base.volume;
                if (clone.readyState >= 3) {
                    play(clone);
                } else {
                    clone.addEventListener('canplaythrough', () => play(clone), { once: true });
                }
            }
        }

        let loseGameSound = null;

        function getBjLoseSound() {
            if (!loseGameSound) {
                const soundUrl = window.soundAssets?.bjLose || '../sounds/rouletteLose.mp3';
                loseGameSound = new Audio(soundUrl);
                loseGameSound.preload = 'auto';
                loseGameSound.volume = 0.5;
                loseGameSound.load();
            }
            return loseGameSound;
        }

        function playBjLoseSound() {
            const base = getBjLoseSound();
            if (!base) return;

            const play = (audioNode) => {
                audioNode.currentTime = 0;
                const promise = audioNode.play();
                if (promise && typeof promise.catch === 'function') {
                    promise.catch(() => { });
                }
            };

            if (base.paused && base.readyState >= 3) {
                play(base);
            } else {
                const clone = base.cloneNode(true);
                clone.volume = base.volume;
                if (clone.readyState >= 3) {
                    play(clone);
                } else {
                    clone.addEventListener('canplaythrough', () => play(clone), { once: true });
                }
            }
        }

        return {
            resume,
            playBjWinSound,
            playBjNormalSound,
            playTieGameSound,
            playBjLoseSound,
            preloadSounds() {
                getBjWinSound();
                getBjNormalSound();
                getTieGameSound();
                getBjLoseSound();
            }
        };
    }

    const audio = createAudioToolkit();
    audio.preloadSounds();

    function settleRound(outcome) {
        if (!window.Balance) return;
        const stake = Number(window.currentBlackjackBet || 0) || 0;
        if (!stake) return;

        let payout = 0;
        let images = document.getElementsByClassName("playerCard");
        let resultMessage = document.getElementById("sumContainer");
        audio.resume();

        switch (outcome) {
            case 'blackjack':
                payout = Math.floor(stake * 5 / 2);
                for (let card of images) {
                    card.classList.add("blackjack");
                }
                audio.playBjWinSound();
                resultMessage.classList.add("blackjack");
                resultMessage.textContent += ` - Blackjack ! Mise ${stake}, gain ${payout - stake}.`;
                break;
            case 'win':
                payout = stake * 2;
                for (let card of images) {
                    card.classList.add("win");
                }
                audio.playBjNormalSound();
                resultMessage.classList.add("win");
                resultMessage.textContent += ` - Vous gagner. Mise ${stake} gagné.`;
                break;
            case 'push':
                payout = stake;
                for (let card of images) {
                    card.classList.add("draw");
                }
                audio.playTieGameSound();
                resultMessage.classList.add("draw");
                resultMessage.textContent += ` - Égalité. Mise ${stake} rendue.`;
                break;
            case 'dealer-blackjack':
                payout = 0;
                for (let card of images) {
                    card.classList.add("lose");
                }
                resultMessage.classList.add("lose");
                resultMessage.textContent += ` - Le croupier a un blackjack ! Mise ${stake} perdue.`;
                break;
            default:
                for (let card of images) {
                    card.classList.add("lose");
                }
                audio.playBjLoseSound();
                resultMessage.classList.add("lose");
                resultMessage.textContent += ` - Vous perdez. Mise ${stake} perdue.`;
        }

        if (payout > 0) {
            window.Balance.gain(payout);
        }
        window.currentBlackjackBet = 0;

    }

    document.addEventListener('DOMContentLoaded', () => {
        const session = window.blackjackSession || {};
        window.currentBlackjackBet = Number(window.currentBlackjackBet || 0) || 0;
        window.pendingBlackjackBet = Number(window.pendingBlackjackBet || 0) || 0;

        if (window.Balance) {
            window.Balance.init({
                session,
                displaySelectors: ['#blackjack-balance'],
            });
        }

        document.addEventListener('blackjack:result', (event) => {
            settleRound(event.detail?.outcome);
        });

        document.addEventListener('blackjack:resultSplit', (event) => {
            settleRoundSplit(
                event.detail?.outcome1,
                event.detail?.outcome2,
                event.detail?.betAmountHand1,
                event.detail?.betAmountHand2
            );
        });

        document.addEventListener('blackjack:bet-reset', () => {
            window.currentBlackjackBet = 0;
        });
    });

    function settleRoundSplit(outcome1, outcome2, betAmountHand1, betAmountHand2) {
        if (!window.Balance) return;

        // Utiliser les montants individuels s'ils sont fournis, sinon fallback à l'ancien système
        let stakePerHand1, stakePerHand2;
        if (betAmountHand1 && betAmountHand2) {
            stakePerHand1 = betAmountHand1;
            stakePerHand2 = betAmountHand2;
        } else {
            const stake = Number(window.currentBlackjackBet || 0) || 0;
            if (!stake) return;
            stakePerHand1 = stake / 2;
            stakePerHand2 = stake / 2;
        }

        audio.resume();
        let payout1 = 0;
        let images1 = document.querySelectorAll("#playerContainer .playerCard");
        let resultMessage1 = document.getElementById("sumContainer");
        switch (outcome1) {
            case 'blackjack':
                payout1 = Math.floor(stakePerHand1 * 5 / 2);
                images1.forEach(card => card.classList.add("blackjack"));
                audio.playBjWinSound();
                resultMessage1.classList.add("blackjack");
                resultMessage1.textContent += ` - Main 1 : Blackjack ! Mise ${stakePerHand1}, gain ${payout1 - stakePerHand1}.`;
                break;
            case 'win':
                payout1 = stakePerHand1 * 2;
                images1.forEach(card => card.classList.add("win"));
                audio.playBjNormalSound();
                resultMessage1.classList.add("win");
                resultMessage1.textContent += ` - Main 1 : Vous gagnez. Mise ${stakePerHand1} gagnée.`;
                break;
            case 'push':
                payout1 = stakePerHand1;
                images1.forEach(card => card.classList.add("draw"));
                audio.playTieGameSound();
                resultMessage1.classList.add("draw");
                resultMessage1.textContent += ` - Main 1 : Égalité. Mise ${stakePerHand1} rendue.`;
                break;
            case 'dealer-blackjack':
                payout1 = 0;
                images1.forEach(card => card.classList.add("lose"));
                resultMessage1.classList.add("lose");
                audio.playBjLoseSound();
                resultMessage1.textContent += ` - Main 1 : Le croupier a un blackjack ! Mise ${stakePerHand1} perdue.`;
                break;
            default:
                images1.forEach(card => card.classList.add("lose"));
                resultMessage1.classList.add("lose");
                audio.playBjLoseSound();
                resultMessage1.textContent += ` - Main 1 : Vous perdez. Mise ${stakePerHand1} perdue.`;
        }

        let payout2 = 0;
        let images2 = document.querySelectorAll("#playerContainerSplit .playerCard");
        let resultMessage2 = document.getElementById("sumContainerSplit");
        switch (outcome2) {
            case 'blackjack':
                payout2 = Math.floor(stakePerHand2 * 5 / 2);
                images2.forEach(card => card.classList.add("blackjack"));
                audio.playBjWinSound();
                resultMessage2.classList.add("blackjack");
                resultMessage2.textContent += ` - Main 2 : Blackjack ! Mise ${stakePerHand2}, gain ${payout2 - stakePerHand2}.`;
                break;
            case 'win':
                payout2 = stakePerHand2 * 2;
                images2.forEach(card => card.classList.add("win"));
                audio.playBjNormalSound();
                resultMessage2.classList.add("win");
                resultMessage2.textContent += ` - Main 2 : Vous gagnez. Mise ${stakePerHand2} gagnée.`;
                break;
            case 'push':
                payout2 = stakePerHand2;
                images2.forEach(card => card.classList.add("draw"));
                audio.playTieGameSound();
                resultMessage2.classList.add("draw");
                resultMessage2.textContent += ` - Main 2 : Égalité. Mise ${stakePerHand2} rendue.`;
                break;
            case 'dealer-blackjack':
                payout2 = 0;
                images2.forEach(card => card.classList.add("lose"));
                resultMessage2.classList.add("lose");
                audio.playBjLoseSound();
                resultMessage2.textContent += ` - Main 2 : Le croupier a un blackjack ! Mise ${stakePerHand2} perdue.`;
                break;
            default:
                images2.forEach(card => card.classList.add("lose"));
                resultMessage2.classList.add("lose");
                audio.playBjLoseSound();
                resultMessage2.textContent += ` - Main 2 : Vous perdez. Mise ${stakePerHand2} perdue.`;
        }

        if (payout1 > 0) window.Balance.gain(payout1);
        if (payout2 > 0) window.Balance.gain(payout2);

        window.currentBlackjackBet = 0;
    }
})();
