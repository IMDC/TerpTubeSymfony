define([
    'core/mediaChooser'
], function (MediaChooser) {
    'use strict';

    var EditView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickSubmit = this._onClickSubmit.bind(this);
        this.bind__onClickDelete = this._onClickDelete.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);

        this.$container = options.container;
        this.$form = this.$container.find('form[name^=' + EditView.FORM_NAME + ']');
        this.$submit = this.$container.find(EditView.Binder.SUBMIT);
        this.$deleteModal = this.$container.find(EditView.Binder.DELETE_MODAL);
        this.$delete = this.$container.find(EditView.Binder.DELETE);

        this.$submit.on('click', this.bind__onClickSubmit);
        this.$delete.on('click', this.bind__onClickDelete);

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

    EditView.TAG = 'GroupEditView';

    EditView.Binder = {
        SUBMIT: '.group-submit',
        DELETE_MODAL: '.group-delete-modal',
        DELETE: '.group-delete'
    };

    // this must be the same name defined in {bundle}/Form/Type/UserGroupType
    EditView.FORM_NAME = 'user_group';

    EditView.prototype._getFormField = function (fieldName) {
        return this.$form.find('#' + EditView.FORM_NAME + '_' + fieldName);
    };

    EditView.prototype._onClickSubmit = function (e) {
        if (this.$form[0].checkValidity()) {
            $(e.target).button('loading');
        }
    };

    EditView.prototype._onClickDelete = function (e) {
        e.preventDefault();

        this.$delete.button('loading');
        this.controller.delete()
            .done(function (data) {
                this.$deleteModal
                    .find('.modal-body')
                    .html('Group deleted successfully.');
            }.bind(this))
            .fail(function () {
                this.$container
                    .find('.modal-body')
                    .prepend('Something went wrong. Try again.');
                this.$delete.button('reset');
            }.bind(this));
    };

    EditView.prototype._updateForm = function () {
        var formField = this._getFormField('media');
        formField.html(
            this.mediaChooser.generateFormData(
                formField.data('prototype')
            )
        );
    };

    EditView.prototype._onSuccess = function (e) {
        this._updateForm();
        this.$submit.attr('disabled', false);
    };

    EditView.prototype._onReset = function (e) {
        this._updateForm();
    };

    return EditView;
});
