define(function () {
    'use strict';

    var AccessTypeComponent = function (options) {
        this.options = options;
        this.typeToFieldMap = [
            {filter: '[value="4"]', fields: ['accessType_data_users']},
            {filter: '[value="6"]', fields: ['group']}
        ];

        this.bind__onChangeAccessType = this._onChangeAccessType.bind(this);

        this.$container = this.options.$container;
        this.$accessTypes = this.$container.find('input:radio');

        this.$accessTypes.on('change', this.bind__onChangeAccessType);

        this.$accessTypes.filter(':checked').trigger('change');
    };

    AccessTypeComponent.TAG = 'AccessTypeComponent';

    AccessTypeComponent.prototype._getFormField = function (fieldName) {
        return this.$container.find('[id$="_' + fieldName + '"]');
    };

    AccessTypeComponent.prototype._onChangeAccessType = function (e) {
        var selected = $(e.target);
        var toggleField = function (accessType, field) {
            var parent = field.parent();

            if (selected.attr('id') == accessType.attr('id')) {
                parent.find('label').addClass('required');
                field.attr('required', true);
                parent.children().show();
            } else {
                parent.find('label').removeClass('required');
                field.attr('required', false);
                parent.children().hide();
            }
        };

        $.each(this.typeToFieldMap, function (index, element) {
            var accessType = this.$accessTypes.filter(element.filter);
            $.each(element.fields, function (i, e) {
                toggleField(accessType, this._getFormField(e));
            }.bind(this));
        }.bind(this));
    };

    AccessTypeComponent.render = function ($form, options) {
        var defaults = {};

        options = options || defaults;
        for (var o in defaults) {
            options[o] = typeof options[o] != 'undefined' ? options[o] : defaults[o];
        }

        options.$container = $form;

        return new AccessTypeComponent(options);
    };

    return AccessTypeComponent;
});
