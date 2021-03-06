define([
    'component/mediaChooserComponent',
    'component/accessTypeComponent'
], function (MediaChooserComponent, AccessTypeComponent) {
    'use strict';

    var NewView = function (controller, options) {
        this.controller = controller;

        this.bind__onSubmitForm = this._onSubmitForm.bind(this);
        this.bind__onUploadStart = this._onUploadStart.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onRemove = this._onRemove.bind(this);
        this.bind__onReset = this._onReset.bind(this);
        this.bind__onError = this._onError.bind(this);

        this.$container = options.container;
        this.$form = this.$container.find('form[name^=' + NewView.FORM_NAME + ']');
        this.$submit = this.$container.find(NewView.Binder.SUBMIT);

        this.$form.on('submit', this.bind__onSubmitForm);

        this.atCmp = AccessTypeComponent.render(this.$form);

        this.mcCmp = MediaChooserComponent.render(this.$form);
        this.mcCmp.subscribe(MediaChooserComponent.Event.UPLOAD_START, this.bind__onUploadStart);
        this.mcCmp.subscribe(MediaChooserComponent.Event.SUCCESS, this.bind__onSuccess);
        this.mcCmp.subscribe(MediaChooserComponent.Event.REMOVE, this.bind__onRemove);
        this.mcCmp.subscribe(MediaChooserComponent.Event.RESET, this.bind__onReset);
        this.mcCmp.subscribe(MediaChooserComponent.Event.ERROR, this.bind__onError);

        var mediaIds = [];
        this._getFormField('mediaIncluded').children().each(function (index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this.$submit.attr('disabled', true);
            this.mcCmp.setMedia(mediaIds);
        }

        $tt._instances.push(this);
    };

    NewView.TAG = 'ThreadNewView';

    NewView.Binder = {
        SUBMIT: '.thread-submit'
    };

    // this must be the same name defined in {bundle}/Form/Type/ThreadType
    NewView.FORM_NAME = 'thread';

    NewView.prototype._getFormField = function (fieldName) {
        return this.$form.find('#' + NewView.FORM_NAME + '_' + fieldName);
    };

    NewView.prototype._onSubmitForm = function (e) {
        if (this.$form[0].checkValidity()) {
            this.$submit.button('loading');
        }
    };

    NewView.prototype._updateForm = function () {
        var $formField = this._getFormField('mediaIncluded');
        $formField.html(
            this.mcCmp.generateFormData(
                $formField.data('prototype')
            )
        );

        $formField = this._getFormField('title');
        $formField.attr('required', this.mcCmp.media.length == 0);

        $formField = $formField.parent().find('label');
        if (this.mcCmp.media.length == 0) {
            $formField.addClass('required');
        } else {
            $formField.removeClass('required');
        }
    };

    NewView.prototype._onUploadStart = function (e) {
        this.$submit.attr('disabled', true);
    };

    NewView.prototype._onSuccess = function (e) {
        this._updateForm();

        this.$submit.attr('disabled', false);
    };

    NewView.prototype._onRemove = function (e) {
        this._updateForm();
    };

    NewView.prototype._onReset = function (e) {
        this._updateForm();
    };
    
    NewView.prototype._onError = function (e) {
	    alert('Error: ' + e.error);
    };

    return NewView;
});
