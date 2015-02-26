define([
    'views/forum/newView'
], function (NewView) {
    'use strict';

    var EditView = function (controller, options) {
        NewView.prototype.constructor.apply(this, arguments);

        this.bind__onClickDelete = this._onClickDelete.bind(this);

        this.$deleteModal = this.$container.find(EditView.Binder.DELETE_MODAL);
        this.$delete = this.$container.find(EditView.Binder.DELETE);

        this.$delete.on('click', this.bind__onClickDelete);
    };

    EditView.extend(NewView);

    EditView.TAG = 'ForumEditView';

    EditView.Binder.DELETE_MODAL = '.forum-delete-modal';
    EditView.Binder.DELETE = '.forum-delete';

    EditView.prototype._onClickDelete = function (e) {
        e.preventDefault();

        this.$delete.button('loading');
        this.controller.delete()
            .done(function (data) {
                this.$deleteModal
                    .find('.modal-body')
                    .html('Forum deleted successfully.');
            }.bind(this))
            .fail(function () {
                this.$container
                    .find('.modal-body')
                    .prepend('Something went wrong. Try again.');
                this.$delete.button('reset');
            }.bind(this));
    };

    return EditView;
});
