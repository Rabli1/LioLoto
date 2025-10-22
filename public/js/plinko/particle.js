function Particle(x, y, r) {
    var options = {
        restitution: 0.5,  // rebonds
        friction: -0.0001, // friction
        label: "particle",
        collisionFilter: {
            group: -1 // 🟩 les particules du même groupe ne se collisionnent pas entre elles
        }
    };

    x += random(-10, 10);
    this.body = Bodies.circle(x, y, r, options);
    this.body.isSettled = false;
    this.r = r;
    World.add(world, this.body);
}

Particle.prototype.isOffScreen = function() {
    var x = this.body.position.x;
    var y = this.body.position.y;
    return (x < -50 || x > width + 50 || y > height + 50);
}

Particle.prototype.show = function() {
    fill(255);
    stroke(255);
    var pos = this.body.position;
    push();
    translate(pos.x, pos.y);
    ellipse(0, 0, this.r * 2);
    pop();
}
