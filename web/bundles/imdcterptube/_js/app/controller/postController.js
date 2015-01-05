define([
    'factory/postFactory',
    'service',
    'service/keyPointService'
], function (PostFactory, Service, KeyPointService) {
    'use strict';

    var Post = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Post.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        this.keyPointService = Service.get('keyPoint');

        this.model.set('keyPoint', new KeyPoint(
            this.model.get('id'),
            this.model.get('startTime'),
            this.model.get('endTime'),
            '', {drawOnTimeLine: this.model.get('isTemporal')}
        ));

        // KeyPointService
        this.bind__onKeyPointEvent = this._onKeyPointEvent.bind(this);

        $tt._instances.push(this);
    };

    Post.TAG = 'Post';

    Post.prototype._onKeyPointEvent = function (e) {
        switch (e.type) {
            case KeyPointService.Event.DURATION:
                this.model.set('keyPoint.videoDuration', e.duration);
                break;
            case KeyPointService.Event.SELECTION_TIMES:
                this.model.set('keyPoint.selection', e.selection);
                break;
            case KeyPointService.Event.HOVER:
            case KeyPointService.Event.CLICK:
                this.model.forceChange();
                break;
            /*case KeyPointService.Event.HOVER:
                this.model.set('keyPoint.isHovering', e.isMouseOver);
                break;
            case KeyPointService.Event.CLICK:
                var prop = 'keyPoint.' + (e.isDblClick ? 'isPlaying' : 'isSeeking');
                this.model.set(prop, true);
                setTimeout(function () {
                    this.model.set(prop, false, false);
                }.bind(this), 100);
                break;*/
        }
    };

    Post.prototype.onViewLoaded = function () {
        this.addKeyPoint();
    };

    Post.prototype.addKeyPoint = function () {
        var keyPoint = this.model.get('keyPoint');
        this.keyPointService.register(keyPoint);
        this.keyPointService.subscribe(keyPoint.id, this.bind__onKeyPointEvent);
        this.keyPointService.dispatch(keyPoint.id, KeyPointService.Event.ADD);
    };

    Post.prototype.hoverKeyPoint = function (args) {
        this.model.set('keyPoint.isHovering', args.isMouseOver);

        this.keyPointService.dispatch(this.model.get('keyPoint.id'), KeyPointService.Event.HOVER, args);
    };

    Post.prototype.clickKeyPoint = function (args) {
        var prop = 'keyPoint.' + (args.isDblClick ? 'isPlaying' : 'isSeeking');
        this.model.set(prop, true);
        setTimeout(function () {
            this.model.set(prop, false, false);
        }.bind(this), 100);

        this.keyPointService.dispatch(this.model.get('keyPoint.id'), KeyPointService.Event.CLICK, args);
    };

    Post.prototype.editKeyPoint = function (args) {
        this.keyPointService.dispatch(this.model.get('keyPoint.id'), KeyPointService.Event.EDIT, args);
    };

    Post.prototype.removeKeyPoint = function () {
        var keyPoint = this.model.get('keyPoint');
        this.keyPointService.dispatch(keyPoint.id, KeyPointService.Event.REMOVE);
        this.keyPointService.unsubscribe(keyPoint.id, this.bind__onKeyPointEvent);
        this.keyPointService.deregister(keyPoint.id);
    };

    Post.prototype.new = function (form) {
        return PostFactory.new(this.model, form)
            .done(function (data) {
                if (data.wasReplied) {
                    window.location.replace(data.redirectUrl);
                }
            });
    };

    Post.prototype.edit = function (form) {
        return PostFactory.edit(this.model, form)
            .done(function (data) {

            });
    };

    Post.prototype.view = function () {
        return PostFactory.view(this.model)
            .done(function (data) {

            });
    };

    Post.prototype.delete = function () {
        return PostFactory.delete(this.model)
            .done(function (data) {
                this.removeKeyPoint();
            }.bind(this));
    };

    return Post;
});
