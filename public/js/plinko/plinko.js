function Plinko(x, y, r) {
    var options = {
        isStatic: true,
        restitution: 0.5,
        friction: 0,
    };
    this.body = Bodies.circle(x, y, r, options);
    this.r = r;
    World.add(world, this.body);
}

Plinko.prototype.show = function () {
    fill(220, 0, 0);
    stroke(255);
    var pos = this.body.position;
    push();
    translate(pos.x, pos.y);
    ellipse(0, 0, this.r * 2);
    pop();
};

(function () {
    const state = window.plinkoGame = window.plinkoGame || {
        total: 0,
        dropped: 0,
        remaining: 0,
        active: false,
        betPerBall: 0,
        totalBet: 0,
        roundPayout: 0,
        dropInterval: 30,
    };

    function format(value) {
        return Number(value || 0).toLocaleString('fr-FR');
    }

    function updateBetLabel(label, pending, ballCount = 1) {
        if (!label) return;
        if (!pending) {
            label.textContent = 'Aucune mise sélectionnée';
            return;
        }
        const total = pending * ballCount;
        label.textContent = `Mise sélectionnée : ${format(pending)} × ${ballCount} = ${format(total)}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.Balance) {
            window.Balance.init({
                session: window.plinkoSession || window.gameSession || {},
                displaySelectors: ['#plinko-balance'],
            });
        }

        const betTokens = document.querySelectorAll('.betToken');
        if (!betTokens.length) return;

        const selectedBetLabel = document.getElementById('selectedBet');
        const clearBetButton = document.getElementById('clearBet');
        const placeBetButton = document.getElementById('placeBet');
        const ballCountSelect = document.getElementById('plinkosCount');

        let pendingBet = 0;
        let currentBallCount = parseInt(ballCountSelect?.value, 10) || 1;

        ballCountSelect?.addEventListener('change', () => {
            currentBallCount = parseInt(ballCountSelect?.value, 10) || 1;
            updateBetLabel(selectedBetLabel, pendingBet, currentBallCount);
        });

        updateBetLabel(selectedBetLabel, pendingBet, currentBallCount);

        betTokens.forEach((btn) => {
            btn.addEventListener('click', () => {
                const value = parseInt(btn.getAttribute('data-value'), 10);
                pendingBet += value;
                updateBetLabel(selectedBetLabel, pendingBet, currentBallCount);
            });
        });

        clearBetButton?.addEventListener('click', () => {
            if (state.active) return;
            pendingBet = 0;
            updateBetLabel(selectedBetLabel, pendingBet, currentBallCount);
        });

        placeBetButton?.addEventListener('click', () => {
            if (state.active) {
                alert('Une manche est déjà en cours.');
                return;
            }

            if (!pendingBet) {
                alert('Veuillez sélectionner une mise !');
                return;
            }

            const selectedBalls = parseInt(ballCountSelect?.value, 10);
            const totalBalls = selectedBalls > 0 ? selectedBalls : 1;
            const totalBetAmount = pendingBet * totalBalls;

            if (
                !window.Balance ||
                !window.Balance.canMise(totalBetAmount) ||
                !window.Balance.miser(totalBetAmount, { persist: false })
            ) {
                alert('Solde insuffisant.');
                return;
            }

            const statsList = document.getElementById('statsList');
            const totalSpan = document.getElementById('totalGain');
            if (statsList) statsList.innerHTML = '';
            if (totalSpan) totalSpan.textContent = '0';
            totalGain = 0;

            state.total = totalBalls;
            state.remaining = totalBalls;
            state.dropped = 0;
            state.betPerBall = pendingBet;
            state.totalBet = totalBetAmount;
            state.roundPayout = 0;
            state.active = true;

            pendingBet = 0;
            updateBetLabel(selectedBetLabel, pendingBet, currentBallCount);

        });
    });

    window.handlePlinko = function (hit) {
        if (!state.active) return;

        const value = Number(typeof hit === 'object' ? hit.value : hit);
        const mise = state.betPerBall;
        let gain = 0;

        if (value > 0) {
            gain = Math.round(mise * value);
            state.roundPayout += gain;
        }

        onBouleTerminee(mise, value);

        state.remaining = Math.max(0, state.remaining - 1);

        if (!state.remaining) {
            if (window.Balance) {
                if (state.roundPayout > 0) {
                    window.Balance.gain(state.roundPayout, { persist: false });
                }
                window.Balance.ajouterMontantJSON();
            }
            state.active = false;
            state.total = 0;
            state.dropped = 0;
            state.betPerBall = 0;
            state.totalBet = 0;
            state.roundPayout = 0;
        }
    };
    let totalGain = 0;

    function ajouterStat(mise, multiplicateur, gain) {
        const statsList = document.getElementById('statsList');
        const totalSpan = document.getElementById('totalGain');

        const div = document.createElement('div');
        div.classList.add('d-flex', 'justify-content-between', 'mb-1');
        div.innerHTML = `
        <span>${mise} x ${multiplicateur}</span>
        <span> ${gain}</span>
    `;

        statsList.appendChild(div);

        totalGain += gain;
        totalSpan.textContent = totalGain;
    }

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

        let ballCashBase = null;

        function getBallCashSound() {
            if (!ballCashBase) {
                const soundUrl = window.soundAssets?.plinkoCash || '../../sounds/plinkoCash.mp3';
                ballCashBase = new Audio(soundUrl);
                ballCashBase.preload = 'auto';
                ballCashBase.volume = 0.5;
                ballCashBase.load();
            }
            return ballCashBase;
        }

        function playBallCashSound() {
            const base = getBallCashSound();
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

        let ballBadCashBase = null;

        function getBallBadCashSound() {
            if (!ballBadCashBase) {
                const soundUrl = window.soundAssets?.plinkoBadCash || '../../sounds/plinkoBadCash.mp3';
                ballBadCashBase = new Audio(soundUrl);
                ballBadCashBase.preload = 'auto';
                ballBadCashBase.volume = 0.5;
                ballBadCashBase.load();
            }
            return ballBadCashBase;
        }

        function playBallBadCashSound() {
            const base = getBallBadCashSound();
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

        let ballGoodCashBase = null;

        function getBallGoodCashSound() {
            if (!ballGoodCashBase) {
                const soundUrl = window.soundAssets?.plinkoGoodCash || '../../sounds/plinkoGoodCash.mp3';
                ballGoodCashBase = new Audio(soundUrl);
                ballGoodCashBase.preload = 'auto';
                ballGoodCashBase.volume = 0.5;
                ballGoodCashBase.load();
            }
            return ballGoodCashBase;
        }

        function playBallGoodCashSound() {
            const base = getBallGoodCashSound();
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

        let ballBestCashBase = null;

        function getBallBestCashSound() {
            if (!ballBestCashBase) {
                const soundUrl = window.soundAssets?.plinkoBestCash || '../../sounds/plinkoBestCash.mp3';
                ballBestCashBase = new Audio(soundUrl);
                ballBestCashBase.preload = 'auto';
                ballBestCashBase.volume = 0.5;
                ballBestCashBase.load();
            }
            return ballBestCashBase;
        }

        function playBallBestCashSound() {
            const base = getBallBestCashSound();
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
            playBallCashSound,
            playBallBadCashSound,
            playBallGoodCashSound,
            playBallBestCashSound,
            preloadSounds() {
                getBallCashSound();
                getBallBadCashSound();
                getBallGoodCashSound();
                getBallBestCashSound();
            }
        };
    }

    const audio = createAudioToolkit();
    audio.preloadSounds();

    function onBouleTerminee(mise, multiplicateur) {
        if (multiplicateur <= 1) {
            audio.resume();
            audio.playBallBadCashSound();
        }

        else if (multiplicateur > 1 && multiplicateur <= 12) {
            audio.resume();
            audio.playBallCashSound();
        }

        else if (multiplicateur > 5 && multiplicateur <= 90) {
            audio.resume();
            audio.playBallGoodCashSound();
        }

        else{
            audio.resume();
            audio.playBallBestCashSound();
        }


        const gain = mise * multiplicateur;
        ajouterStat(mise, multiplicateur, gain);
    }
})();