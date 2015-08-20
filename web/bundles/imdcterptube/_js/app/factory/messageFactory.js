define(function () {
    'use strict';

    var MessageFactory = {};

    MessageFactory.edit = function (model) {
        var deferred = $.Deferred();
        var settings = {
            method: 'POST',
            url: Routing.generate('imdc_message_mark_as_read', {messageid: model.get('id')})
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.wasEdited) {
                    deferred.resolve(data);
                } else {
                    deferred.reject(data);
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    return MessageFactory;
});
