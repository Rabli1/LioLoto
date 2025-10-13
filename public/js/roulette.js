let rouletteNumbers = [0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5, 24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26];
let redNumbers = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];

function initBettingMat() {
    const bettingMat = document.getElementById('bettingMat');
    const item0 = document.createElement('div');

    let rowCnt = 6;
    let colCnt = 2;
    let leftHorizontal = 400;
    let leftVertical = 480;
    let topVertical = 508;

    item0.textContent = 0;
    item0.id = 'item0';

    bettingMat.appendChild(item0);

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

    for (let i = 2; i < 7; i++) {
        if (i == 2 || i == 4 || i == 6) {
            for (let j = 3; j < 25; j += 2) {
                const item = document.createElement('div');
                item.style.gridRow = i;
                item.style.gridColumn = j;
                item.classList.add('gap', 'clickRoulette');
                bettingMat.appendChild(item);
            }
        }
        else {
            for (let j = 2; j < 25; j += 2) {
                const item = document.createElement('div');
                item.style.gridRow = i;
                item.style.gridColumn = j;
                item.classList.add('gap', 'clickRoulette');
                bettingMat.appendChild(item);
            }
        }
    }

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

initBettingMat();
document.querySelectorAll('.clickRoulette').forEach(gapClick => {
    gapClick.addEventListener('click', e => {
        if (gapClick.querySelector('.rouletteToken')) {
            return;
        }

        const token = document.createElement('div');
        token.classList.add('rouletteToken');
        token.textContent = '10';

        token.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            token.remove();
        });

        gapClick.appendChild(token);
    });
});