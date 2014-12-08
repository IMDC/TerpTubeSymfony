define([
    'core/postManager'
], function(PostManager) {
    "use strict";

    var Post = function(model, view, options) {
        console.log("%s: %s- model=%o, view=%o, options=%o", Post.TAG, "constructor", model, view, options);

        this.model = model;
        this.view = view;
        this.options = options;

        this.keyPoint = null;

        this.bind__onClickHoverTimelineKeyPoint = this._onClickHoverTimelineKeyPoint.bind(this);
        this.bind__onClickNew = this._onClickNew.bind(this);
        this.bind__onClickEdit = this._onClickEdit.bind(this);
        this.bind__onClickDelete = this._onClickDelete.bind(this);
        this.bind__onClickSubmitNew = this._onClickSubmitNew.bind(this);
        this.bind__onClickSubmitEdit = this._onClickSubmitEdit.bind(this);
        this.bind__onClickReset = this._onClickReset.bind(this);
        this.bind__onClickCancelNew = this._onClickCancelNew.bind(this);
        this.bind__onClickCancelEdit = this._onClickCancelEdit.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onSuccessAndPost = this._onSuccessAndPost.bind(this);
        this.bind__onReset = this._onReset.bind(this);

        this.bind__renderTimelineKeyPoint = this._renderTimelineKeyPoint.bind(this);
        this.bind__onSelectionTimes = this._onSelectionTimes.bind(this);
        this.bind__onHoverKeyPoint = this._onHoverKeyPoint.bind(this);
        this.bind__onClickKeyPoint = this._onClickKeyPoint.bind(this);

        $tt._instances.push(this);
    };

    Post.TAG = "Post";

    Post.Event = {
        TIMELINE_KEYPOINT: "eventTimelineKeyPoint"
    };

    Post.prototype._toggleForm = function(disabled) {
        this.view.$submitNew.button(disabled ? "loading" : "reset");
        this.view.$submitEdit.button(disabled ? "loading" : "reset");
        this.view.$reset.attr("disabled", disabled);
        this.view.$cancelNew.attr("disabled", disabled);
        this.view.$cancelEdit.attr("disabled", disabled);
    };

    Post.prototype._renderTimelineKeyPoint = function(e) {
        console.log("%s: %s", Post.TAG, "_renderTimelineKeyPoint");

        var startTimePercentage = ((100 * this.keyPoint.startTime) / e.duration).toFixed(2);
        var endTimePercentage = ((100 * this.keyPoint.endTime) / e.duration).toFixed(2);
        var widthPercentage = (endTimePercentage - startTimePercentage).toFixed(2);

        this.view.$timelineKeyPoint.css({
            left: startTimePercentage + "%",
            width: widthPercentage + "%"
        });
    };

    Post.prototype._onSelectionTimes = function(e) {
        this.view.getFormField("startTime").val(e.selection.startTime);
        this.view.getFormField("endTime").val(e.selection.endTime);
    };

    Post.prototype._onHoverKeyPoint = function(e) {
        if (e.keyPoint.id != this.keyPoint.id)
            return;

        this.view.$container.toggleClass("tt-post-container-highlight");
    };

    Post.prototype._onClickKeyPoint = function(e) {
        if (e.keyPoint.id != this.keyPoint.id)
            return;

        var video = this.view.$container.find("video")[0];
        if (video) {
            video.currentTime = 0;
            video.play();
        }
    };

    Post.prototype.onViewLoaded = function() {
        var mediaIds = [];
        this.view.getFormField("attachedFile").children().each(function(index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this._toggleForm(true);
            this.view.mediaChooser.setMedia(mediaIds);
        }

        $(document).on("eventDuration", this.bind__renderTimelineKeyPoint);
        $(document).on("eventSelectionTimes", this.bind__onSelectionTimes);
        $(document).on("eventKeyPointHover", this.bind__onHoverKeyPoint);
        $(document).on("eventKeyPointClick", this.bind__onClickKeyPoint);

        this.keyPoint = new KeyPoint(
            this.model.id,
            this.model.startTime,
            this.model.endTime,
            "", {
                drawOnTimeLine: this.model.isTemporal
            }
        );

        $(document).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: "add",
            keyPoint: this.keyPoint
        }));
    };

    // clicking the clock icon will move the density bar to the comments time
    // and highlight the comment on the density bar
    // mousing over the clock icon should highlight the comment on the density bar
    Post.prototype._onClickHoverTimelineKeyPoint = function(e) {
        if (e && e.type == "click")
            e.preventDefault();

        $(document).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: e.type,
            keyPoint: this.keyPoint
        }));
    };

    Post.prototype._onClickNew = function(e) {
        e.preventDefault();

        PostManager.new(this.model, this.view.$form[0]);
    };

    Post.prototype._onClickEdit = function(e) {
        e.preventDefault();

        PostManager.edit(this.model, this.view.$form[0])
            .then(function(data, textStatus, jqXHR) {
                this.view.$container.replaceWith(data.html);
                this.view = new $tt.Views.Post(post);

                $(document).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
                    action: "edit",
                    keyPoint: this.keyPoint
                }));
            }.bind(this));
    };

    Post.prototype._onClickDelete = function(e) {
        e.preventDefault();

        this.view.$delete.button("loading");
        PostManager.delete(this.model)
            .then(function(data, textStatus, jqXHR) {
                if (!data.wasDeleted) {
                    this.view.$deleteModal
                        .find(".modal-body")
                        .prepend("Something went wrong. Try again.");
                    return;
                }

                this.view.$deleteModal
                    .find(".modal-body")
                    .html("Post deleted successfully.");

                if (this.view.$deleteModal.data('bs.modal').isShown) {
                    this.view.$deleteModal.on("hidden.bs.modal", function(e) {
                        this.view.$container.remove();
                    });
                    this.view.$deleteModal.modal("hide");
                    this.view.$container.fadeOut("slow");
                } else {
                    this.view.$container.fadeOut("slow", function(e) {
                        this.view.$container.remove();
                    });
                }

                $(document).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
                    action: "remove",
                    keyPoint: this.keyPoint
                }));
            }.bind(this),
            function(jqXHR, textStatus, errorThrown) {
                this.view.$container
                    .find(".modal-body")
                    .prepend("Something went wrong. Try again.");
                this.view.$delete.button("reset");
            }.bind(this));
    };

    Post.prototype._preSubmit = function() {
        if (this.view.getFormField("content").val() == "" && this.view.mediaChooser.media.length == 0) {
            alert("Your post cannot be blank. You must either select a file or write a comment.");
            return;
        }

        this._toggleForm(true);
    };

    Post.prototype._onClickSubmitNew = function(e) {
        e.preventDefault();

        this._preSubmit();
        PostManager.new(this.model, this.view.$form[0])
            .then(function(data, textStatus, jqXHR) {
                if (data.wasReplied) {
                    window.location.replace(data.redirectUrl);
                } else {
                    this.view.$container.replaceWith(data.html);
                    this.view = new $tt.Views.Post(post);
                }
            }.bind(this),
            function(jqXHR, textStatus, errorThrown) {
                this._toggleForm(false);
            }.bind(this));
    };

    Post.prototype._onClickSubmitEdit = function(e) {
        e.preventDefault();

        this._preSubmit();
        PostManager.edit(this.model, this.view.$form[0])
            .then(function(data, textStatus, jqXHR) {
                this.view.$container.replaceWith(data.html);
                this.view = new $tt.Views.Post(post);

                if (data.wasEdited) {
                    $(document).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
                        action: "cancel",
                        keyPoint: this.keyPoint
                    }));
                }
            }.bind(this),
            function(jqXHR, textStatus, errorThrown) {
                this._toggleForm(false);
            }.bind(this));
    };

    Post.prototype._onClickReset = function(e) {
        e.preventDefault();

        this.view.mediaChooser.reset();
        this.view.getFormField("startTime").val(this.keyPoint.startTime);
        this.view.getFormField("endTime").val(this.keyPoint.endTime);
        this.view.getFormField("content").val("");

        $(document).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: "edit",
            keyPoint: this.keyPoint
        }));
    };

    Post.prototype._onClickCancelNew = function(e) {
        e.preventDefault();

        this.view.$container.remove();

        $(document).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
            action: "remove",
            keyPoint: this.keyPoint
        }));
    };

    Post.prototype._onClickCancelEdit = function(e) {
        e.preventDefault();

        PostManager.view(this.model)
            .then(function(data, textStatus, jqXHR) {
                this.view.$container.replaceWith(data.html);
                this.view = new $tt.Views.Post(post);

                $(document).trigger($.Event(Post.Event.TIMELINE_KEYPOINT, {
                    action: "cancel",
                    keyPoint: this.keyPoint
                }));
            }.bind(this));
    };

    Post.prototype._updateForm = function() {
        var formField = this.view.getFormField("attachedFile");
        formField.html(
            this.view.mediaChooser.generateFormData(
                formField.data("prototype")
            )
        );
    };

    Post.prototype._onSuccess = function(e) {
        this._updateForm();
        this._toggleForm(false);
    };

    Post.prototype._onSuccessAndPost = function(e) {
        this._updateForm();
        this.view.$submit.trigger("click");
    };

    Post.prototype._onReset = function(e) {
        this._updateForm();
    };

    return Post;
});
