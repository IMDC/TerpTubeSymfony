define([
    'model/model',
    'core/mediaChooser',
    'service/keyPointService'
], function(Model, MediaChooser, KeyPointService) {
    "use strict";

    var PostView = function(controller, options) {
        this.controller = controller;

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
        this.bind__onModelChange = this._onModelChange.bind(this);

        // KeyPointService
        //this.bind__onKeyPointEvent = this._onKeyPointEvent.bind(this);
        /*this.bind__renderTimelineKeyPoint = this._renderTimelineKeyPoint.bind(this);
        this.bind__onSelectionTimes = this._onSelectionTimes.bind(this);
        this.bind__onHoverKeyPoint = this._onHoverKeyPoint.bind(this);
        this.bind__onClickKeyPoint = this._onClickKeyPoint.bind(this);*/

        this.$container = $(PostView.Binder.CONTAINER + "[data-pid='" + this.controller.model.get('id') + "']");
        this.$form = this.$container.find("form[name^=" + PostView.FORM_NAME + "]");
        this.$timelineKeyPoint = this.$container.find(PostView.Binder.TIMELINE_KEYPOINT);
        this.$new = this.$container.find(PostView.Binder.NEW);
        this.$edit = this.$container.find(PostView.Binder.EDIT);
        this.$deleteModal = this.$container.find(PostView.Binder.DELETE_MODAL);
        this.$delete = this.$container.find(PostView.Binder.DELETE);
        this.$submitNew = this.$container.find(PostView.Binder.SUBMIT_NEW);
        this.$submitEdit = this.$container.find(PostView.Binder.SUBMIT_EDIT);
        this.$reset = this.$container.find(PostView.Binder.RESET);
        this.$cancelNew = this.$container.find(PostView.Binder.CANCEL_NEW);
        this.$cancelEdit = this.$container.find(PostView.Binder.CANCEL_EDIT);

        this.$timelineKeyPoint.on("click", this.bind__onClickHoverTimelineKeyPoint);
        this.$timelineKeyPoint.on("dblclick", this.bind__onClickHoverTimelineKeyPoint);
        this.$timelineKeyPoint.hover(
            this.bind__onClickHoverTimelineKeyPoint,
            this.bind__onClickHoverTimelineKeyPoint);
        this.$new.on("click", this.bind__onClickNew);
        this.$edit.on("click", this.bind__onClickEdit);
        this.$delete.on("click", this.bind__onClickDelete);
        this.$submitNew.on("click", this.bind__onClickSubmitNew);
        this.$submitEdit.on("click", this.bind__onClickSubmitEdit);
        this.$reset.on("click", this.bind__onClickReset);
        this.$cancelNew.on("click", this.bind__onClickCancelNew);
        this.$cancelEdit.on("click", this.bind__onClickCancelEdit);

        this.mediaChooser = new MediaChooser({enableDoneAndPost: true});
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS_AND_POST, this.bind__onSuccessAndPost);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.setContainer(this.$form);
        this.mediaChooser.bindUIEvents();

        var mediaIds = [];
        this._getFormField("attachedFile").children().each(function(index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this._toggleForm(true);
            this.mediaChooser.setMedia(mediaIds);
        }

        // KeyPointService
        /*this.keyPointService = Service.get('keyPoint');
        $(this.keyPointService).on("eventDuration", this.bind__renderTimelineKeyPoint);
        $(this.keyPointService).on("eventSelectionTimes", this.bind__onSelectionTimes);
        $(this.keyPointService).on("eventKeyPointHover", this.bind__onHoverKeyPoint);
        $(this.keyPointService).on("eventKeyPointClick", this.bind__onClickKeyPoint);*/

        this.controller.model.subscribe(Model.Event.CHANGE, this.bind__onModelChange);
    };

    PostView.TAG = "PostView";

    PostView.Binder = {
        CONTAINER: ".post-container",

        TIMELINE_KEYPOINT: ".post-timeline-keypoint",
        NEW: ".post-new",
        EDIT: ".post-edit",
        DELETE_MODAL: ".post-delete-modal",
        DELETE: ".post-delete",
        SUBMIT_NEW: ".post-submit-new",
        SUBMIT_EDIT: ".post-submit-edit",
        RESET: ".post-reset",
        CANCEL_NEW: ".post-cancel-new",
        CANCEL_EDIT: ".post-cancel-edit"
    };

    // this must be the same name defined in {bundle}/Form/Type/PostType
    PostView.FORM_NAME = "post";

    PostView.prototype._getFormField = function(fieldName) {
        return this.$form.find("#" + PostView.FORM_NAME + "_" + fieldName);
    };

    // clicking the clock icon will move the density bar to the comments time
    // and highlight the comment on the density bar
    // mousing over the clock icon should highlight the comment on the density bar
    PostView.prototype._onClickHoverTimelineKeyPoint = function(e) {
        if (e && e.type == "click")
            e.preventDefault();

        this.controller.interactKeyPoint(e.type);
    };

    PostView.prototype._onClickNew = function(e) {
        e.preventDefault();

        this.$new.hide();
        this.controller.new(null)
            .done(function(data) {
                //this.$container.replaceWith(data.html);
                //var _self = this;
                //_self = new PostView(this.controller.model, this.controller.options);
                this.$container.after(data.html);
            }.bind(this))
            .fail(function() {
                this.$new.show();
            }.bind(this));
    };

    PostView.prototype._onClickEdit = function(e) {
        e.preventDefault();

        this.controller.edit(null)
            .done(function(data) {
                this.$container.replaceWith(data.html);
                var _self = this;
                _self = new PostView(this.controller.model, this.controller.options);
            }.bind(this));
    };

    PostView.prototype._onClickDelete = function(e) {
        e.preventDefault();

        this.view.$delete.button("loading");
        this.controller.delete()
            .done(function(data) {
                this.$deleteModal
                    .find(".modal-body")
                    .html("Post deleted successfully.");

                if (this.$deleteModal.data('bs.modal').isShown) {
                    this.$deleteModal.on("hidden.bs.modal", function(e) {
                        this.$container.remove();
                    });
                    this.$deleteModal.modal("hide");
                    this.$container.fadeOut("slow");
                } else {
                    this.$container.fadeOut("slow", function(e) {
                        this.$container.remove();
                    });
                }
            }.bind(this))
            .fail(function() {
                this.$container
                    .find(".modal-body")
                    .prepend("Something went wrong. Try again.");
                this.$delete.button("reset");
            }.bind(this));
    };

    PostView.prototype._toggleForm = function(disabled) {
        this.$submitNew.button(disabled ? "loading" : "reset");
        this.$submitEdit.button(disabled ? "loading" : "reset");
        this.$reset.attr("disabled", disabled);
        this.$cancelNew.attr("disabled", disabled);
        this.$cancelEdit.attr("disabled", disabled);
    };

    PostView.prototype._preSubmit = function() {
        if (this._getFormField("content").val() == "" && this.mediaChooser.media.length == 0) {
            alert("Your post cannot be blank. You must either select a file or write a comment.");
            return false;
        }
        this._toggleForm(true);
        return true;
    };

    PostView.prototype._onClickSubmitNew = function(e) {
        e.preventDefault();

        if (!this._preSubmit())
            return;

        this.controller.new(this.$form[0])
            .done(function(data) {
                if (!data.wasReplied) {
                    this.$container.replaceWith(data.html);
                    var _self = this;
                    _self = new PostView(this.controller.model, this.controller.options);
                }
            }.bind(this))
            .fail(function() {
                this._toggleForm(false);
            }.bind(this));
    };

    PostView.prototype._onClickSubmitEdit = function(e) {
        e.preventDefault();

        if (!this._preSubmit())
            return;

        this.controller.edit(this.$form[0])
            .done(function(data) {
                this.$container.replaceWith(data.html);
                var _self = this;
                _self = new PostView(this.controller.model, this.controller.options);
            }.bind(this))
            .fail(function() {
                this._toggleForm(false);
            }.bind(this));
    };

    PostView.prototype._onClickReset = function(e) {
        e.preventDefault();

        this.mediaChooser.reset();
        this._getFormField("startTime").val(this.controller.model.get('keyPoint.startTime'));
        this._getFormField("endTime").val(this.controller.model.get('keyPoint.endTime'));
        this._getFormField("content").val("");

        this.controller.editKeyPoint();
    };

    PostView.prototype._onClickCancelNew = function(e) {
        e.preventDefault();

        this.$container.remove();
        this.controller.removeKeyPoint();

        $(PostView.Binder.CONTAINER + "[data-pid='" + this.controller.model.get('parentPostId') + "']")
            .find(PostView.Binder.NEW)
            .show();
    };

    PostView.prototype._onClickCancelEdit = function(e) {
        e.preventDefault();

        this.controller.view()
            .done(function(data) {
                this.$container.replaceWith(data.html);
                var _self = this;
                _self = new PostView(this.controller.model, this.controller.options);
            }.bind(this));
    };

    PostView.prototype._updateForm = function() {
        var formField = this._getFormField("attachedFile");
        formField.html(
            this.mediaChooser.generateFormData(
                formField.data("prototype")
            )
        );
    };

    PostView.prototype._onSuccess = function(e) {
        this._updateForm();
        this._toggleForm(false);
    };

    PostView.prototype._onSuccessAndPost = function(e) {
        this._updateForm();
        this.$submit.trigger("click");
    };

    PostView.prototype._onReset = function(e) {
        this._updateForm();
    };

    PostView.prototype._onModelChange = function(e) {
        this._renderTimelineKeyPoint(
            e.model.get('keyPoint.startTime', ''),
            e.model.get('keyPoint.endTime', ''),
            e.model.get('keyPoint.videoDuration', '')
        );
        this._onSelectionTimes(
            e.model.get('keyPoint.selection.startTime', ''),
            e.model.get('keyPoint.selection.endTime', '')
        );
        this._onHoverKeyPoint(
            e.model.get('keyPoint.isHovering', false)
        );
        this._onClickKeyPoint(
            e.model.get('keyPoint.isPlaying', false)
        );
    };

    PostView.prototype._renderTimelineKeyPoint = function(startTime, endTime, videoDuration) {
        console.log("%s: %s", PostView.TAG, "_renderTimelineKeyPoint");

        var startTimePercentage = ((100 * startTime) / videoDuration).toFixed(2);
        var endTimePercentage = ((100 * endTime) / videoDuration).toFixed(2);
        var widthPercentage = (endTimePercentage - startTimePercentage).toFixed(2);

        this.$timelineKeyPoint.css({
            left: startTimePercentage + "%",
            width: widthPercentage + "%"
        });
    };

    PostView.prototype._onSelectionTimes = function(startTime, endTime) {
        this._getFormField("startTime").val(startTime);
        this._getFormField("endTime").val(endTime);
    };

    PostView.prototype._onHoverKeyPoint = function(isHovering) {
        if (isHovering) {
            this.$container.addClass("tt-post-container-highlight");
        } else {
            this.$container.removeClass("tt-post-container-highlight");
        }
    };

    PostView.prototype._onClickKeyPoint = function(isPlaying) {
        var video = this.$container.find("video")[0];
        if (video && isPlaying) {
            video.currentTime = 0;
            video.play();
        }
    };

    return PostView;
});
