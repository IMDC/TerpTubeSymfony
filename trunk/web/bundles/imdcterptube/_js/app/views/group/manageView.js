define([
    'component/tableComponent'
], function (TableComponent) {
    'use strict';

    var ManageView = function (controller, options) {
        this.controller = controller;

        this.bind__onShownTab = this._onShownTab.bind(this);
        this.bind__onClickRemove = this._onClickRemove.bind(this);
        this.bind__onClickAdd = this._onClickAdd.bind(this);

        this.$container = options.container;
        this.$formSearch = this.$container.find('form[name^=' + ManageView.FORM_NAME_SEARCH + ']');
        this.$formRemove = this.$container.find('form[name^=' + ManageView.FORM_NAME_REMOVE + ']');
        this.$formAdd = this.$container.find('form[name^=' + ManageView.FORM_NAME_ADD + ']');
        this.$tabs = this.$container.find(ManageView.Binder.TABS);
        this.$members = this.$container.find(ManageView.Binder.MEMBERS);
        this.$community = this.$container.find(ManageView.Binder.COMMUNITY);
        this.$remove = this.$container.find(ManageView.Binder.REMOVE);
        this.$add = this.$container.find(ManageView.Binder.ADD);

        this.$tabs.on('shown.bs.tab', this.bind__onShownTab);
        this.$remove.on('click', this.bind__onClickRemove);
        this.$add.on('click', this.bind__onClickAdd);

        var membersToggle = function (e) {
            this.$remove.attr('disabled', e.$selection.length == 0);
        }.bind(this);
        this.membersTblCmp = TableComponent.table(this.$members);
        this.membersTblCmp.subscribe(TableComponent.Event.SELECTION_CHANGE, membersToggle);

        var communityToggle = function (e) {
            this.$add.attr('disabled', e.$selection.length == 0);
        }.bind(this);
        this.communityTblCmp = TableComponent.table(this.$community);
        this.communityTblCmp.subscribe(TableComponent.Event.SELECTION_CHANGE, communityToggle);

        $tt._instances.push(this);
    };

    ManageView.TAG = 'GroupManageView';

    ManageView.Binder = {
        TABS: '.group-tabs a[data-toggle="tab"]',
        MEMBERS: '#tabMembers',
        COMMUNITY: '#tabCommunity',
        REMOVE: '.group-remove',
        ADD: '.group-add'
    };

    ManageView.FORM_NAME = 'user_group_manage_';
    ManageView.FORM_NAME_SEARCH = ManageView.FORM_NAME + 'search';
    ManageView.FORM_NAME_REMOVE = ManageView.FORM_NAME + 'remove';
    ManageView.FORM_NAME_ADD = ManageView.FORM_NAME + 'add';

    ManageView.prototype._getFormField = function (form, fieldName) {
        return form.find('#' + form.attr('name') + '_' + fieldName);
    };

    ManageView.prototype._onShownTab = function (e) {
        this._getFormField(this.$formSearch, 'active_tab').val($(e.target).attr('href'));
    };

    ManageView.prototype._updateUsersSelectForm = function ($users, $form) {
        var $userList = this._getFormField($form, 'users');
        $userList.html('');

        $users.each(function (index, element) {
            var $newUser = $userList.data('prototype');
            $newUser = $($newUser.replace(/__name__/g, index));
            $newUser.val($(element).data('uid'));
            $userList.append($newUser);
        });
    };

    ManageView.prototype._onClickRemove = function (e) {
        e.preventDefault();

        this.$remove.button('loading');
        this.$remove.toggleClass('disabled');

        this._updateUsersSelectForm(this.membersTblCmp.getSelection(), this.$formRemove);
        this.$formRemove.submit();
    };

    ManageView.prototype._onClickAdd = function (e) {
        e.preventDefault();

        this.$add.button('loading');
        this.$add.toggleClass('disabled');

        this._updateUsersSelectForm(this.communityTblCmp.getSelection(), this.$formAdd);
        this.$formAdd.submit();
    };

    return ManageView;
});
