define([
    'core/mediaChooser'
], function (MediaChooser) {
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

        this.mediaChooser = new MediaChooser();
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.setContainer(this.$form);
        this.mediaChooser.bindUIEvents();

        var mediaIds = [];
        this._getFormField('media').children().each(function (index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this.$submit.attr('disabled', true);
            this.mediaChooser.setMedia(mediaIds);
        }

        this.controller.onViewLoaded();
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
            $(e.target).button('loading');
        }
    };

    NewView.prototype._updateForm = function () {
        var formField = this._getFormField('media');
        formField.html(
            this.mediaChooser.generateFormData(
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
