define([
    'controller/thread_',
    'core/mediaChooser'
], function(Thread, MediaChooser) {
    "use strict";

    var ThreadView = function(model, options) {
        var ctrl = new Thread(model, this, options);

        this.$container = options.container;
        this.$form = this.$container.find("form[name^=" + ThreadView.FORM_NAME + "]");
        this.$submit = this.$container.find(ThreadView.Binder.SUBMIT);
        this.$deleteModal = this.$container.find(ThreadView.Binder.DELETE_MODAL);
        this.$delete = this.$container.find(ThreadView.Binder.DELETE);
        /*for (var binder in ThreadView.Binder) {
            this["$" + binder] = this.$container.find(ThreadView.Binder[binder]);
        }*/

        this.getFormField = function(fieldName) {
            return this.$form.find("#" + ThreadView.FORM_NAME + "_" + fieldName);
        };

        this.$submit.on("click", ctrl.bind__onClickSubmit);
        this.$delete.on("click", ctrl.bind__onClickDelete);

        $("#videoSpeed").on("click", ctrl.bind__onClickVideoSpeed);
        $("#closedCaptions").on("click", ctrl.bind__onClickClosedCaptions);

        this.mediaChooser = new MediaChooser();
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, ctrl.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, ctrl.bind__onReset);
        this.mediaChooser.setContainer(this.$form);
        this.mediaChooser.bindUIEvents();

        ctrl.onViewLoaded();
    };

    ThreadView.TAG = "ThreadView";

    ThreadView.Binder = {
        SUBMIT: ".thread-submit",
        DELETE_MODAL: ".thread-delete-modal",
        DELETE: ".thread-delete"
    };

    // this must be the same name defined in {bundle}/Form/Type/ThreadType
    ThreadView.FORM_NAME = "thread";

    return ThreadView;
});
