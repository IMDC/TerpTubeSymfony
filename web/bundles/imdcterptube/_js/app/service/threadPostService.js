define([
    'core/subscriber',
    'underscore'
], function (Subscriber) {
    'use strict';

    var ThreadPostService = function () {
        Subscriber.prototype.constructor.apply(this);

        this.name = 'ThreadPost';
    };

    ThreadPostService.extend(Subscriber);

    ThreadPostService.Event = {
        ADD: 'eventAdd',
        REPLACE: 'eventUpdate',
        REMOVE: 'eventRemove'
    };

    ThreadPostService.prototype.subscribe = function (callback) {
        _.each(ThreadPostService.Event, function (value, key, list) {
            Subscriber.prototype.subscribe.call(this, value, callback);
        }.bind(this));
    };

    ThreadPostService.prototype.dispatch = function (event, args) {
        Subscriber.prototype._dispatch.call(this, event, args);
    };

    return ThreadPostService;
});
