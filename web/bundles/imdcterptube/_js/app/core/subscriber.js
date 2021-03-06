define(['underscore'], function () {
    'use strict';

    var Subscriber = function () {
        this.subscriptions = {};
    };

    Subscriber.prototype._dispatch = function (event, args) {
        var e = {
            type: event
        };

        if (_.isObject(args) && !_.isArray(args) && !_.isFunction(args)) {
            // add the extra args for this event to the event object
            _.each(args, function (value, key, list) {
                e[key] = value;
            });
        }

        // loop through the callbacks
        _.each(this.subscriptions[event], function (element, index, list) {
            if (_.isFunction(element))
                element.call(this, e);
        }, this);
    };

    Subscriber.prototype.subscribe = function (event, callback) {
        if (!_.has(this.subscriptions, event)) {
            this.subscriptions[event] = [];
        }

        if (!_.contains(this.subscriptions[event], callback)) {
            this.subscriptions[event].push(callback);
        }
    };

    Subscriber.prototype.unsubscribe = function (callback) {
        _.each(this.subscriptions, function (callbacks, key, list) {
            if (_.contains(callbacks, callback)) {
                var cbIndex = _.indexOf(callbacks, callback);
                callbacks.splice(cbIndex, 1);
            }
        });
    };

    return Subscriber;
});
