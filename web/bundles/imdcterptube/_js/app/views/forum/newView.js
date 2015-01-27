define([
    'component/mediaChooserComponent'
], function (MediaChooserComponent) {
    'use strict';

    var NewView = function (controller, options) {
        this.controller = controller;

        this.bind__onChangeAccessType = this._onChangeAccessType.bind(this);
        this.bind__onClickSubmit = this._onClickSubmit.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);

        this.$container = options.container;
        this.$form = this.$container.find('form[name^=' + NewView.FORM_NAME + ']');
        this.$accessTypes = this.$form.find('input:radio');
        this.$submit = this.$container.find(NewView.Binder.SUBMIT);

        this.$accessTypes.on('change', this.bind__onChangeAccessType);
        this.$submit.on('click', this.bind__onClickSubmit);

        this.$accessTypes.filter(':checked').trigger('change');

        this.mcCmp = MediaChooserComponent.render(this.$form);
        this.mcCmp.subscribe(MediaChooserComponent.Event.SUCCESS, this.bind__onSuccess);
        this.mcCmp.subscribe(MediaChooserComponent.Event.RESET, this.bind__onReset);

        var mediaIds = [];
        this._getFormField('titleMedia').children().each(function (index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this.$submit.attr('disabled', true);
            this.mcCmp.setMedia(mediaIds);
        }

        $tt._instances.push(this);
    };

    NewView.TAG = 'ForumNewView';

    NewView.Binder = {
        SUBMIT: '.forum-submit'
    };

    // this must be the same name defined in {bundle}/Form/Type/ForumType
    NewView.FORM_NAME = 'forum';

    NewView.prototype._getFormField = function (fieldName) {
        return this.$form.find('#' + NewView.FORM_NAME + '_' + fieldName);
    };

    NewView.prototype._onChangeAccessType = function (e) {
        var group = this._getFormField('group');
        var parent = group.parent();

        if ($(e.target).attr('id') == this.$accessTypes.filter('[value="6"]').attr('id')) {
            parent.find('label').addClass('required');
            group.attr('required', true);
            parent.children().show();
        } else {
            parent.find('label').removeClass('required');
            group.attr('required', false);
            parent.children().hide();
        }
    };

    NewView.prototype._onClickSubmit = function (e) {
        if (this.$form[0].checkValidity()) {
            this.$submit.button('loading');
        }
    };

    NewView.prototype._updateForm = function () {
        var formField = this._getFormField('titleMedia');
        formField.html(
            this.mcCmp.generateFormData(
                formField.data('prototype')
            )
        );
    };

    NewView.prototype._onSuccess = function (e) {
        this.$submit.attr('disabled', false);

        this._getFormField('titleText')
            .attr('required', false)
            .parent()
            .find('label')
            .removeClass('required');

        this._updateForm();
    };

    NewView.prototype._onReset = function (e) {
        if (this.mcCmp.media.length == 0)
            this._getFormField('titleText')
                .attr('required', true)
                .parent()
                .find('label')
                .addClass('required');

        this._updateForm();
    };

    return NewView;
});
