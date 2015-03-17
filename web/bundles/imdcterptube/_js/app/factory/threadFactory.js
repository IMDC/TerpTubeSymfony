define(function () {
    'use strict';

    var ThreadFactory = {};

    ThreadFactory.delete = function (model) {
        var deferred = $.Deferred();
        var settings = {
            url: Routing.generate('imdc_thread_delete', {threadid: model.get('id')}),
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

    return ThreadFactory;
});
