define(['underscore'], function () {
    'use strict';

    var KeyPointService = function () {
        this.name = 'KeyPoint';
        this.keyPoints = [];
        this.subscriptions = [];
    };

    KeyPointService.Event = {
        ADD: 'eventAdd',
        DURATION: 'eventDuration',
        SELECTION_TIMES: 'eventSelectionTimes',
        HOVER: 'eventHover',
        CLICK: 'eventClick',
        EDIT: 'eventEdit',
        REMOVE: 'eventRemove'
    };

    KeyPointService._kIndex = function (keyPointId) {
        return _.isString(keyPointId) ? keyPointId : 'K' + keyPointId;
    };

    KeyPointService.prototype.register = function (keyPoint) {
        var kIndex = KeyPointService._kIndex(keyPoint.id);
        if (_.isObject(keyPoint))
            this.keyPoints[kIndex] = keyPoint;
    };

    KeyPointService.prototype.deregister = function (keyPointId) {
        var kIndex = KeyPointService._kIndex(keyPointId);
        if (_.has(this.keyPoints, kIndex)) {
            this.keyPoints.splice(kIndex, 1);
        }
    };

    KeyPointService.prototype.subscribe = function (keyPointId, callback) {
        var kIndex = KeyPointService._kIndex(keyPointId);
        if (!_.has(this.subscriptions, kIndex)) {
            this.subscriptions[kIndex] = [];
        }

        if (!_.contains(this.subscriptions[kIndex], callback)) {
            this.subscriptions[kIndex].push(callback);
        }
    };

    KeyPointService.prototype.unsubscribe = function (keyPointId, callback) {
        var kIndex = KeyPointService._kIndex(keyPointId);
        if (_.has(this.subscriptions, kIndex)) {
            var callbacks = this.subscriptions[kIndex];
            if (_.contains(callbacks, callback)) {
                var index = _.indexOf(callbacks, callback);
                callbacks.splice(index, 1);
            }
        }
    };

    KeyPointService.prototype.dispatch = function (keyPointId, event, args) {
        var kIndex = KeyPointService._kIndex(keyPointId);

        if ((kIndex !== 'all' && !_.has(this.keyPoints, kIndex))
            || !_.contains(KeyPointService.Event, event))
            return;

        var subscriptions = kIndex === 'all' ? _.values(this.subscriptions) : [this.subscriptions[kIndex]];

        var invoke = function (element, index, list) {
            var e = {
                type: event
            };

            if (kIndex === 'all') {
                e.keyPoints = this.keyPoints;
            } else {
                e.keyPoint = this.keyPoints[kIndex];
            }

            if (_.isObject(args)) {
                // add the extra args for this event to the event object
                _.each(args, function (value, key, list) {
                    e[key] = value;
                });
            }

            // loop through the callbacks
            _.each(element, function (element2, index, list) {
                if (_.isFunction(element2))
                    element2.call(this, e);
            }, this);
        };

        _.each(subscriptions, invoke, this);

        if (kIndex !== 'all') {
            _.each([this.subscriptions['all']], invoke, this);
        }
    };

    return KeyPointService;
});
