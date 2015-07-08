define([
    'core/subscriber'
], function (Subscriber) {
    'use strict';

    var ThreadPostService = function () {
        Subscriber.prototype.constructor.apply(this);

        this.name = 'ThreadPost';
    };

    ThreadPostService.extend(Subscriber);

    ThreadPostService.Event = {
        ADD: 'eventAdd'
    };

    ThreadPostService.prototype.dispatch = function (event, args) {
        Subscriber.prototype._dispatch.call(this, event, args);
    };

    return ThreadPostService;
});
