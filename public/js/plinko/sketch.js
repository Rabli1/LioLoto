var Engine = Matter.Engine,
    World = Matter.World,
    Bodies = Matter.Bodies,
    Events = Matter.Events;

var engine;
var world;
var particles = [];
var plinkos = [];
var bounds = [];
var cubes = [];
var cols = 19;
var rows = 16;

function gameState() {
    if (!window.plinkoGame) {
        window.plinkoGame = {
            total: 0,
            dropped: 0,
            remaining: 0,
            active: false,
            betPerBall: 0,
            dropInterval: 30,
        };
    }
    return window.plinkoGame;
}

function setup() {
    let canvas = createCanvas(600, 625);
    canvas.parent('canvas-container');
    engine = Engine.create();
    world = engine.world;
    world.gravity.y = 1;

    var spacing = width / cols;
    var centerX = width / 2;
    var minPlinkos = 3;

    for (var j = 0; j < rows; j++) {
        var numPlinkos = minPlinkos + j;
        if (numPlinkos > cols) numPlinkos = cols;

        var y = spacing + j * spacing + 30;
        var totalWidth = (numPlinkos - 1) * spacing;

        for (var i = 0; i < numPlinkos; i++) {
            var x = centerX - totalWidth / 2 + i * spacing;
            var p = new Plinko(x, y, 6
            );
            plinkos.push(p);
        }
    }

    var numCubes = 17;
    var cubeSize = 25;
    var margin = 30;
    var spacing = (width - margin * 2) / numCubes;
    var startX = margin + spacing / 2;
    var y = height - cubeSize / 2 - 50;

    var multipliers = [
        "250x", "99x", "41x", "10x", "5x", "3x", "1.5x",
        "0.4x", "0.3x", "0.4x",
        "1.5x", "3x", "5x", "10x", "41x", "99x", "250x"
    ];

     for (var k = 0; k < numCubes; k++) {
        var cubeX = startX + k * spacing;
        var color = getColorForMultiplier(multipliers[k]);
        var cube = new Boundary(cubeX, y, cubeSize, cubeSize, color);
        cube.body.label = "cube";
        cube.multiplier = multipliers[k];
        cube.body.plinkoMultiplierValue = parseFloat(multipliers[k]);
        cube.body.plinkoMultiplierLabel = multipliers[k];
        bounds.push(cube);
        cubes.push(cube);
    }

    Events.on(engine, "collisionStart", function (event) {
        var pairs = event.pairs;
        for (var i = 0; i < pairs.length; i++) {
            var bodyA = pairs[i].bodyA;
            var bodyB = pairs[i].bodyB;

            if (isParticleAndCube(bodyA, bodyB)) {
                var particleBody = bodyA.label === "particle" ? bodyA : bodyB;
                var cubeBody = bodyA.label === "cube" ? bodyA : bodyB;

                if (!particleBody.isSettled && typeof window.handlePlinko === 'function') {
                    window.handlePlinko({
                        value: cubeBody.plinkoMultiplierValue,
                        label: cubeBody.plinkoMultiplierLabel
                    });
                }

                particleBody.isSettled = true;
                removeParticle(particleBody);
            }
        }
    });
}

function isParticleAndCube(a, b) {
    return (
        (a.label === "particle" && b.label === "cube") ||
        (a.label === "cube" && b.label === "particle")
    );
}

function removeParticle(body) {
    for (var i = 0; i < particles.length; i++) {
        if (particles[i].body === body) {
            World.remove(world, body);
            particles.splice(i, 1);
            break;
        }
    }
}

function newParticle() {
    var p = new Particle(300, 50, 10);
    particles.push(p);
}

function draw() {
    background(40);
    Engine.update(engine);

    const state = gameState();
    if (state.active && state.dropped < state.total && frameCount % state.dropInterval === 0) {
        newParticle();
        state.dropped += 1;
    }

    for (var i = 0; i < particles.length; i++) {
        var particle = particles[i];
        particle.show();
        if (particle.isOffScreen()) {
            if (!particle.body.isSettled && typeof window.handlePlinko === 'function') {
                window.handlePlinko({ value: 0, label: '0x' });
            }
            particle.body.isSettled = true;
            World.remove(world, particle.body);
            particles.splice(i, 1);
            i--;
        }
    }

    for (var j = 0; j < plinkos.length; j++) {
        plinkos[j].show();
    }

    for (var k = 0; k < bounds.length; k++) {
        bounds[k].show();
        noStroke();
        fill(0);
        textAlign(CENTER, CENTER);
        textSize(k === 0 || k === bounds.length - 1 ? 11 : 13);
        var pos = bounds[k].body.position;
        text(bounds[k].multiplier, pos.x, pos.y);
    }
}
