define([
    'controller/thread_',
    'core/mediaChooser'
], function(Thread, MediaChooser) {
    "use strict";

    var ThreadView = function(model, options) {
        this.controller = new Thread(model, options);

        this.bind__onClickSubmit = this._onClickSubmit.bind(this);
        this.bind__onClickDelete = this._onClickDelete.bind(this);
        this.bind__onClickVideoSpeed = this._onClickVideoSpeed.bind(this);
        this.bind__onClickClosedCaptions = this._onClickClosedCaptions.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);

        this.$container = options.container;
        this.$form = this.$container.find("form[name^=" + ThreadView.FORM_NAME + "]");
        this.$submit = this.$container.find(ThreadView.Binder.SUBMIT);
        this.$deleteModal = this.$container.find(ThreadView.Binder.DELETE_MODAL);
        this.$delete = this.$container.find(ThreadView.Binder.DELETE);
        /*for (var binder in ThreadView.Binder) {
            this["$" + binder] = this.$container.find(ThreadView.Binder[binder]);
        }*/

        this.$submit.on("click", this.bind__onClickSubmit);
        this.$delete.on("click", this.bind__onClickDelete);

        $("#videoSpeed").on("click", this.bind__onClickVideoSpeed);
        $("#closedCaptions").on("click", this.bind__onClickClosedCaptions);

        this.mediaChooser = new MediaChooser();
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.setContainer(this.$form);
        this.mediaChooser.bindUIEvents();

        var mediaIds = [];
        this._getFormField("mediaIncluded").children().each(function(index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this.$submit.attr("disabled", true);
            this.mediaChooser.setMedia(mediaIds);
        }

        this.controller.onViewLoaded();
    };

    ThreadView.TAG = "ThreadView";

    ThreadView.Binder = {
        SUBMIT: ".thread-submit",
        DELETE_MODAL: ".thread-delete-modal",
        DELETE: ".thread-delete"
    };

    // this must be the same name defined in {bundle}/Form/Type/ThreadType
    ThreadView.FORM_NAME = "thread";

    ThreadView.prototype._getFormField = function(fieldName) {
        return this.$form.find("#" + ThreadView.FORM_NAME + "_" + fieldName);
    };

    ThreadView.prototype._onClickSubmit = function(e) {
        if (this.$form[0].checkValidity()) {
            $(e.target).button("loading");
        }
    };

    ThreadView.prototype._onClickDelete = function(e) {
        e.preventDefault();

        this.$delete.button("loading");
        this.controller.delete()
            .done(function(data) {
                this.$deleteModal
                    .find(".modal-body")
                    .html("Topic deleted successfully.");
            }.bind(this))
            .fail(function() {
                this.$container
                    .find(".modal-body")
                    .prepend("Something went wrong. Try again.");
                this.$delete.button("reset");
            }.bind(this));
    };

    // change the video speed when the slowdown button is clicked
    ThreadView.prototype._onClickVideoSpeed = function(e) {
        e.preventDefault();

        var speedImage = this.controller.adjustVideoSpeed();
        $("#videoSpeed img").attr("src", speedImage);
    };

    // change the captioning display when you click the captioning button
    ThreadView.prototype._onClickClosedCaptions = function(e) {
        e.preventDefault();

        var captionImage = this.controller.toggleClosedCaptions();
        $("#closed-caption-button img").attr("src", captionImage);
    };

    ThreadView.prototype._updateForm = function() {
        var formField = this._getFormField("mediaIncluded");
        formField.html(
            this.mediaChooser.generateFormData(
                formField.data("prototype")
            )
        );
    };

    ThreadView.prototype._onSuccess = function(e) {
        this._updateForm();
        this.$submit.attr("disabled", false);
        if (this.mediaChooser.media.length > 0) {
            this._getFormField("title")
                .attr("required", false)
                .parent()
                .find("label")
                .removeClass("required");
        }
    };

    ThreadView.prototype._onReset = function(e) {
        this._updateForm();
        if (this.mediaChooser.media.length == 0) {
            this._getFormField("title")
                .attr("required", true)
                .parent()
                .find("label")
                .addClass("required");
        }
    };

    return ThreadView;
});
