define([
    'underscore'
], function () {
    'use strict';

    var bootstrap = function (model, controller, view, options) {
        if (_.isString(controller))
            controller = require('controller/' + controller + 'Controller');

        if (_.isString(view))
            view = require('views/' + view + 'View');

        var c = new controller(model, options);
        new view(c, options);

        c.onViewLoaded();
    };

    return bootstrap;
});
