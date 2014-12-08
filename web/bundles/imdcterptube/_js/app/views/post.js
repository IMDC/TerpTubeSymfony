define([
    'controller/post_',
    'core/mediaChooser'
], function(Post, MediaChooser) {
    "use strict";

    var PostView = function(model, options) {
        var ctrl = new Post(model, this, options);

        this.$container = $(PostView.Binder.CONTAINER + "[data-pid='" + model.id + "']");
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

        this.getFormField = function(fieldName) {
            return this.$form.find("#" + PostView.FORM_NAME + "_" + fieldName);
        };

        this.$timelineKeyPoint.on("click", ctrl.bind__onClickHoverTimelineKeyPoint);
        this.$timelineKeyPoint.on("dblclick", ctrl.bind__onClickHoverTimelineKeyPoint);
        this.$timelineKeyPoint.hover(
            ctrl.bind__onClickHoverTimelineKeyPoint,
            ctrl.bind__onClickHoverTimelineKeyPoint);
        this.$new.on("click", ctrl.bind__onClickNew);
        this.$edit.on("click", ctrl.bind__onClickEdit);
        this.$delete.on("click", ctrl.bind__onClickDelete);
        this.$submitNew.on("click", ctrl.bind__onClickSubmitNew);
        this.$submitEdit.on("click", ctrl.bind__onClickSubmitEdit);
        this.$reset.on("click", ctrl.bind__onClickReset);
        this.$cancelNew.on("click", ctrl.bind__onClickCancelNew);
        this.$cancelEdit.on("click", ctrl.bind__onClickCancelEdit);

        this.mediaChooser = new MediaChooser({enableDoneAndPost: true});
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, ctrl.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS_AND_POST, ctrl.bind__onSuccessAndPost);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, ctrl.bind__onReset);
        this.mediaChooser.setContainer(this.$form);
        this.mediaChooser.bindUIEvents();

        ctrl.onViewLoaded();
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

    return PostView;
});
