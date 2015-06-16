/**
 * Created by imdc on 26/05/2015.
 */
define(function () {
    'use strict';

    var MessageFactory = {};

    MessageFactory.edit = function (model) {
        var deferred = $.Deferred();
        var settings = {
            url: Routing.generate('imdc_message_mark_as_read', {messageid: model.get('id')}),
            type: 'POST'
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.wasEdited) {
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

    return MessageFactory;
});
