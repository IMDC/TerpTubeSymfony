define(function () {
    'use strict';

    var GroupFactory = {};

    GroupFactory.delete = function (group) {
        var deferred = $.Deferred();
        var settings = {
            url: Routing.generate('imdc_group_delete', {groupId: group.get('id')}),
            type: 'POST'
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.wasDeleted) {
                    deferred.resolve(data);
                } else {
                    deferred.reject();
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject();
            });

        return deferred.promise();
    };

    return GroupFactory;
});
