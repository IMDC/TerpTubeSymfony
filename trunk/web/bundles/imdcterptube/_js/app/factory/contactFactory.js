define(function () {
    'use strict';

    var ContactFactory = {};

    ContactFactory.delete = function (userIds, contactList) {
        var settings = {
            url: Routing.generate('imdc_contact_delete'),
            type: 'POST',
            data: {
                userIds: userIds,
                contactList: contactList
            }
        };

        return $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.success) {
                    return $.Deferred().resolve(data);
                } else {
                    return $.Deferred().reject(data);
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    return ContactFactory;
});
