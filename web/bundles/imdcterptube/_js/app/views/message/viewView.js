define([], function () {
    'use strict';

    var ViewView = function (controller, options) {
        this.controller = controller;

        this.$container = options.container;

        //TODO gallery

        $tt._instances.push(this);
    };

    ViewView.TAG = 'MessageViewView';

    return ViewView;
});
