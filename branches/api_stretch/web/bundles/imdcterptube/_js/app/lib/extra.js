Function.prototype.extend = (function () {
    /*for (var p in parent)
        this[p] = parent[p];

    for (var p in parent.prototype)
        this.prototype[p] = parent.prototype[p];

    this.prototype.constructor = this;
    this.prototype.parent = parent.prototype;

    return this;*/

    return function (parent) {
        var _self = this;
        Object.keys(parent).forEach(function (key) {
            var value = parent[key];
            if (_.isObject(value)) { //TODO add support for arrays or refactor all classes to use only simple types
                _self[key] = _.clone(value);
            } else {
                _self[key] = value;
            }
        });

        this.prototype = Object.create(parent.prototype);
        this.prototype.constructor = this;
    }
})();
