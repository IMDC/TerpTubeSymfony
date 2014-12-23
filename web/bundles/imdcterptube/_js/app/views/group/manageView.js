define(function () {
    'use strict';

    var ManageView = function (controller, options) {
        this.controller = controller;

        //this.bind__onClickSearch = this._onClickSearch.bind(this);
        this.bind__onShownTab = this._onShownTab.bind(this);
        this.bind__onClickRemove = this._onClickRemove.bind(this);
        this.bind__onClickAdd = this._onClickAdd.bind(this);

        this.$container = options.container;
        this.$formSearch = this.$container.find('form[name^=' + ManageView.FORM_NAME_SEARCH + ']');
        this.$formRemove = this.$container.find('form[name^=' + ManageView.FORM_NAME_REMOVE + ']');
        this.$formAdd = this.$container.find('form[name^=' + ManageView.FORM_NAME_ADD + ']');
        //this.$search = this.$container.find(ManageView.Binder.SEARCH);
        this.$tabs = this.$container.find(ManageView.Binder.TABS);
        this.$membersToggle = this.$container.find(ManageView.Binder.MEMBERS_TOGGLE);
        this.$communityToggle = this.$container.find(ManageView.Binder.COMMUNITY_TOGGLE);
        //this.$users = this.$container.find(ManageView.Binder.MEMBERS_CBS + ',' + ManageView.Binder.COMMUNITY_CBS);
        this.$members = this.$container.find(ManageView.Binder.MEMBERS_CBS);
        this.$community = this.$container.find(ManageView.Binder.COMMUNITY_CBS);
        this.$remove = this.$container.find(ManageView.Binder.REMOVE);
        this.$add = this.$container.find(ManageView.Binder.ADD);

        //this.$search.on('click', this.bind__onClickSearch);
        this.$tabs.on('shown.bs.tab', this.bind__onShownTab);

        this.$membersToggle.on('click', function (e) {
            this.$members.prop('checked', this.$members.filter(':checked').length == 0);
            this.$remove.attr('disabled', this.$members.filter(':checked').length == 0);
        }.bind(this));
        this.$communityToggle.on('click', function (e) {
            this.$community.prop('checked', this.$community.filter(':checked').length == 0);
            this.$add.attr('disabled', this.$community.filter(':checked').length == 0);
        }.bind(this));

        this.$members.on('change', function (e) {
            $(e.target).prop('checked', $(e.target).prop('checked'));

            var checked = this.$members.filter(':checked').length;
            this.$remove.attr('disabled', checked == 0);
            this.$membersToggle.prop('checked', checked == this.$members.length);
        }.bind(this));
        this.$community.on('change', function (e) {
            $(e.target).prop('checked', $(e.target).prop('checked'));

            var checked = this.$community.filter(':checked').length;
            this.$add.attr('disabled', checked == 0);
            this.$communityToggle.prop('checked', checked == this.$community.length);
        }.bind(this));

        this.$remove.on('click', this.bind__onClickRemove);
        this.$add.on('click', this.bind__onClickAdd);

        $tt._instances.push(this);
    };

    ManageView.TAG = 'GroupManageView';

    ManageView.Binder = {
        //SEARCH: '.group-search',
        TABS: '.group-tabs a[data-toggle="tab"]',
        MEMBERS_TOGGLE: '#tab-members .group-toggle-selection',
        COMMUNITY_TOGGLE: '#tab-community .group-toggle-selection',
        MEMBERS_CBS: '#tab-members input[type="checkbox"][data-uid]:not([disabled])',
        COMMUNITY_CBS: '#tab-community input[type="checkbox"][data-uid]:not([disabled])',
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

    /*ManageView.prototype._onClickSearch = function (e) {

     };*/

    ManageView.prototype._updateUsersSelectForm = function (users, form) {
        if (users.length == 0) {
            alert('No users selected.');
            return true;
        }

        var userList = this._getFormField(form, 'users');
        userList.html('');

        users.each(function (index, element) {
            var newUser = userList.data('prototype');
            newUser = $(newUser.replace(/__name__/g, index));
            newUser.val($(element).data('uid'));
            userList.append(newUser);
        });
    };

    ManageView.prototype._onClickRemove = function (e) {
        e.preventDefault();

        this._updateUsersSelectForm(this.$members.filter(':checked'), this.$formRemove);

        this.$remove.button('loading');
        this.$remove.toggleClass('disabled');
        this.$formRemove.submit();
    };

    ManageView.prototype._onClickAdd = function (e) {
        e.preventDefault();

        this._updateUsersSelectForm(this.$community.filter(':checked'), this.$formAdd);

        this.$add.button('loading');
        this.$add.toggleClass('disabled');
        this.$formAdd.submit();
    };

    return ManageView;
});
