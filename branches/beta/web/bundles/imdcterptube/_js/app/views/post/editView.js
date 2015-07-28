define([
    'views/post/newView'
], function (NewView) {
    'use strict';

    var EditView = function (controller, options) {
        NewView.prototype.constructor.apply(this, arguments);

        this.bind__onClickSubmit = this._onClickSubmit.bind(this);
        this.bind__onClickCancel = this._onClickCancel.bind(this);

        this.$submit = this.$container.find(EditView.Binder.SUBMIT);
        this.$cancel = this.$container.find(EditView.Binder.CANCEL);

        this.$submit.on("click", this.bind__onClickSubmit);
        this.$cancel.on("click", this.bind__onClickCancel);
    };

    EditView.extend(NewView);

    EditView.TAG = 'PostEditView';

    EditView.Binder.SUBMIT = '.post-submit-edit';
    EditView.Binder.CANCEL = '.post-cancel-edit';

    EditView.prototype._toggleForm = function (disabled) {
        this.$submit.button(disabled ? 'loading' : 'reset');
        this.$cancel.attr('disabled', disabled);
    };

    EditView.prototype._onClickSubmit = function (e) {
        e.preventDefault();

        if (!this._preSubmit())
            return;

        this.controller.put(this.$form[0])
            .done(function (data) {
                this.controller.updateInThread('view');

                if (this.controller.model.get('is_temporal', false)) {
                    this.controller.editKeyPoint({cancel: true});
                }
            }.bind(this))
            .fail(function (data) {
                this.controller.updateInThread('edit');
            }.bind(this));
    };

    EditView.prototype._onClickCancel = function (e) {
        e.preventDefault();

        this.controller.get()
            .done(function (data) {
                this.controller.updateInThread('view');

                if (this.controller.model.get('is_temporal', false)) {
                    this.controller.editKeyPoint({cancel: true});
                }
            }.bind(this))
            .fail(function (data) {
                //TODO
            });
    };

    return EditView;
});
