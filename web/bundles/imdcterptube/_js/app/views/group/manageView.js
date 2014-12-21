define(function () {
    'use strict';

    var ManageView = function (controller, options) {
        this.controller = controller;

        this.bind__onShownTab = this._onShownTab.bind(this);
        //this.bind__onClickSearch = this._onClickSearch.bind(this);
        this.bind__onClickRemove = this._onClickRemove.bind(this);
        this.bind__onClickAdd = this._onClickAdd.bind(this);

        this.$container = options.container;
        this.$form = this.$container.find('form[name^=' + ManageView.FORM_NAME + ']');
        this.$tabs = this.$container.find(ManageView.Binder.TABS);
        //this.$search = this.$container.find(ManageView.Binder.SEARCH);
        this.$remove = this.$container.find(ManageView.Binder.REMOVE);
        this.$add = this.$container.find(ManageView.Binder.ADD);

        this.$tabs.on('shown.bs.tab', this.bind__onShownTab);
        //this.$search.on('click', this.bind__onClickSearch);
        this.$remove.on('click', this.bind__onClickRemove);
        this.$add.on('click', this.bind__onClickAdd);

        this.controller.onViewLoaded();
    };

    ManageView.TAG = 'GroupManageView';

    ManageView.Binder = {
        TABS: '.group-tabs a[data-toggle="tab"]',
        //SEARCH: '.group-search',
        REMOVE: '.group-remove',
        ADD: '.group-add'
    };

    ManageView.FORM_NAME = 'user_group_manage_search';

    ManageView.prototype._getFormField = function (fieldName) {
        return this.$form.find('#' + ManageView.FORM_NAME + '_' + fieldName);
    };

    ManageView.prototype._onShownTab = function (e) {
        this._getFormField('active_tab').val($(e.target).attr('href'));
    };

    /*ManageView.prototype._onClickSearch = function (e) {

    };*/

    ManageView.prototype._onClickRemove = function (e) {

    };

    ManageView.prototype._onClickAdd = function (e) {

    };

    return ManageView;
});
