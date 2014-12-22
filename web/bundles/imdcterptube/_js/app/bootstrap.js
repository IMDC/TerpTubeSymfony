define(function () {
    'use strict';

    var bootstrap = function (model, controller, view, options) {
        var c = new controller(model, options);
        new view(c, options);

        c.onViewLoaded();
    };

    return bootstrap;
});
