let rouletteNumbers = [0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5, 24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26];
let redNumbers = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];
let tokenPlaced = false;

function initBettingMat() {
    const bettingMat = document.getElementById('bettingMat');
    const item0 = document.createElement('div');
    let rowCnt = 6;
    let colCnt = 2;

    item0.textContent = 0;
    item0.id = 'item0';

    bettingMat.appendChild(item0);

    //Génère les cases numéro
    for (let i = 1; i < 37; i++) {
        const item = document.createElement('div');
        item.textContent = i;
        item.id = `item${i}`;
        item.classList.add('clickRoulette');

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
        bettingMat.appendChild(rightSideItem);
    }

    //Génère les cases au top
    for (let i = 2; i < 19; i = i + 8) {
        const topItem = document.createElement('div');
        if (i == 2) {
            topItem.textContent = '1 à 12';
        }
        else if (i == 10) {
            topItem.textContent = '13 à 24';
        }
        else if (i == 18) {
            topItem.textContent = '25 à 36';
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
        }
        else if (i == 6) {
            bottomItem.textContent = 'Pair';
        }
        else if (i == 10) {
            bottomItem.textContent = 'Rouge';
            bottomItem.style.backgroundColor = 'rgba(211, 47, 47, 0.5)';
        }
        else if (i == 14) {
            bottomItem.textContent = 'Noir';
            bottomItem.style.backgroundColor = 'rgba(255, 255, 255, 0.06)';
        }
        else if (i == 18) {
            bottomItem.textContent = 'Impair';
        }
        else {
            bottomItem.textContent = '19 à 36';
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
    }, 10000);

}

initBettingMat();
initRoulette();

const clickRoulette = document.querySelectorAll('.clickRoulette');
const buttonSpin = document.getElementById('buttonSpin');
let bet = 10;

buttonSpin.addEventListener('click', () => {
    spinRoulette(Math.floor(Math.random() * 37));
})

clickRoulette.forEach(gapClick => {
    gapClick.addEventListener('click', e => {
        const token = document.createElement('div');
        token.classList.add('rouletteToken');
        token.textContent = `${bet}`;
        
        if (gapClick.querySelector('.rouletteToken')) {
            bet += bet;
            token.textContent = `${bet}`;
        }

        token.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            token.remove();
            bet = 10;
            tokenPlaced = document.querySelector('.rouletteToken') !== null;
            if (!tokenPlaced) {
                buttonSpin.hidden = true;
            }
        });

        tokenPlaced = true;
        gapClick.appendChild(token);
        if (tokenPlaced) {
            buttonSpin.hidden = false;
        }
    });
});