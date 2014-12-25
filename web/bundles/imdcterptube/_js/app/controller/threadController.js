define([
    'factory/threadFactory',
    'service',
    'service/keyPointService'
], function (ThreadFactory, Service, KeyPointService) {
    'use strict';

    var Thread = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Thread.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        this.keyPointService = Service.get('keyPoint');

        this.model.set('keyPoints', []);

        this.videoSpeed = 0;

        // KeyPointService
        this.bind__onKeyPointEvent = this._onKeyPointEvent.bind(this);

        $tt._instances.push(this);
    };

    Thread.TAG = 'Thread';

    Thread.prototype._onKeyPointEvent = function (e) {
        if (e.type != KeyPointService.Event.TIMELINE)
            return;

        if (e.action == 'add') {
            //if (e.keyPoint.startTime && e.keyPoint.endTime)
                this.model.addKeyPoint(e.keyPoint);
        }

        if (e.action == 'edit') {
            this.model.setKeyPoint(e.keyPoint.id, 'options.drawOnTimeLine', false);
            this.model.setKeyPoint(e.keyPoint.id, 'isEditing', true);
        }

        if (e.action == 'cancel' || e.action == 'remove') {
            this.model.setKeyPoint(e.keyPoint.id, 'isEditing', false);
            this.model.setKeyPoint(e.keyPoint.id, 'options.drawOnTimeLine', true);
        }

        if (e.action == 'remove') {
            this.model.removeKeyPoint(e.keyPoint.id);
        }

        if (e.action == 'click') {
            this.model.setKeyPoint(e.keyPoint.id, 'isSeeking', true);
            this.model.setKeyPoint(e.keyPoint.id, 'isSeeking', false, false);
        }

        if (e.action == 'dblclick') {
            this.model.setKeyPoint(e.keyPoint.id, 'isPlaying', true);
            this.model.setKeyPoint(e.keyPoint.id, 'isPlaying', false, false);
        }

        if (e.action == 'mouseenter') {
            this.model.setKeyPoint(e.keyPoint.id, 'isHovering', true);
        }

        if (e.action == 'mouseleave') {
            this.model.setKeyPoint(e.keyPoint.id, 'isHovering', false);
        }
    };

    Thread.prototype.onViewLoaded = function () {
        this.keyPointService.subscribe('all', this.bind__onKeyPointEvent);
    };

    Thread.prototype.updateKeyPointDuration = function (duration) {
        if (!isNaN(duration)) {
            this.keyPointService.dispatch('all', KeyPointService.Event.DURATION, {duration: duration});
        }
    };

    Thread.prototype.updateKeyPointSelectionTimes = function (selection) {
        this.keyPointService.dispatch('all', KeyPointService.Event.SELECTION_TIMES, {
            selection: {
                startTime: parseFloat(selection.minTime).toFixed(2),
                endTime: parseFloat(selection.maxTime).toFixed(2)
            }
        });
    };

    Thread.prototype.updateKeyPointHover = function (keyPointId, args) {
        this.keyPointService.dispatch(keyPointId, KeyPointService.Event.HOVER, args);
    };

    Thread.prototype.updateKeyPointClick = function (keyPointId) {
        this.keyPointService.dispatch(keyPointId, KeyPointService.Event.CLICK);
    };

    Thread.prototype.adjustVideoSpeed = function () {
        this.videoSpeed = (this.videoSpeed + 1) % 3;
        switch (this.videoSpeed) {
            case 0:
                return {value: 1.0, image: this.options.player.speedImages.normal};
            case 1:
                return {value: 2.0, image: this.options.player.speedImages.fast};
            case 2:
                return {value: 0.5, image: this.options.player.speedImages.slow};
            default:
                return {value: 1.0, image: this.options.player.speedImages.normal};
        }
    };

    Thread.prototype.delete = function () {
        return ThreadFactory.delete(this.model)
            .done(function (data) {
                window.location.assign(data.redirectUrl);
            }.bind(this));
    };

    return Thread;
});
