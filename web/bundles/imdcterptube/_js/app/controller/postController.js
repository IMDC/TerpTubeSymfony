define([
    'factory/postFactory',
    'service',
    'service/keyPointService'
], function(PostFactory, Service, KeyPointService) {
    "use strict";

    var Post = function(model, options) {
        console.log("%s: %s- model=%o, options=%o", Post.TAG, "constructor", model, options);

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

    Post.TAG = "Post";

    Post.Event = {
        TIMELINE_KEYPOINT: "eventTimelineKeyPoint"
    };

    Post.prototype._onKeyPointEvent = function(e) {
        switch (e.type) {
            case KeyPointService.Event.DURATION:
                this.model.set('keyPoint.videoDuration', e.duration);
                //this._renderTimelineKeyPoint(e);
                break;
            case KeyPointService.Event.SELECTION_TIMES:
                this.model.set('keyPoint.selection', e.selection);
                //this._onSelectionTimes(e);
                break;
            case KeyPointService.Event.HOVER:
                this.model.set('keyPoint.isHovering', e.isMouseOver);
                //this._onHoverKeyPoint(e);
                break;
            case KeyPointService.Event.CLICK:
                this.model.set('keyPoint.isPlaying', true);
                setTimeout(function () {
                    this.model.set('keyPoint.isPlaying', false);
                }.bind(this));
                //this._onClickKeyPoint(e);
                break;
        }
    };

    Post.prototype.onViewLoaded = function(/*view*/) {
        /*$(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: this.options.editing ? "edit" : "add",
            keyPoint: this.keyPoint
        }));*/
        var keyPoint = this.model.get('keyPoint');
        this.keyPointService.register(keyPoint);
        //this.keyPointService.subscribe(this.keyPoint.id, view.bind__onKeyPointEvent);
        this.keyPointService.subscribe(keyPoint.id, this.bind__onKeyPointEvent);
        this.keyPointService.dispatch(keyPoint.id, KeyPointService.Event.TIMELINE, {
            action: this.options.editing ? "edit" : "add"
        });
    };

    Post.prototype.new = function(form) {
        return PostFactory.new(this.model, form)
            .done(function(data) {
                if (data.wasReplied) {
                    window.location.replace(data.redirectUrl);
                }
            }.bind(this));
    };

    Post.prototype.edit = function(form) {
        return PostFactory.edit(this.model, form)
            .done(function(data) {
                if (data.wasEdited) {
                    /*$(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
                        action: "cancel",
                        keyPoint: this.keyPoint
                    }));*/
                    this.keyPointService.dispatch(this.model.get('keyPoint').id, KeyPointService.Event.TIMELINE, {action: 'cancel'});
                    this.options.editing = false;
                }
                this.options.editing = true;
            }.bind(this));
    };

    Post.prototype.delete = function() {
        return PostFactory.delete(this.model)
            .done(function(data) {
                /*$(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
                    action: "remove",
                    keyPoint: this.keyPoint
                }));*/
                this.removeKeyPoint();
            }.bind(this));
    };

    Post.prototype.interactKeyPoint = function(eventType) {
        /*$(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: eventType,
            keyPoint: this.keyPoint
        }));*/
        this.keyPointService.dispatch(this.model.get('keyPoint').id, KeyPointService.Event.TIMELINE, {action: eventType});
    };

    Post.prototype.editKeyPoint = function() {
        /*$(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: "edit",
            keyPoint: this.keyPoint
        }));*/
        this.keyPointService.dispatch(this.model.get('keyPoint').id, KeyPointService.Event.TIMELINE, {action: 'edit'});
    };

    Post.prototype.removeKeyPoint = function() {
        /*$(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: "remove",
            keyPoint: this.keyPoint
        }));*/
        var keyPoint = this.model.get('keyPoint');
        this.keyPointService.dispatch(keyPoint.id, KeyPointService.Event.TIMELINE, {action: 'remove'});
        this.keyPointService.deregister(keyPoint.id);
    };

    Post.prototype.view = function() {
        this.options.editing = false;

        return PostFactory.view(this.model)
            .done(function(data) {
                /*$(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
                    action: "cancel",
                    keyPoint: this.keyPoint
                }));*/
                this.keyPointService.dispatch(this.model.get('keyPoint').id, KeyPointService.Event.TIMELINE, {action: 'cancel'});
            }.bind(this));
    };

    return Post;
});
