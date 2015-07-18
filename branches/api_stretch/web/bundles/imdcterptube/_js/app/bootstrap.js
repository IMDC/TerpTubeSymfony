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
        var v = new view(c, options);

        if (_.isFunction(v.loadView)) //TODO implement in all views
            v.loadView();
        c.onViewLoaded();
    };

    return bootstrap;
});
