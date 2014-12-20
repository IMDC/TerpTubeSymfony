define(function () {
    'use strict';

    var ManageView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickRemove = this._onClickRemove.bind(this);
        this.bind__onClickAdd = this._onClickAdd.bind(this);

        this.$container = options.container;
        this.$remove = this.$container.find(ManageView.Binder.REMOVE);
        this.$add = this.$container.find(ManageView.Binder.ADD);

        this.$remove.on('click', this.bind__onClickRemove);
        this.$add.on('click', this.bind__onClickAdd);

        this.controller.onViewLoaded();
    };

    ManageView.TAG = 'GroupManageView';

    ManageView.Binder = {
        REMOVE: '.group-remove',
        ADD: '.group-add'
    };

    ManageView.prototype._onClickRemove = function (e) {

    };

    ManageView.prototype._onClickAdd = function (e) {

    };

    return ManageView;
});
