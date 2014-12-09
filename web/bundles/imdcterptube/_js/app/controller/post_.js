define([
    'core/postManager'
], function(PostManager) {
    "use strict";

    var Post = function(model, options) {
        console.log("%s: %s- model=%o, options=%o", Post.TAG, "constructor", model, options);

        this.model = model;
        this.options = options;

        this.keyPointService = $tt._services['keyPoint'];

        this.keyPoint = new KeyPoint(
            this.model.id,
            this.model.startTime,
            this.model.endTime,
            "", {drawOnTimeLine: this.model.isTemporal}
        );

        $tt._instances.push(this);
    };

    Post.TAG = "Post";

    Post.Event = {
        TIMELINE_KEYPOINT: "eventTimelineKeyPoint"
    };

    Post.prototype.onViewLoaded = function() {
        $(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: this.options.editing ? "edit" : "add",
            keyPoint: this.keyPoint
        }));
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
                    $(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
                        action: "cancel",
                        keyPoint: this.keyPoint
                    }));
                    this.options.editing = false;
                }
                this.options.editing = true;
            }.bind(this));
    };

    Post.prototype.delete = function() {
        return PostManager.delete(this.model)
            .done(function(data) {
                $(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
                    action: "remove",
                    keyPoint: this.keyPoint
                }));
            }.bind(this));
    };

    Post.prototype.interactKeyPoint = function(eventType) {
        $(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: eventType,
            keyPoint: this.keyPoint
        }));
    };

    Post.prototype.editKeyPoint = function() {
        $(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: "edit",
            keyPoint: this.keyPoint
        }));
    };

    Post.prototype.removeKeyPoint = function() {
        $(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: "remove",
            keyPoint: this.keyPoint
        }));
    };

    Post.prototype.view = function() {
        this.options.editing = false;

        return PostManager.view(this.model)
            .done(function(data) {
                $(this.keyPointService).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
                    action: "cancel",
                    keyPoint: this.keyPoint
                }));
            }.bind(this));
    };

    return Post;
});
