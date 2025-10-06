var Engine = Matter.Engine,
    World = Matter.World,
    Bodies = Matter.Bodies;

var engine;
var world;
var particles = [];
var plinkos = [];
var bounds = [];
var cols = 11;
var rows = 10;

function setup() {
    let canvas = createCanvas(600, 800); //grace a p5 width = 600 et height = 800. Pas besoin de les calls
    canvas.parent('canvas-container');
    engine = Engine.create();
    world = engine.world;
    world.gravity.y = 2;
    newParticle();
    var spacing = width/cols /1.5;
    for (var j = 0; j < rows * 1.5; j++) {
        for (var i = 0; i < cols * 1.5 + 1; i++) {
            var x = i * spacing;
            if ( j % 2 == 0) {
                x += spacing/2;
            }
            var y = spacing + j * spacing + 30;
            var p = new Plinko(x, y, 6);
            plinkos.push(p);
        }
    }

    var b = new Boundary(width/2, height + 50, width, 100); //le sol
    bounds.push(b);

    for ( var i = 0; i < cols + 2; i++) { //les murs verticaux
        var x = i * spacing;
        var h = 70;
        var w = 10;
        var y = height - h/2;
        var b = new Boundary(x * 1.5, y, w, h);
        bounds.push(b);
    }
}

function newParticle() {
    var p = new Particle(300, 50, 10);
    particles.push(p);
}

function draw() {
        if (frameCount % 60 == 0) {
        newParticle();
    }
    background(51);
    Engine.update(engine);
    for (var i = 0; i < particles.length; i++) {
        particles[i].show();
        if (particles[i].isOffScreen()) {
            World.remove(world, particles[i].body);
            particles.splice(i, 1);
            i--;
        }
    }
    for (var j = 0; j < plinkos.length; j++) {
        plinkos[j].show();
    }

    for (var k = 0; k < bounds.length; k++) {
        bounds[k].show();
    }
}