define([
    'core/postManager',
    'service',
    'service/keyPointService'
], function(PostManager, Service, KeyPointService) {
    "use strict";

    var Post = function(model, options) {
        console.log("%s: %s- model=%o, options=%o", Post.TAG, "constructor", model, options);

        this.model = model;
        this.options = options;

        this.keyPointService = Service.get('keyPoint');

        this.keyPoint = new KeyPoint( //TODO put this inside the model
            this.model.id,
            this.model.startTime,
            this.model.endTime,
            "", {drawOnTimeLine: this.model.isTemporal}
        );

        // KeyPointService
        //this.bind__onKeyPointEvent = this._onKeyPointEvent.bind(this);

        $tt._instances.push(this);
    };

    Post.TAG = "Post";

    Post.Event = {
        TIMELINE_KEYPOINT: "eventTimelineKeyPoint"
    };

    Post.prototype.onViewLoaded = function(view) { //FIXME controller should not be aware of view
        /*$(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: this.options.editing ? "edit" : "add",
            keyPoint: this.keyPoint
        }));*/
        this.keyPointService.register(this.keyPoint);
        this.keyPointService.subscribe(this.keyPoint.id, view.bind__onKeyPointEvent);
        this.keyPointService.dispatch(this.keyPoint.id, KeyPointService.Event.TIMELINE, {
            action: this.options.editing ? "edit" : "add"
        });
    };

    Post.prototype.new = function(form) {
        return PostManager.new(this.model, form)
            .done(function(data) {
                if (data.wasReplied) {
                    window.location.replace(data.redirectUrl);
                }
            }.bind(this));
    };

    Post.prototype.edit = function(form) {
        return PostManager.edit(this.model, form)
            .done(function(data) {
                if (data.wasEdited) {
                    /*$(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
                        action: "cancel",
                        keyPoint: this.keyPoint
                    }));*/
                    this.keyPointService.dispatch(this.keyPoint.id, KeyPointService.Event.TIMELINE, {action: 'cancel'});
                    this.options.editing = false;
                }
                this.options.editing = true;
            }.bind(this));
    };

    Post.prototype.delete = function() {
        return PostManager.delete(this.model)
            .done(function(data) {
                /*$(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
                    action: "remove",
                    keyPoint: this.keyPoint
                }));*/
                this.keyPointService.dispatch(this.keyPoint.id, KeyPointService.Event.TIMELINE, {action: 'remove'});
            }.bind(this));
    };

    Post.prototype.interactKeyPoint = function(eventType) {
        /*$(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: eventType,
            keyPoint: this.keyPoint
        }));*/
        this.keyPointService.dispatch(this.keyPoint.id, KeyPointService.Event.TIMELINE, {action: eventType});
    };

    Post.prototype.editKeyPoint = function() {
        /*$(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: "edit",
            keyPoint: this.keyPoint
        }));*/
        this.keyPointService.dispatch(this.keyPoint.id, KeyPointService.Event.TIMELINE, {action: 'edit'});
    };

    Post.prototype.removeKeyPoint = function() {
        /*$(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: "remove",
            keyPoint: this.keyPoint
        }));*/
        this.keyPointService.dispatch(this.keyPoint.id, KeyPointService.Event.TIMELINE, {action: 'remove'});
    };

    Post.prototype.view = function() {
        this.options.editing = false;

        return PostManager.view(this.model)
            .done(function(data) {
                /*$(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
                    action: "cancel",
                    keyPoint: this.keyPoint
                }));*/
                this.keyPointService.dispatch(this.keyPoint.id, KeyPointService.Event.TIMELINE, {action: 'cancel'});
            }.bind(this));
    };

    return Post;
});
