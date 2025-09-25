var Engine = Matter.Engine,
    Render = Matter.Render,
    Runner = Matter.Runner,
    Bodies = Matter.Bodies,
    Composite = Matter.Composite;

var engine = Engine.create();

var render = Render.create({
    element: document.body,
    engine: engine
});

render.canvas.width = 800;
render.canvas.height = 600;

var cercle = Bodies.circle(Math.random(350), 50, 5, density=0.5, force=4, inertia=0.01);
var peg = Bodies.circle(425, 300, 10, { isStatic: true });
var peg2 = Bodies.circle(375, 300, 10, { isStatic: true });
var peg3 = Bodies.circle(325, 300, 10, { isStatic: true });
var peg4 = Bodies.circle(475, 300, 10, { isStatic: true });
var peg8 = Bodies.circle(350, 250, 10, { isStatic: true });
var peg9 = Bodies.circle(400, 250, 10, { isStatic: true });
var peg10 = Bodies.circle(450, 250, 10, { isStatic: true });
var ground = Bodies.rectangle(400, 610, 810, 60, { isStatic: true });
var leftWall = Bodies.rectangle(10, 300, 60, 600, { isStatic: true });
var rightWall = Bodies.rectangle(790, 300, 60, 600, { isStatic: true });
Composite.add(engine.world, [cercle, ground, leftWall, rightWall, peg, peg2, peg3, peg4, peg8, peg9, peg10]);

Render.run(render);

var runner = Runner.create();

Runner.run(runner, engine);