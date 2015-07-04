define(function () {
    'use strict';

    var ContactFactory = {};

    ContactFactory.delete = function (userIds, contactList) {
        var deferred = $.Deferred();
        var settings = {
            method: 'DELETE',
            url: Routing.generate('imdc_contact_delete'),
            data: {
                userIds: userIds,
                contactList: contactList
            }
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    return ContactFactory;
});
