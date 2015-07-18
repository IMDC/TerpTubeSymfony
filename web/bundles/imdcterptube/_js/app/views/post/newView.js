define([
    'model/model',
    'component/mediaChooserComponent'
], function (Model, MediaChooserComponent) {
    'use strict';

    var NewView = function (controller, options) {
        this.controller = controller;
        this.options = options;

        this.bind__onClickSubmit = this._onClickSubmit.bind(this);
        this.bind__onClickReset = this._onClickReset.bind(this);
        this.bind__onClickCancel = this._onClickCancel.bind(this);
        this.bind__onUploadStart = this._onUploadStart.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onSuccessAndPost = this._onSuccessAndPost.bind(this);
        this.bind__onReset = this._onReset.bind(this);
        this.bind__onModelChange = this._onModelChange.bind(this);
        this.bind__onError = this._onError.bind(this);

        this.$container = $(NewView.Binder.CONTAINER + '[data-pid="' + this.controller.model.get('id') + '"]');
        this.$form = this.$container.find('form[name^=' + NewView.FORM_NAME + ']');
        this.$submit = this.$container.find(NewView.Binder.SUBMIT);
        this.$reset = this.$container.find(NewView.Binder.RESET);
        this.$cancel = this.$container.find(NewView.Binder.CANCEL);

        this.$submit.on('click', this.bind__onClickSubmit);
        this.$reset.on('click', this.bind__onClickReset);
        this.$cancel.on('click', this.bind__onClickCancel);

        this.mcCmp = MediaChooserComponent.render(this.$form, {enableDoneAndPost: true});
        this.mcCmp.subscribe(MediaChooserComponent.Event.UPLOAD_START, this.bind__onUploadStart);
        this.mcCmp.subscribe(MediaChooserComponent.Event.SUCCESS, this.bind__onSuccess);
        this.mcCmp.subscribe(MediaChooserComponent.Event.SUCCESS_AND_POST, this.bind__onSuccessAndPost);
        this.mcCmp.subscribe(MediaChooserComponent.Event.RESET, this.bind__onReset);
        this.mcCmp.subscribe(MediaChooserComponent.Event.ERROR, this.bind__onError);

        this.controller.model.subscribe(Model.Event.CHANGE, this.bind__onModelChange);

        $tt._instances.push(this);
    };

    NewView.TAG = 'PostNewView';

    NewView.Binder = {
        CONTAINER: '.post-container',
        SUBMIT: '.post-submit-new',
        RESET: '.post-reset',
        CANCEL: '.post-cancel-new'
    };

    // this must be the same name defined in {bundle}/Form/Type/PostType
    NewView.FORM_NAME = 'post';

    NewView.prototype.loadView = function () {
        var mediaIds = [];
        this._getFormField('attachedFile').children().each(function (index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this._toggleForm(true);
            this.mcCmp.setMedia(mediaIds);
        }
    };

    NewView.prototype._getFormField = function (fieldName) {
        return this.$form.find('#' + NewView.FORM_NAME + '_' + fieldName);
    };

    NewView.prototype._toggleForm = function (disabled) {
        this.$submit.button(disabled ? 'loading' : 'reset');
        this.$reset.attr('disabled', disabled);
        this.$cancel.attr('disabled', disabled);
    };

    NewView.prototype._preSubmit = function () {
        if (this._getFormField('content').val() == '' && this.mcCmp.media.length == 0) {
            alert('Your post cannot be blank. You must either select a file or write a comment.');
            return false;
        }
        this._toggleForm(true);
        return true;
    };

    NewView.prototype._reset = function () {
        this.mcCmp.reset();
        this._getFormField('startTime').val(this.controller.model.get('keyPoint.startTime'));
        this._getFormField('endTime').val(this.controller.model.get('keyPoint.endTime'));
        this._getFormField('content').val('');

        this.controller.editKeyPoint({cancel: true});
    };

    NewView.prototype._destroy = function () {
        this.$container.remove();
        this.controller.removeKeyPoint();
        this.controller.removeFromThread();
    };

    NewView.prototype._onClickSubmit = function (e) {
        e.preventDefault();

        if (!this._preSubmit())
            return;

        this.controller.post(this.$form[0])
            .done(function (data) {
                this.controller.addToThread(data.post);

                if (this.options.is_permanent) {
                    this._reset();
                    this._toggleForm(false);
                } else {
                    this._destroy();
                }
            }.bind(this))
            .fail(function (data) {
                this.controller.updateInThread('new');
            }.bind(this));
    };

    NewView.prototype._onClickReset = function (e) {
        e.preventDefault();

        this._reset();
    };

    NewView.prototype._onClickCancel = function (e) {
        e.preventDefault();

        this._destroy();
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
            this.mcCmp.generateFormData(
                formField.data('prototype')
            )
        );
    };

    NewView.prototype._onUploadStart = function (e) {
        this.$submit.attr('disabled', true);
    };

    NewView.prototype._onSuccess = function (e) {
        this._updateForm();
        this.$submit.attr('disabled', false);
        this._toggleForm(false);
    };

    NewView.prototype._onSuccessAndPost = function (e) {
        this._updateForm();
        this.$submit.trigger('click');
    };

    NewView.prototype._onReset = function (e) {
        this._updateForm();
    };

    NewView.prototype._onError = function (e) {
        alert('Error: ' + e.error);
    };

    return NewView;
});
