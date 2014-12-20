define(['underscore'], function () {
    'use strict';

    var Model = function (data) {
        data = data || {};
        if (!_.isObject(data)) {
            throw 'data must be an object';
        }

        this.data = data;
        this.subscriptions = [];
    };

    Model.Event = {
        CHANGE: 'eventChange'
    };

    Model._stringToKeyPath = function (str) {
        return str.split('.');
    };

    Model.prototype._findKeyPath = function (list, keyPath) {
        var path = Model._stringToKeyPath(keyPath);

        while (path.length !== 0) {
            var key = path.shift();
            if (_.has(list, key)) {
                list = list[key];
            } else {
                return undefined;
            }
        }

        return list;
    };

    Model.prototype._setKeyPath = function (list, keyPath, value) {
        var path = Model._stringToKeyPath(keyPath);

        while (path.length > 1) {
            var key = path.shift();
            if (_.has(list, key)) {
                list = list[key];
            } else {
                list[key] = {};
            }
        }

        list[path.shift()] = value;
    };

    Model.prototype._dispatch = function (event, args) {
        var e = {
            type: event,
            model: this
        };

        if (_.isObject(args)) {
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

    Model.prototype.get = function (keyPath, defaultValue) {
        var result = this._findKeyPath(this.data, keyPath);
        return typeof result !== 'undefined' ? result : defaultValue;
    };

    Model.prototype.set = function (keyPath, value, doDispatch) {
        var doDispatch = typeof doDispatch !== 'undefined' ? doDispatch : true;
        var result = this._findKeyPath(this.data, keyPath);

        this._setKeyPath(this.data, keyPath, value);

        if (doDispatch && result !== value) {
            this._dispatch(Model.Event.CHANGE);
        }
    };

    Model.prototype.subscribe = function (event, callback) {
        if (!_.contains(this.subscriptions, event)) {
            this.subscriptions[event] = [];
        }

        if (!_.contains(this.subscriptions[event], callback)) {
            this.subscriptions[event].push(callback);
        }
    };

    Model.prototype.unsubscribe = function (callback) {
        _.each(this.subscriptions, function (callbacks, index, list) {
            if (_.contains(callbacks, callback)) {
                var index = _.indexOf(callbacks, callback);
                callbacks.splice(index, 1);
            }
        });
    };

    return Model;
});
