define([
    'model/model',
    'core/mediaChooser'
], function (Model, MediaChooser) {
    'use strict';

    var NewView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickSubmitNew = this._onClickSubmitNew.bind(this);
        this.bind__onClickReset = this._onClickReset.bind(this);
        this.bind__onClickCancelNew = this._onClickCancelNew.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onSuccessAndPost = this._onSuccessAndPost.bind(this);
        this.bind__onReset = this._onReset.bind(this);
        this.bind__onModelChange = this._onModelChange.bind(this);

        this.$container = $(NewView.Binder.CONTAINER + '[data-pid="' + this.controller.model.get('id') + '"]');
        this.$form = this.$container.find('form[name^=' + NewView.FORM_NAME + ']');
        this.$submitNew = this.$container.find(NewView.Binder.SUBMIT_NEW);
        this.$reset = this.$container.find(NewView.Binder.RESET);
        this.$cancelNew = this.$container.find(NewView.Binder.CANCEL_NEW);

        this.$submitNew.on('click', this.bind__onClickSubmitNew);
        this.$reset.on('click', this.bind__onClickReset);
        this.$cancelNew.on('click', this.bind__onClickCancelNew);

        this.mediaChooser = new MediaChooser({enableDoneAndPost: true});
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS_AND_POST, this.bind__onSuccessAndPost);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.setContainer(this.$form);
        this.mediaChooser.bindUIEvents();

        var mediaIds = [];
        this._getFormField('attachedFile').children().each(function (index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this._toggleForm(true);
            this.mediaChooser.setMedia(mediaIds);
        }

        this.controller.model.subscribe(Model.Event.CHANGE, this.bind__onModelChange);

        $tt._instances.push(this);
    };

    NewView.TAG = 'PostNewView';

    NewView.Binder = {
        CONTAINER: '.post-container',
        SUBMIT_NEW: '.post-submit-new',
        RESET: '.post-reset',
        CANCEL_NEW: '.post-cancel-new'
    };

    // this must be the same name defined in {bundle}/Form/Type/PostType
    NewView.FORM_NAME = 'post';

    NewView.prototype._getFormField = function (fieldName) {
        return this.$form.find('#' + NewView.FORM_NAME + '_' + fieldName);
    };

    NewView.prototype._toggleForm = function (disabled) {
        this.$submitNew.button(disabled ? 'loading' : 'reset');
        this.$reset.attr('disabled', disabled);
        this.$cancelNew.attr('disabled', disabled);
    };

    NewView.prototype._preSubmit = function () {
        if (this._getFormField('content').val() == '' && this.mediaChooser.media.length == 0) {
            alert('Your post cannot be blank. You must either select a file or write a comment.');
            return false;
        }
        this._toggleForm(true);
        return true;
    };

    NewView.prototype._onClickSubmitNew = function (e) {
        e.preventDefault();

        if (!this._preSubmit())
            return;

        this.controller.new(this.$form[0])
            .done(function (data) {
                if (!data.wasReplied) {
                    this.$container.replaceWith(data.html);
                    this.controller.removeKeyPoint();
                    var _self = this;
                    _self = new NewView(this.controller, this.controller.options);
                    this.controller.onViewLoaded();
                }
            }.bind(this))
            .fail(function () {
                this._toggleForm(false);
            }.bind(this));
    };

    NewView.prototype._onClickReset = function (e) {
        e.preventDefault();

        this.mediaChooser.reset();
        this._getFormField('startTime').val(this.controller.model.get('keyPoint.startTime'));
        this._getFormField('endTime').val(this.controller.model.get('keyPoint.endTime'));
        this._getFormField('content').val('');

        this.controller.editKeyPoint({cancel: true});
    };

    NewView.prototype._onClickCancelNew = function (e) {
        e.preventDefault();

        this.$container.remove();
        this.controller.removeKeyPoint();

        $(NewView.Binder.CONTAINER + '[data-pid="' + this.controller.model.get('parent_post.id') + '"]')
            .find(NewView.Binder.NEW)
            .show();
    };

    NewView.prototype._onSelectionTimes = function (startTime, endTime) {
        this._getFormField('startTime').val(startTime);
        this._getFormField('endTime').val(endTime);
    };

    NewView.prototype._onModelChange = function (e) {
        this._onSelectionTimes(
            e.model.get('keyPoint.selection.startTime', ''),
            e.model.get('keyPoint.selection.endTime', '')
        );
    };

    NewView.prototype._updateForm = function () {
        var formField = this._getFormField('attachedFile');
        formField.html(
            this.mediaChooser.generateFormData(
                formField.data('prototype')
            )
        );
    };

    NewView.prototype._onSuccess = function (e) {
        this._updateForm();
        this._toggleForm(false);
    };

    NewView.prototype._onSuccessAndPost = function (e) {
        this._updateForm();
        this.$submit.trigger('click');
    };

    NewView.prototype._onReset = function (e) {
        this._updateForm();
    };

    return NewView;
});
