define(function () {
    'use strict';

    var ContactFactory = {};

    ContactFactory.delete = function (userIds, contactList) {
        var deferred = $.Deferred();
        var settings = {
            url: Routing.generate('imdc_contact_delete'),
            type: 'POST',
            data: {
                userIds: userIds,
                contactList: contactList
            }
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.success) {
                    deferred.resolve(data);
                } else {
                    deferred.reject(data);
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject();
            });

        return deferred.promise();
    };

    return ContactFactory;
});
