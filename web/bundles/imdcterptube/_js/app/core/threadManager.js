define(function() {
    "use strict";

    var ThreadManager = {};

    ThreadManager.delete = function(thread) {
        var settings = {
            url: Routing.generate('imdc_thread_delete', {threadid: thread.id}),
            type: "POST"
        };

        return $.ajax(settings)
            .then(function(data, textStatus, jqXHR) {
                if (data.wasDeleted) {
                    return $.Deferred().resolve(data);
                } else {
                    return $.Deferred().reject();
                }
            }.bind(this),
            function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.statusText);
                return $.Deferred().reject();
            }.bind(this));
    };

    return ThreadManager;
});
