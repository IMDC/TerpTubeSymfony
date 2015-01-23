define([
    'core/subscriber',
    'extra'
], function (Subscriber) {
    'use strict';

    var TableComponent = function (options) {
        Subscriber.prototype.constructor.apply(this);

        this.bind__onClickToggleSelection = this._onClickToggleSelection.bind(this);
        this.bind__onClickBulkAction = this._onClickBulkAction.bind(this);
        this.bind__onClickItems = this._onClickItems.bind(this);

        this.$table = options.$table;
        this.$toggleSelection = this.$table.find(TableComponent.Binder.TOGGLE_SELECTION);
        this.$bulkActions = this.$table.find(TableComponent.Binder.BULK_ACTION);
        this.$items = this.$table.find(TableComponent.Binder.ITEM);

        this.$toggleSelection.attr('disabled', !options.multiSelect);
        if (!options.multiSelect) {
            this.$toggleSelection.prop('checked', false);
        }
        this.$toggleSelection.on('click', this.bind__onClickToggleSelection);

        this.$bulkActions.on('click', this.bind__onClickBulkAction);
        this.$items.on('change', this.bind__onClickItems);
    };

    TableComponent.extend(Subscriber);

    TableComponent.Binder = {
        TOGGLE_SELECTION: '.table-component-toggle-selection',
        BULK_ACTION: '.table-component-bulk-action',
        ITEM: 'input.table-component-item:not([disabled])'
    };

    TableComponent.Event = {
        SELECTION_CHANGE: 'eventSelectionChange',
        CLICK_BULK_ACTION: 'eventClickBulkAction'
    };

    TableComponent.prototype._dispatchToggleEvent = function (e) {
        var $checked = this.getSelection();
        this._dispatch(TableComponent.Event.SELECTION_CHANGE, {
            $selection: $checked,
            tableComponent: this,
            parentEvent: e
        });
    };

    TableComponent.prototype._onClickToggleSelection = function (e) {
        this.$items.prop('checked', $(e.target).is('input[type="checkbox"]') ? e.target.checked : this.getSelection().length == 0);
        this._dispatchToggleEvent(e);
    };

    TableComponent.prototype._onClickBulkAction = function (e) {
        var action = $(e.currentTarget).data('action');
        this._dispatch(TableComponent.Event.CLICK_BULK_ACTION, {
            $bulkAction: $(e.currentTarget),
            action: action,
            $selection: this.getSelection(),
            tableComponent: this,
            parentEvent: e
        });
    };

    TableComponent.prototype._onClickItems = function (e) {
        var $checked = this.getSelection();
        this._dispatch(TableComponent.Event.SELECTION_CHANGE, {
            $selection: $checked,
            tableComponent: this,
            parentEvent: e
        });

        var allChecked = $checked.length == this.$items.length;
        this.$toggleSelection.prop('checked', allChecked);
        if (allChecked) {
            this._dispatchToggleEvent(e);
        }
    };

    TableComponent.prototype.getTable = function () {
        return this.$table;
    };

    TableComponent.prototype.getBulkActions = function () {
        return this.$bulkActions;
    };

    TableComponent.prototype.getSelection = function () {
        return this.$items.filter(':checked');
    };

    TableComponent.table = function ($table, options) {
        var defaults = {
            multiSelect: true
        };

        options = options || defaults;
        for (var o in defaults) {
            options[o] = typeof options[o] != "undefined" ? options[o] : defaults[o];
        }

        options.$table = $table;

        return new TableComponent(options);
    };

    return TableComponent;
});
