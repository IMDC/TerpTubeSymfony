define([
    'component/mediaChooserComponent'
], function (MediaChooserComponent) {
    'use strict';

    var NewView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickSubmit = this._onClickSubmit.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);

        this.$container = options.container;
        this.$form = this.$container.find('form[name^=' + NewView.FORM_NAME + ']');
        this.$submit = this.$container.find(NewView.Binder.SUBMIT);

        this.$submit.on('click', this.bind__onClickSubmit);

        this.mcCmp = MediaChooserComponent.render(this.$form);
        this.mcCmp.subscribe(MediaChooserComponent.Event.SUCCESS, this.bind__onSuccess);
        this.mcCmp.subscribe(MediaChooserComponent.Event.RESET, this.bind__onReset);

        var mediaIds = [];
        this._getFormField('media').children().each(function (index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this.$submit.attr('disabled', true);
            this.mcCmp.setMedia(mediaIds);
        }

        $tt._instances.push(this);
    };

    NewView.TAG = 'GroupNewView';

    NewView.Binder = {
        SUBMIT: '.group-submit'
    };

    // this must be the same name defined in {bundle}/Form/Type/UserGroupType
    NewView.FORM_NAME = 'user_group';

    NewView.prototype._getFormField = function (fieldName) {
        return this.$form.find('#' + NewView.FORM_NAME + '_' + fieldName);
    };

    NewView.prototype._onClickSubmit = function (e) {
        if (this.$form[0].checkValidity()) {
            this.$submit.button('loading');
        }
    };

    NewView.prototype._updateForm = function () {
        var formField = this._getFormField('media');
        formField.html(
            this.mcCmp.generateFormData(
                formField.data('prototype')
            )
        );
    };

    NewView.prototype._onSuccess = function (e) {
        this._updateForm();
        this.$submit.attr('disabled', false);
    };

    NewView.prototype._onReset = function (e) {
        this._updateForm();
    };

    return NewView;
});
