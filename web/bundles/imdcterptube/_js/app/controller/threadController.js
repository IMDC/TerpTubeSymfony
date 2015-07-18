define([
    'factory/threadFactory',
    'service',
    'service/keyPointService',
    'service/threadPostService'
], function (ThreadFactory, Service, KeyPointService, ThreadPostService) {
    'use strict';

    var Thread = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Thread.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        this.keyPointService = Service.get('keyPoint');
        this.threadPostService = Service.get('threadPost');

        this.videoSpeed = 0;

        this.bind__onKeyPointEvent = this._onKeyPointEvent.bind(this); // KeyPointService
        this.bind__onThreadPostEvent = this._onThreadPostEvent.bind(this); // ThreadPostService

        this.keyPointService.subscribe('all', this.bind__onKeyPointEvent);
        this.threadPostService.subscribe(this.bind__onThreadPostEvent);

        $tt._instances.push(this);
    };

    Thread.TAG = 'Thread';

    Thread.prototype._onKeyPointEvent = function (e) {
        /*if (!e.keyPoints)
            return; // only 'all' dispatches wanted*/

        switch (e.type) {
            case KeyPointService.Event.ADD:
                //if (e.keyPoint.startTime && e.keyPoint.endTime)
                    this.model.addKeyPoint(e.keyPoint);
                break;
            case KeyPointService.Event.HOVER:
            case KeyPointService.Event.CLICK:
                this.model.forceChangeKeyPoint(e.keyPoint.id);
                break;
            /*case KeyPointService.Event.HOVER:
                this.model.setKeyPointProperty(e.keyPoint.id, 'isHovering', e.isMouseOver);
                break;
            case KeyPointService.Event.CLICK:
                var prop = e.isDblClick ? 'isPlaying' : 'isSeeking';
                this.model.setKeyPointProperty(e.keyPoint.id, prop, true);
                setTimeout(function () {
                    this.model.setKeyPointProperty(e.keyPoint.id, prop, false, false);
                }.bind(this), 100);
                break;*/
            case KeyPointService.Event.EDIT:
                if (!e.cancel) {
                    this.model.setKeyPointProperty(e.keyPoint.id, 'options.drawOnTimeLine', false);
                    this.model.setKeyPointProperty(e.keyPoint.id, 'isEditing', true);
                    break;
                }
                // do not break;
            case KeyPointService.Event.REMOVE:
                this.model.setKeyPointProperty(e.keyPoint.id, 'isEditing', false);
                this.model.setKeyPointProperty(e.keyPoint.id, 'options.drawOnTimeLine', true);
                if (e.type == KeyPointService.Event.REMOVE) {
                    //setTimeout(function () {
                        this.model.removeKeyPoint(e.keyPoint.id);
                    //}.bind(this), 100);
                }
                break;
        }
    };

    Thread.prototype._onThreadPostEvent = function (e) {
        switch (e.type) {
            case ThreadPostService.Event.ADD:
                this.model.addPost(e.post, e.post.get('id') < 0 ? 'new' : 'view');
                break;
            case ThreadPostService.Event.REPLACE:
                this.model.forceChangePost(e.post, e.view);
                break;
            case ThreadPostService.Event.REMOVE:
                this.model.removePost(e.post);
                break;
        }
    };

    Thread.prototype.onViewLoaded = function () {

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

    Thread.prototype.hoverKeyPoint = function (keyPointId, args) {
        this.model.setKeyPointProperty(keyPointId, 'isPlayerHovering', args.isMouseOver);

        this.keyPointService.dispatch(keyPointId, KeyPointService.Event.HOVER, args);
    };

    Thread.prototype.rightClickKeyPoint = function (keyPointId) {
        this.model.setKeyPointProperty(keyPointId, 'isPlayerPlaying', true);
        setTimeout(function () {
            this.model.setKeyPointProperty(keyPointId, 'isPlayerPlaying', false, false);
        }.bind(this), 100);

        this.keyPointService.dispatch(keyPointId, KeyPointService.Event.CLICK, {which: 'right'});
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
            });
    };

    return Thread;
});
