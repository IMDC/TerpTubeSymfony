define(function () {
    'use strict';

    var Home = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Home.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    Home.TAG = 'Home';

    Home.prototype.onViewLoaded = function () {

    };

    return Home;
});
