var Engine = Matter.Engine,
    World = Matter.World,
    Bodies = Matter.Bodies,
    Events = Matter.Events; // 🟩 pour écouter les collisions

var engine;
var world;
var particles = [];
var plinkos = [];
var bounds = [];
var cubes = []; // 🟩 liste des cubes du bas
var cols = 19;
var rows = 16;

function setup() {
    let canvas = createCanvas(600, 625);
    canvas.parent('canvas-container');
    engine = Engine.create();
    world = engine.world;
    world.gravity.y = 1;

    var spacing = width / cols;
    var centerX = width / 2;
    var minPlinkos = 3;

    // --- triangle inversé ---
    for (var j = 0; j < rows; j++) {
        var numPlinkos = minPlinkos + j;
        if (numPlinkos > cols) numPlinkos = cols;

        var y = spacing + j * spacing + 30;
        var totalWidth = (numPlinkos - 1) * spacing;

        for (var i = 0; i < numPlinkos; i++) {
            var x = centerX - totalWidth / 2 + i * spacing;
            var p = new Plinko(x, y, 6);
            plinkos.push(p);
        }
    }

    // --- cubes du bas ---
    var numCubes = 17;
    var cubeSize = 25;
    var margin = 30;
    var spacing = (width - margin * 2) / numCubes;
    var startX = margin + spacing / 2;
    var y = height - cubeSize / 2 - 50;

    // 🟩 multiplicateurs symétriques
    var multipliers = [
        "500x", "80x", "25x", "10x", "4x", "1x", "0.5x",
        "0.2x", "0.2x", "0.2x", // centre (3x 0.2x total)
        "0.5x", "1x", "4x", "10x", "25x", "80x", "500x"
    ];

    for (var i = 0; i < numCubes; i++) {
        var x = startX + i * spacing;
        var cube = new Boundary(x, y, cubeSize, cubeSize);
        cube.body.label = "cube";
        cube.multiplier = multipliers[i];
        bounds.push(cube);
        cubes.push(cube);
    }

    // 🟩 Détection de collisions
    Events.on(engine, "collisionStart", function(event) {
        var pairs = event.pairs;
        for (var i = 0; i < pairs.length; i++) {
            var bodyA = pairs[i].bodyA;
            var bodyB = pairs[i].bodyB;

            // on vérifie si c’est une particule et un cube
            if (isParticleAndCube(bodyA, bodyB)) {
                removeParticle(bodyA.label === "particle" ? bodyA : bodyB);
            }
        }
    });
}

// 🟩 fonction utilitaire pour détecter particule-cube
function isParticleAndCube(a, b) {
    return (
        (a.label === "particle" && b.label === "cube") ||
        (a.label === "cube" && b.label === "particle")
    );
}

// 🟩 supprime la particule du monde et du tableau
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
    background(51);
    Engine.update(engine);

    // --- Drop automatique des billes si le round est en cours ---
    if (particlesDropped < totalParticles && frameCount % dropInterval === 0) {
        newParticle();
        particlesDropped++;
    }

    // --- Affichage des particules ---
    for (var i = 0; i < particles.length; i++) {
        particles[i].show();
        if (particles[i].isOffScreen()) {
            World.remove(world, particles[i].body);
            particles.splice(i, 1);
            i--;
        }
    }

    // --- Affichage des plinkos ---
    for (var j = 0; j < plinkos.length; j++) {
        plinkos[j].show();
    }

    // --- Affichage des cubes ---
    for (var k = 0; k < bounds.length; k++) {
        bounds[k].show();
        fill(0);
        textAlign(CENTER, CENTER);
        textSize(k === 0 || k === bounds.length - 1 ? 11 : 13);
        let pos = bounds[k].body.position;
        text(bounds[k].multiplier, pos.x, pos.y);
    }
}
