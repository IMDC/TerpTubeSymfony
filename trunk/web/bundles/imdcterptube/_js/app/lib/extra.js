Function.prototype.extend = function (parent) {
    /*for (var p in parent)
        this[p] = parent[p];

    for (var p in parent.prototype)
        this.prototype[p] = parent.prototype[p];

    this.prototype.constructor = this;
    this.prototype.parent = parent.prototype;

    return this;*/

    for (var p in parent) {
        this[p] = parent[p];
    }

    this.prototype = Object.create(parent.prototype);
    this.prototype.constructor = this;
};
