function Boundary(x, y, w, h, color) {
    var options = {
        isStatic: true,
    };
    this.body = Bodies.rectangle(x, y, w, h, options);
    this.w = w;
    this.h = h;
    this.color = color || [255, 255, 255]; 
    World.add(world, this.body);
}

Boundary.prototype.show = function() {
    fill(this.color[0], this.color[1], this.color[2]);
    noStroke();
    var pos = this.body.position;
    push();
    translate(pos.x, pos.y);
    rectMode(CENTER);
    rect(0, 0, this.w, this.h, 5);
    pop();
};

function getColorForMultiplier(multiplier) {
    var value = parseFloat(multiplier);
    
    if (value < 0.5) {
        return [255, 255, 150];
    } else if (value < 1) {
        return [255, 255, 0];
    } else if (value < 2) {
        return [255, 200, 0];
    } else if (value < 4) {
        return [255, 165, 0];
    } else if (value < 7) {
        return [255, 100, 0];
    } else if (value < 15) {
        return [255, 69, 0];
    } else if (value < 50) {
        return [255, 0, 0];
    } else if (value < 150) {
        return [200, 0, 0];
    } else {
        return [175, 0, 0];
    }
}