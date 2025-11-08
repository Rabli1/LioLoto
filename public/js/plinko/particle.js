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

    let ballSoundBase = null;

    function getBallSound() {
        if (!ballSoundBase) {
            const soundUrl = window.soundAssets?.plinko || '../../sounds/plinko.mp3';
            ballSoundBase = new Audio(soundUrl);
            ballSoundBase.preload = 'auto';
            ballSoundBase.volume = 0.5;
            ballSoundBase.load();
        }
        return ballSoundBase;
    }

    function playBallSound() {
        const base = getBallSound();
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
        playBallSound,
        preloadSounds() {
            getBallSound();
        }
    };
}

const audio = createAudioToolkit();

audio.preloadSounds();

function Particle(x, y, r) {
    var options = {
<<<<<<< HEAD
        restitution: 0.5, 
        friction: -0.0001, 
        label: "particle",
        collisionFilter: {
            group: -1 
=======
        restitution: 0.5,
        friction: -0.0001,
        label: "particle",
        collisionFilter: {
            group: -1
>>>>>>> 3c2514f55b98ba367079857abe65897cbd6dd402
        }
    };

    audio.resume();
    audio.playBallSound();

    x += random(-10, 10);
    this.body = Bodies.circle(x, y, r, options);
    this.body.isSettled = false;
    this.r = r;
    World.add(world, this.body);
}

Particle.prototype.isOffScreen = function () {
    var x = this.body.position.x;
    var y = this.body.position.y;
    return (x < -50 || x > width + 50 || y > height + 50);
}

Particle.prototype.show = function () {
    fill(255);
    stroke(255);
    var pos = this.body.position;
    push();
    translate(pos.x, pos.y);
    ellipse(0, 0, this.r * 2);
    pop();
}