define([
    'views/post/newView'
], function (NewView) {
    'use strict';

    var EditView = function (controller, options) {
        NewView.prototype.constructor.apply(this, arguments);

        this.bind__onClickSubmitEdit = this._onClickSubmitEdit.bind(this);
        this.bind__onClickCancelEdit = this._onClickCancelEdit.bind(this);

        this.$submitEdit = this.$container.find(EditView.Binder.SUBMIT_EDIT);
        this.$cancelEdit = this.$container.find(EditView.Binder.CANCEL_EDIT);

        this.$submitEdit.on("click", this.bind__onClickSubmitEdit);
        this.$cancelEdit.on("click", this.bind__onClickCancelEdit);
    };

    EditView.extend(NewView);

    EditView.TAG = 'PostEditView';

    EditView.Binder.SUBMIT_EDIT = '.post-submit-edit';
    EditView.Binder.CANCEL_EDIT = '.post-cancel-edit';

    EditView.prototype._toggleForm = function (disabled) {
        this.$submitEdit.button(disabled ? 'loading' : 'reset');
        this.$cancelEdit.attr('disabled', disabled);
    };

    EditView.prototype._onClickSubmitEdit = function (e) {
        e.preventDefault();

        if (!this._preSubmit())
            return;

        this.controller.edit(this.$form[0])
            .done(function (data) {
                //TODO make me better
                this.$container.replaceWith(data.html);
                //this.controller.removeKeyPoint();
                if (this.controller.model.get('is_temporal', false)) {
                    this.controller.editKeyPoint({cancel: true});
                }
                var _self = this;
                var ViewView = require('views/post/viewView');
                _self = new ViewView(this.controller, this.controller.options);
                this.controller.onViewLoaded();
                // ViewView was not present when model was changed. force it now to update the view
                this.controller.model.forceChange();
            }.bind(this))
            .fail(function () {
                this._toggleForm(false);
            }.bind(this));
    };

    EditView.prototype._onClickCancelEdit = function (e) {
        e.preventDefault();

        this.controller.view()
            .done(function (data) {
                //TODO make me better
                this.$container.replaceWith(data.html);
                //this.controller.removeKeyPoint();
                if (this.controller.model.get('is_temporal', false)) {
                    this.controller.editKeyPoint({cancel: true});
                }
                var _self = this;
                var ViewView = require('views/post/viewView');
                //this.controller.model.set('keyPoint.videoDuration', 0, false); // force view update in the future
                _self = new ViewView(this.controller, this.controller.options);
                this.controller.onViewLoaded();
                // ViewView was not present when model was changed. force it now to update the view
                this.controller.model.forceChange();
            }.bind(this));
    };

    return EditView;
});
