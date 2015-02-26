define(function () {
    'use strict';

    var GroupFactory = {};

    GroupFactory.delete = function (group) {
        var settings = {
            url: Routing.generate('imdc_group_delete', {groupId: group.get('id')}),
            type: 'POST'
        };

        return $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.wasDeleted) {
                    return $.Deferred().resolve(data);
                } else {
                    return $.Deferred().reject();
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    return GroupFactory;
});
