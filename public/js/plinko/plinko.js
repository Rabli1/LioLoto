function Plinko(x,y,r) {
    var options = {
        isStatic: true,
        restitution: 0.5, //les rebonds
        friction: 0 //friction
    }
    this.body = Bodies.circle(x, y, r, options);
    this.r = r;
    World.add(world, this.body);
}

Plinko.prototype.show = function() {
    fill(0,204,0);
    stroke(255);
    var pos = this.body.position;
    push();
    translate (pos.x, pos.y);
    ellipse(0, 0, this.r * 2);
    pop();
}