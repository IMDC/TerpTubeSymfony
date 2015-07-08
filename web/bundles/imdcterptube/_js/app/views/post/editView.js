define([
    'views/post/newView',
    'core/helper'
], function (NewView, Helper) {
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
                //FIXME i am a duplicate
                dust.render('post_view', {post: this.controller.model.data}, function (err, out) {
                    this.$container.replaceWith(out);
                    if (this.controller.model.get('is_temporal', false)) {
                        this.controller.editKeyPoint({cancel: true});
                    }
                    var _self = this;
                    var ViewView = require('views/post/viewView');
                    _self = new ViewView(this.controller, this.controller.options);
                    this.controller.onViewLoaded();
                    //FIXME view was not present when model was changed. force it now to update the view
                    this.controller.model.forceChange();
                }.bind(this));
            }.bind(this))
            .fail(function (data) {
                //TODO make me better
                //FIXME i am a duplicate
                dust.render('post_edit', {post: this.controller.model.data}, function (err, out) {
                    this.$container.replaceWith(out);
                    Helper.autoSize();
                    if (this.controller.model.get('is_temporal', false)) {
                        this.controller.editKeyPoint({cancel: false});
                    }
                    var _self = this;
                    var EditView = require('views/post/editView');
                    _self = new EditView(this.controller, this.controller.options);
                    this.controller.onViewLoaded();
                    //FIXME view was not present when model was changed. force it now to update the view
                    this.controller.model.forceChange();
                }.bind(this));
            }.bind(this));
    };

    EditView.prototype._onClickCancelEdit = function (e) {
        e.preventDefault();

        this.controller.get()
            .done(function (data) {
                //TODO make me better
                //FIXME i am a duplicate
                dust.render('post_view', {post: this.controller.model.data}, function (err, out) {
                    this.$container.replaceWith(out);
                    if (this.controller.model.get('is_temporal', false)) {
                        this.controller.editKeyPoint({cancel: true});
                    }
                    var _self = this;
                    var ViewView = require('views/post/viewView');
                    _self = new ViewView(this.controller, this.controller.options);
                    this.controller.onViewLoaded();
                    //FIXME view was not present when model was changed. force it now to update the view
                    this.controller.model.forceChange();
                }.bind(this));
            }.bind(this))
            .fail(function (data) {
                //TODO
            });
    };

    return EditView;
});
