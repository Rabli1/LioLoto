let rouletteNumbers = [0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5, 24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26];
let redNumbers = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];

function initBettingMat() {
    const bettingMat = document.getElementById('bettingMat');
    const item0 = document.createElement('div');

    let rowCnt = 4;
    let colCnt = 2;

    item0.textContent = 0;
    item0.id = 'item0';

    bettingMat.appendChild(item0);

    for (let i = 1; i < 37; i++) {
        const item = document.createElement('div');
        item.textContent = i;
        item.id = `item${i}`;

        if (rowCnt < 2) {
            rowCnt = 4;
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
            colCnt++;
        }
        rowCnt--;
        bettingMat.appendChild(item);
    }

    for (let i = 2; i < 5; i++) {
        const rightSideItem = document.createElement('div');
        rightSideItem.textContent = '2:1';
        rightSideItem.classList.add('rightSideItem');
        rightSideItem.style.gridRow = i;
        bettingMat.appendChild(rightSideItem);
    }

    for (let i = 2; i < 11; i = i + 4) {
        const topItem = document.createElement('div');
        if (i == 2) {
            topItem.textContent = '1 à 12';
        }
        else if (i == 6) {
            topItem.textContent = '13 à 24';
        }
        else {
            topItem.textContent = '25 à 36';
        }
        topItem.classList.add('topItem');
        topItem.style.gridColumn = `${i}/${i + 4}`;
        bettingMat.appendChild(topItem);
    }

    for (let i = 2; i < 13; i = i + 2) {
        const bottomItem = document.createElement('div');
        if (i == 2) {
            bottomItem.textContent = '1 à 18';
        }
        else if (i == 4) {
            bottomItem.textContent = 'Pair';
        }
        else if (i == 6){
            bottomItem.textContent = 'Rouge';
            bottomItem.style.backgroundColor = 'rgba(211, 47, 47, 0.5)';
        }
        else if(i == 8){
            bottomItem.textContent = 'Noir';
            bottomItem.style.backgroundColor = 'rgba(255, 255, 255, 0.06)';
        }
        else if (i == 10) {
            bottomItem.textContent = 'Impair';
        }
        else{
            bottomItem.textContent = '19 à 36';
        }
        bottomItem.classList.add('bottomItem');
        bottomItem.style.gridColumn = `${i}/${i + 2}`;
        bettingMat.appendChild(bottomItem);
    }
    
}

initBettingMat();