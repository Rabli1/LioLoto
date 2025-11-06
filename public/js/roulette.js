(function () {
    let rouletteNumbers = [0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5, 24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26];
    let redNumbers = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];
    let tokenPlaced = false;
    let resultSpin;

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

        let rouletteSoundBase = null;

        function getRouletteSound() {
            if (!rouletteSoundBase) {
                const soundUrl = window.soundAssets?.roulette || '../sounds/roulette.wav';
                rouletteSoundBase = new Audio(soundUrl);
                rouletteSoundBase.preload = 'auto';
                rouletteSoundBase.volume = 0.5;
                rouletteSoundBase.load();
            }
            return rouletteSoundBase;
        }

        function playRouletteSound() {
            const base = getRouletteSound();
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

        let rouletteWinSoundBase = null;

        function getRouletteWinSound() {
            if (!rouletteWinSoundBase) {
                const soundUrl = window.soundAssets?.rouletteWin || '../sounds/rouletteWin.wav';
                rouletteWinSoundBase = new Audio(soundUrl);
                rouletteWinSoundBase.preload = 'auto';
                rouletteWinSoundBase.volume = 0.5;
                rouletteWinSoundBase.load();
            }
            return rouletteWinSoundBase;
        }

        function playRouletteWinSound() {
            const base = getRouletteWinSound();
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

        let rouletteLoseSoundBase = null;

        function getRouletteLoseSound() {
            if (!rouletteLoseSoundBase) {
                const soundUrl = window.soundAssets?.rouletteLose || '../sounds/rouletteLose.mp3';
                rouletteLoseSoundBase = new Audio(soundUrl);
                rouletteLoseSoundBase.preload = 'auto';
                rouletteLoseSoundBase.volume = 0.5;
                rouletteLoseSoundBase.load();
            }
            return rouletteLoseSoundBase;
        }

        function playRouletteLoseSound() {
            const base = getRouletteLoseSound();
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


        let tokenSoundBase = null;

        function getTokenSound() {
            if (!tokenSoundBase) {
                const soundUrl = window.soundAssets?.token || '../sounds/token.mp3';
                tokenSoundBase = new Audio(soundUrl);
                tokenSoundBase.preload = 'auto';
                tokenSoundBase.volume = 0.5;
                tokenSoundBase.load();
            }
            return tokenSoundBase;
        }

        function playTokenSound() {
            const base = getTokenSound();
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
            playRouletteSound,
            playTokenSound,
            playRouletteWinSound,
            playRouletteLoseSound,
            preloadSounds() {
                getRouletteSound();
                getTokenSound();
                getRouletteWinSound();
                getRouletteLoseSound();
            }
        };
    }

    const audio = createAudioToolkit();

    audio.preloadSounds();


    function initBettingMat() {
        const bettingMat = document.getElementById('bettingMat');
        const item0 = document.createElement('div');
        let rowCnt = 6;
        let colCnt = 2;

        item0.textContent = 0;
        item0.id = 'item0';
        item0.classList.add('clickRoulette');
        item0.dataset.cases = 0;

        bettingMat.appendChild(item0);

        //Génère les cases numéro
        for (let i = 1; i < 37; i++) {
            const item = document.createElement('div');
            item.textContent = i;
            item.id = `item${i}`;
            item.classList.add('clickRoulette');
            item.dataset.cases = i;

            if (rowCnt < 2) {
                rowCnt = 6;
            }

            if (redNumbers.includes(i)) {
                item.style.backgroundColor = 'rgba(211, 47, 47, 0.5)';
            }
            else {
                item.style.background = 'rgba(255, 255, 255, 0.06)';
            }
            item.style.gridRow = rowCnt;
            item.style.gridColumn = colCnt;

            if (i % 3 == 0) {
                colCnt += 2;
            }
            rowCnt -= 2;
            bettingMat.appendChild(item);
        }

        //Génère les cases gap
        let numCaseGapV = 3;
        let numCaseGapH = 2;
        for (let i = 2; i < 7; i++) {
            if (i == 2 || i == 4 || i == 6) {
                for (let j = 3; j < 25; j += 2) {
                    const item = document.createElement('div');
                    item.style.gridRow = i;
                    item.style.gridColumn = j;
                    item.classList.add('gap', 'clickRoulette');
                    item.dataset.cases = `${numCaseGapV} et ${numCaseGapV += 3}`;
                    if (numCaseGapV == 36) {
                        numCaseGapV = 2;
                    }
                    else if (numCaseGapV == 35) {
                        numCaseGapV = 1;
                    }
                    bettingMat.appendChild(item);
                }
            }
            else {
                for (let j = 2; j < 25; j += 2) {
                    const item = document.createElement('div');
                    item.style.gridRow = i;
                    item.style.gridColumn = j;
                    item.classList.add('gap', 'clickRoulette');
                    item.dataset.cases = `${numCaseGapH} et ${numCaseGapH += 1}`;
                    numCaseGapH += 2;
                    if (numCaseGapH == 36) {
                        numCaseGapH = 1;
                    }
                    bettingMat.appendChild(item);
                }
            }
        }

        //Génère les cases 2:1
        for (let i = 2; i < 7; i++) {
            const rightSideItem = document.createElement('div');
            if (i == 3 || i == 5) {
                i++;
            }
            rightSideItem.textContent = '2:1';
            rightSideItem.classList.add('rightSideItem', 'clickRoulette');
            rightSideItem.style.gridRow = i;
            rightSideItem.dataset.cases = `col${i / 2}`;
            bettingMat.appendChild(rightSideItem);
        }

        //Génère les cases au top
        for (let i = 2; i < 19; i = i + 8) {
            const topItem = document.createElement('div');
            if (i == 2) {
                topItem.textContent = '1 à 12';
                topItem.dataset.cases = '1-12';
            }
            else if (i == 10) {
                topItem.textContent = '13 à 24';
                topItem.dataset.cases = '13-24';
            }
            else if (i == 18) {
                topItem.textContent = '25 à 36';
                topItem.dataset.cases = '25-36';
            }
            topItem.classList.add('topItem', 'clickRoulette');
            topItem.style.gridColumn = `${i}/${i + 7}`;
            bettingMat.appendChild(topItem);
        }

        //Génère les cases en bas
        for (let i = 2; i < 23; i = i + 4) {
            const bottomItem = document.createElement('div');
            if (i == 2) {
                bottomItem.textContent = '1 à 18';
                bottomItem.dataset.cases = '1-18';
            }
            else if (i == 6) {
                bottomItem.textContent = 'Pair';
                bottomItem.dataset.cases = 'p';
            }
            else if (i == 10) {
                bottomItem.textContent = 'Rouge';
                bottomItem.style.backgroundColor = 'rgba(211, 47, 47, 0.5)';
                bottomItem.dataset.cases = 'r';
            }
            else if (i == 14) {
                bottomItem.textContent = 'Noir';
                bottomItem.style.backgroundColor = 'rgba(255, 255, 255, 0.06)';
                bottomItem.dataset.cases = 'n';
            }
            else if (i == 18) {
                bottomItem.textContent = 'Impair';
                bottomItem.dataset.cases = 'i';
            }
            else {
                bottomItem.textContent = '19 à 36';
                bottomItem.dataset.cases = '19-36';

            }
            bottomItem.classList.add('bottomItem', 'clickRoulette');
            bottomItem.style.gridColumn = `${i}/${i + 3}`;
            bettingMat.appendChild(bottomItem);
        }
    }

    function initRoulette() {
        const rouletteContainer = document.getElementById('rouletteContainer');
        const roulette = document.createElement('div');
        roulette.classList.add('roulette');

        const outerRim = document.createElement('div');
        outerRim.classList.add('outerRim');
        roulette.append(outerRim);

        for (let i = 0; i < rouletteNumbers.length; i++) {
            const rouletteSect = document.createElement('div');
            const numSect = document.createElement('span');
            rouletteSect.id = `sect${i}`;
            rouletteSect.classList.add('rouletteSect');
            if (rouletteNumbers[i] < 10) {
                numSect.classList.add('singleNum');
            }
            else {
                numSect.classList.add('doubleNum');
            }
            numSect.innerText = rouletteNumbers[i];
            rouletteSect.append(numSect);
            const block = document.createElement('div');
            block.classList.add('block');
            rouletteSect.append(block);
            roulette.append(rouletteSect);
        }

        const rimPocket = document.createElement('div');
        rimPocket.classList.add('rimPockets');
        roulette.append(rimPocket);

        const ballContainer = document.createElement('div');
        ballContainer.classList.add('ballContainer');
        const ball = document.createElement('div');
        ball.classList.add('ball');
        ballContainer.append(ball);
        roulette.append(ballContainer);

        const pocketSect = document.createElement('div');
        pocketSect.classList.add('pocketSect');
        roulette.append(pocketSect);

        const middleRoulette = document.createElement('div');
        middleRoulette.classList.add('middleRoulette');
        roulette.append(middleRoulette);

        const spinButton = document.createElement('button');
        spinButton.classList.add('spinButton');
        spinButton.id = 'buttonSpin';
        spinButton.innerText = 'Spin';
        if (tokenPlaced == false) {
            spinButton.hidden = true;
        }
        roulette.append(spinButton);

        rouletteContainer.append(roulette);
    }

    function spinRoulette(winningNumber) {
        const roulette = document.querySelector('.roulette');
        const ballContainer = document.querySelector('.ballContainer');
        const buttonSpin = document.getElementById('buttonSpin');

        if (buttonSpin) {
            buttonSpin.disabled = true;
        }

        audio.resume();
        audio.playRouletteSound();

        for (let i = 0; i < rouletteNumbers.length; i++) {
            if (rouletteNumbers[i] == winningNumber) {
                var degree = (i * -9.73) + 362;
            }
        }
        roulette.style.cssText = 'animation: wheelRotate 5s linear infinite;';
        ballContainer.style.cssText = 'animation: ballRotate 1s linear infinite;';

        setTimeout(function () {
            ballContainer.style.cssText = 'animation: ballRotate 2s linear infinite;';
            style = document.createElement('style');
            style.type = 'text/css';
            style.innerText = '@keyframes ballStop {from {transform: rotate(0deg);}to{transform: rotate(-' + degree + 'deg);}}';
            document.head.appendChild(style);
        }, 2000);
        setTimeout(function () {
            ballContainer.style.cssText = 'animation: ballStop 3s linear;';
        }, 6000);
        setTimeout(function () {
            ballContainer.style.cssText = 'transform: rotate(-' + degree + 'deg);';
        }, 9000);
        setTimeout(function () {
            roulette.style.cssText = '';
            style.remove();

            try {
                settleRouletteBets(winningNumber);
            } catch (e) {
                console.error('Erreur de règlement des mises roulette:', e);
            }

            if (buttonSpin) {
                buttonSpin.disabled = false;
            }
        }, 10000);

    }

    function isRed(num) {
        const redNumbers = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];
        return redNumbers.includes(num);
    }

    function payoutMultiplier(betCase, winningNumber, parentElement) {
        const s = String(betCase).trim();
        const isColumnCell = !!(parentElement && parentElement.classList.contains('rightSideItem'));

        // 2:1 bet
        if (isColumnCell) {
            let col = 0;
            if (s.startsWith('col')) {
                col = parseInt(s.slice(3), 10);
            } else {
                col = parseInt(s, 10);
            }
            if ([1, 2, 3].includes(col)) {
                const inColumn = winningNumber !== 0 && ((winningNumber - col) % 3 === 0);
                return inColumn ? 3 : 0;
            }
            return 0;
        }

        // Num bet
        if (s !== '') {
            const n = Number(s);
            if (Number.isInteger(n) && String(n) === s && n >= 0 && n <= 36) {
                return n === winningNumber ? 36 : 0;
            }
        }

        // Gap bet
        if (s.includes(' et ')) {
            const [aStr, bStr] = s.split(' et ').map((v) => parseInt(v, 10));
            if ([aStr, bStr].includes(winningNumber)) {
                return 18; // 17:1 profit, 18 with stake
            }
            return 0;
        }

        // 1 a 18 bet
        if (s === '1-18') {
            return (winningNumber >= 1 && winningNumber <= 18) ? 2 : 0;
        }
        // 19 a 36 bet
        if (s === '19-36') {
            return (winningNumber >= 19 && winningNumber <= 36) ? 2 : 0;
        }

        // Douzaine bet
        if (s === '1-12' || s === '13-24' || s === '25-36') {
            const [minStr, maxStr] = s.split('-');
            const min = parseInt(minStr, 10);
            const max = parseInt(maxStr, 10);
            return (winningNumber >= min && winningNumber <= max) ? 3 : 0;
        }

        // Rouge, Noir, Pair Impair bet
        switch (s) {
            case 'p':
                return (winningNumber !== 0 && winningNumber % 2 === 0) ? 2 : 0;
            case 'i':
                return (winningNumber % 2 === 1) ? 2 : 0;
            case 'r':
                return (winningNumber !== 0 && isRed(winningNumber)) ? 2 : 0;
            case 'n':
                return (winningNumber !== 0 && !isRed(winningNumber)) ? 2 : 0;
        }

        return 0;
    }

    function settleRouletteBets(winningNumber) {
        const tokens = document.querySelectorAll('.rouletteToken');
        if (!tokens.length) {
            if (window.Balance && typeof window.Balance.ajouterMontantJSON === 'function') {
                window.Balance.ajouterMontantJSON();
            }
            return;
        }

        let totalPayout = 0;
        let totalBet = 0;
        let hasWin = false;

        tokens.forEach(token => {
            const stake = parseInt(token.textContent, 10) || 0;
            totalBet += stake;
            const parent = token.parentElement;
            if (!stake || !parent) return;
            const betDescriptor = String(parent.dataset.cases || '').trim();
            const multiplier = payoutMultiplier(betDescriptor, winningNumber, parent);
            if (multiplier > 0) {
                totalPayout += stake * multiplier;
                hasWin = true;
                parent.classList.add('winnerBet');
            }
        });

        const container = document.getElementById('endContainer');
        const endAmount = document.getElementById('endAmount');
        const winnerBet = document.querySelectorAll('.winnerBet');

        if (container && endAmount) {
            if (hasWin) {
                audio.resume();
                audio.playRouletteWinSound();
                container.classList.add('roulette-win');
                endAmount.classList.add('winAmount');
                winnerBet.forEach(winner => {winner.classList.add('winnerBox');});
                endAmount.textContent = `+${totalPayout}$`;
            } else {
                audio.resume();
                audio.playRouletteLoseSound();
                container.classList.add('roulette-lose');
                endAmount.classList.add('loseAmount');
                endAmount.textContent = `-${totalBet}$`;
            }

            setTimeout(() => {
                container.classList.remove('roulette-win', 'roulette-lose');
                endAmount.classList.remove('winAmount', 'loseAmount');
                winnerBet.forEach(winner => {winner.classList.remove('winnerBox','winnerBet');});
                endAmount.textContent = '';
            }, 2500);
        }

        if (window.Balance && totalPayout > 0) {
            window.Balance.gain(totalPayout, { persist: false });
        }

        document.querySelectorAll('.rouletteToken').forEach(t => t.remove());

        tokenPlaced = false;
        const buttonSpin = document.getElementById('buttonSpin');
        if (buttonSpin) buttonSpin.hidden = true;

        if (window.Balance && typeof window.Balance.ajouterMontantJSON === 'function') {
            window.Balance.ajouterMontantJSON();
        }
    }

    initBettingMat();
    initRoulette();
    const buttonSpin = document.getElementById('buttonSpin');
    const clickRoulette = document.querySelectorAll('.clickRoulette');
    const wageButton = document.querySelectorAll('.tokenWage');
    wageButton[0].classList.add('tokenWageSelected');
    const clearMat = document.getElementById('clearMat');
    let tokenValue = 10;

    if (window.Balance) {
        window.Balance.init({
            session: window.rouletteSession || window.gameSession || {},
            displaySelectors: ['#roulette-balance'],
        });
    }

    buttonSpin.addEventListener('click', () => {
        resultSpin = Math.floor(Math.random() * 37);
        spinRoulette(resultSpin);
    })

    clickRoulette.forEach(betClick => {
        betClick.addEventListener('click', e => {
            const existingToken = betClick.querySelector('.rouletteToken');

            if (!window.Balance.miser(tokenValue, { persist: false })) {
                alert('Solde insuffisant.');
                return;
            }

            if (existingToken) {
                const currentValue = parseInt(existingToken.textContent);
                const newValue = currentValue + tokenValue;
                audio.resume();
                audio.playTokenSound();
                existingToken.textContent = newValue;
            } else {
                const token = document.createElement('div');
                token.classList.add('rouletteToken');
                token.textContent = tokenValue;

                token.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    const currentValue = parseInt(token.textContent);

                    if (currentValue > tokenValue) {
                        token.textContent = currentValue - tokenValue;
                        if (window.Balance) {
                            window.Balance.gain(tokenValue, { persist: false });
                        }
                    } else {
                        if (window.Balance) {
                            window.Balance.gain(currentValue, { persist: false });
                        }
                        token.remove();
                    }

                    tokenPlaced = document.querySelector('.rouletteToken') !== null;
                    if (!tokenPlaced) {
                        buttonSpin.hidden = true;
                    }
                });

                audio.resume();
                audio.playTokenSound();
                betClick.appendChild(token);
            }

            tokenPlaced = true;
            if (tokenPlaced) {
                buttonSpin.hidden = false;
            }
        });
    });

    wageButton.forEach(wageClick => {
        wageClick.addEventListener('click', () => {
            wageButton.forEach(btn => btn.classList.remove('tokenWageSelected'));
            tokenValue = parseInt(wageClick.dataset.value);
            wageClick.classList.add('tokenWageSelected');
        });
    });

    clearMat.addEventListener('click', () => {
        const tokens = document.querySelectorAll('.rouletteToken');
        let totalRefund = 0;

        tokens.forEach(token => {
            totalRefund += parseInt(token.textContent);
            token.remove();
        });

        if (window.Balance && totalRefund > 0) {
            window.Balance.gain(totalRefund, { persist: false });
        }

        tokenPlaced = false;
        buttonSpin.hidden = true;
    });


})();
