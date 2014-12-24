define(function () {
    'use strict';

    var ThreadFactory = {};

    ThreadFactory.delete = function (thread) {
        var settings = {
            url: Routing.generate('imdc_thread_delete', {threadid: thread.get('id')}),
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

    return ThreadFactory;
});
