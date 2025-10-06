var Engine = Matter.Engine,
    World = Matter.World,
    Bodies = Matter.Bodies;

var engine;
var world;
var particles = [];

function setup() {
    let canvas = createCanvas(600, 400);
    canvas.parent('canvas-container');
    engine = Engine.create();
    world = engine.world;

    var p = new Particle(300, 50, 10);
    particles.push(p);

}

function draw() {
    background(51);
    particles[0].show();
}