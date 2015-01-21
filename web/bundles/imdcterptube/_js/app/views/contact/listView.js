define([
    'component/tableComponent',
    'factory/contactFactory'
], function (TableComponent, ContactFactory) {
    'use strict';

    var ListView = function (controller, options) {
        this.controller = controller;

        this.bind__onSelectionChange = this._onSelectionChange.bind(this);
        this.bind__onClickBulkAction = this._onClickBulkAction.bind(this);

        this.$container = options.container;
        this.$form = this.$container.find('form[name^=' + ListView.FORM_NAME + ']');
        this.$tabPanes = this.$container.find(ListView.Binder.TAB_PANES);

        this.tabPaneTblCmps = [];
        this.$tabPanes.each(function (index, element) {
            this.tabPaneTblCmps[element.id] = TableComponent.table($(element));
            this.tabPaneTblCmps[element.id].subscribe(TableComponent.Event.SELECTION_CHANGE, this.bind__onSelectionChange);
            this.tabPaneTblCmps[element.id].subscribe(TableComponent.Event.CLICK_BULK_ACTION, this.bind__onClickBulkAction);
        }.bind(this));

        $tt._instances.push(this);
    };

    ListView.TAG = 'ContactListView';

    ListView.Binder = {
        TAB_PANES: '.tab-pane[id^=tab]'
    };

    ListView.FORM_NAME = 'users_select';

    ListView.prototype._getFormField = function (form, fieldName) {
        return form.find('#' + form.attr('name') + '_' + fieldName);
    };

    ListView.prototype._onSelectionChange = function (e) {
        var $bulkActions = e.tableComponent.getBulkActions();
        $bulkActions.attr('disabled', e.$selection.length == 0);
    };

    ListView.prototype._updateUsersSelectForm = function ($users) {
        var $userList = this._getFormField(this.$form, 'users');
        $userList.html('');

        $users.each(function (index, element) {
            var $newUser = $userList.data('prototype');
            $newUser = $($newUser.replace(/__name__/g, index));
            $newUser.val($(element).data('uid'));
            $userList.append($newUser);
        });
    };

    ListView.prototype._onClickBulkAction = function (e) {
        switch (e.action) {
            case 1: // remove
                var contactList = e.tableComponent.getTable()
                    .attr('id')
                    .replace(/^tab/, '')
                    .toLowerCase();

                if (contactList == 'all') {
                    if (!confirm('This will remove the selected contacts from all lists. Continue?'))
                        break;
                }

                e.$bulkAction.attr('disabled', true);

                var userIds = [];
                e.$selection.each(function (index, element) {
                    userIds.push($(element).data('uid'));
                });

                ContactFactory.delete(userIds, contactList)
                    .done(function (data) {
                        window.location.reload(true);
                    })
                    .fail(function (data) {
                        if (data) {
                            alert(data.message);
                        } else {
                            alert('Something went wrong.');
                        }
                        e.$bulkAction.attr('disabled', false);
                    });
                break;
            case 2: // new group
                e.$bulkAction.attr('disabled', true);
                this._updateUsersSelectForm(e.$selection);
                this.$form.attr('action', Routing.generate('imdc_group_new'));
                this.$form.submit();
                break;
            case 3: // send message
                e.$bulkAction.attr('disabled', true);
                this._updateUsersSelectForm(e.$selection);
                this.$form.attr('action', Routing.generate('imdc_message_new'));
                this.$form.submit();
                break;
        }
    };

    return ListView;
});
