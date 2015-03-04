define([
    'component/tableComponent'
], function (TableComponent) {
    'use strict';

    var ManageView = function (controller, options) {
        this.controller = controller;

        this.bind___onClickFormCheckboxLink = this._onClickFormCheckboxLink.bind(this);
        this.bind__onShownTab = this._onShownTab.bind(this);
        this.bind__onClickRemove = this._onClickRemove.bind(this);
        this.bind__onClickAdd = this._onClickAdd.bind(this);

        this.$container = options.container;
        this.$formSearch = this.$container.find('form[name^=' + ManageView.FORM_NAME_SEARCH + ']');
        this.$formRemove = this.$container.find('form[name^=' + ManageView.FORM_NAME_REMOVE + ']');
        this.$formAdd = this.$container.find('form[name^=' + ManageView.FORM_NAME_ADD + ']');
        this.$formCheckboxLinks = this.$formSearch.find('label[class^="checkbox"] a');
        this.$tabs = this.$container.find(ManageView.Binder.TABS);
        this.$tabPanes = this.$container.find(ManageView.Binder.TAB_PANES);
        this.$remove = this.$container.find(ManageView.Binder.REMOVE);
        this.$add = this.$container.find(ManageView.Binder.ADD);

        this.$formCheckboxLinks.on('click', this.bind___onClickFormCheckboxLink);
        this.$tabs.on('shown.bs.tab', this.bind__onShownTab);
        this.$remove.on('click', this.bind__onClickRemove);
        this.$add.on('click', this.bind__onClickAdd);

        var removeToggle = function (e) {
            this.$remove.attr('disabled', e.$selection.length == 0);
        }.bind(this);
        var addToggle = function (e) {
            this.$add.attr('disabled', e.$selection.length == 0);
        }.bind(this);
        var removeOrAdd = function (type) {
            return type == 'remove' ? removeToggle : addToggle;
        };

        this.tableCmps = [];
        this.$tabPanes.each(function (index, element) {
            var $tabPane = $(element);
            var toggle = removeOrAdd($tabPane.data('type'));
            var tblCmp = TableComponent.table($tabPane);
            tblCmp.subscribe(TableComponent.Event.SELECTION_CHANGE, toggle);
            this.tableCmps.push(tblCmp);
        }.bind(this));

        this.$tabs.filter('[href="' + (location.hash
            ? '#tab' + location.hash.substr(1) // strip '#'
            : '#tabMembers') + '"]').tab('show');

        $tt._instances.push(this);
    };

    ManageView.TAG = 'GroupManageView';

    ManageView.Binder = {
        TABS: 'a[data-toggle="tab"]',
        TAB_PANES: '[id^=tab]',
        REMOVE: '.group-remove',
        ADD: '.group-add'
    };

    ManageView.FORM_NAME = 'ugm_';
    ManageView.FORM_NAME_SEARCH = ManageView.FORM_NAME + 'search';
    ManageView.FORM_NAME_REMOVE = ManageView.FORM_NAME + 'remove';
    ManageView.FORM_NAME_ADD = ManageView.FORM_NAME + 'add';

    ManageView.prototype._getFormField = function (form, fieldName) {
        return form.find('#' + form.attr('name') + '_' + fieldName);
    };

    ManageView.prototype._onClickFormCheckboxLink = function (e) {
        e.preventDefault();

        var $cbs = this.$formSearch.find('input[type="checkbox"]');
        var $cb = $(e.target).parent().find('input[type="checkbox"]');
        var checked = $cb.prop('checked');
        $cbs.prop('checked', false); // disable all others first
        $cb.prop('checked', !checked);
        this.$formSearch[0].submit();
    };

    ManageView.prototype._onShownTab = function (e) {
        var hash = '#' + $(e.target).attr('href').substr(4); // strip '#tab'

        if (history.pushState) {
            history.pushState(null, null, hash);
        } else {
            location.hash = hash;
        }

        // KnpPaginatorBundle:Pagination:twitter_bootstrap_v3_pagination.html.twig
        // update hash on pagination urls
        this.$tabPanes.find('ul.pagination li a').each(function (index, element) {
            var $link = $(element);
            var url = $link.attr('href');
            var index = url.lastIndexOf('#');
            if (index > 0) {
                url = url.substring(0, index);
            }
            $link.attr('href', url + hash);
        });
    };

    ManageView.prototype._updateUsersSelectForm = function ($form, $button) {
        var $userList = this._getFormField($form, 'users');

        $button.button('loading');
        $button.toggleClass('disabled');

        $userList.html('');
        $.each(this.tableCmps, function (index, element) {
            element.getSelection().each(function (index, element) {
                var $newUser = $userList.data('prototype');
                $newUser = $($newUser.replace(/__name__/g, index));
                $newUser.val($(element).data('uid'));
                $userList.append($newUser);
            });
        }.bind(this));
    };

    ManageView.prototype._onClickRemove = function (e) {
        e.preventDefault();

        this._updateUsersSelectForm(this.$formRemove, this.$remove);

        this.$formRemove.submit();
    };

    ManageView.prototype._onClickAdd = function (e) {
        e.preventDefault();

        this._updateUsersSelectForm(this.$formAdd, this.$add);

        this.$formAdd.submit();
    };

    return ManageView;
});
