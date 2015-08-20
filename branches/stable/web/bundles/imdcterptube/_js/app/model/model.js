define([
    'core/subscriber',
    'extra',
    'underscore'
], function (Subscriber) {
    'use strict';

    var Model = function (data) {
        Subscriber.prototype.constructor.apply(this);

        data = data || {};
        if (!_.isObject(data) || _.isArray(data) || _.isFunction(data)) {
            throw new Error('data must be an object');
        }

        this.data = data;
    };

    Model.extend(Subscriber);

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
            var index = parseInt(key, 10);
            key = _.isNumber(index) && !_.isNaN(index) ? index : key;
            // _.has and _.contains are ?? under phantomjs
            if ((_.isObject(list) && (_.has(list, key) && list.hasOwnProperty(key))) ||
                (_.isArray(list) && (_.contains(list, key) && (list[key] !== undefined)))) {
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
            var index = parseInt(key, 10);
            key = _.isNumber(index) && !_.isNaN(index) ? index : key;
            // _.has and _.contains are ?? under phantomjs
            if ((_.isObject(list) && (!_.has(list, key) && !list.hasOwnProperty(key))) ||
                (_.isArray(list) && (!_.contains(list, key) && (list[key] === undefined))) ||
                (_.isNull(list[key]))) {
                console.log('defining key: ' + key);
                var nextKey = parseInt(path[0], 10); // check the next key to predict type
                list[key] = _.isNumber(nextKey) && !_.isNaN(nextKey) ? [] : {};
            }
            list = list[key];
        }

        list[path.shift()] = value;
    };

    Model.prototype._dispatch = function (event, keyPath, args) {
        args = _.extend(args || {}, {
            keyPath: keyPath || '',
            model: this
        });

        Subscriber.prototype._dispatch.call(this, event, args);
    };

    Model.prototype.get = function (keyPath, defaultValue) {
        var result = this._findKeyPath(this.data, keyPath);
        return typeof result !== 'undefined' ? result : defaultValue;
    };

    Model.prototype.set = function (keyPath, value, doDispatch) {
        doDispatch = typeof doDispatch !== 'undefined' ? doDispatch : true;
        var result = this._findKeyPath(this.data, keyPath);

        this._setKeyPath(this.data, keyPath, value);

        if (doDispatch && result !== value) {
            this._dispatch(Model.Event.CHANGE, keyPath);
        }
    };

    Model.prototype.update = function (data, keyPath) {
        _.each(data, function (value, key, list) {
            var cKeyPath = keyPath ? (keyPath + '.' + key) : key;

            if (_.isObject(value) || _.isArray(value)) {
                console.log('update: ' + cKeyPath);
                this.update(value, cKeyPath);
            } else {
                console.log('set: ' + cKeyPath + ' to:' + value);
                this.set(cKeyPath, value);
            }
        }, this);
    };

    Model.prototype.forceChange = function (keyPath, args) {
        this._dispatch(Model.Event.CHANGE, keyPath, args);
    };

    //TODO move to collection
    Model.prototype.find = function (value, keyPath, collection) {
        return _.findIndex(collection, function (model) {
            return model.get(keyPath) == value;
        });
    };

    return Model;
});
