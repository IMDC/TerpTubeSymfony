define(function() {
    "use strict";

    var ThreadManager = {};

    ThreadManager.delete = function(thread) {
        var settings = {
            url: Routing.generate('imdc_thread_delete', {threadid: thread.id}),
            type: "POST",
            success: function(data, textStatus, jqXHR) {

            }.bind(this),
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.statusText);
            }.bind(this)
        };

        return $.ajax(settings);
    };

    return ThreadManager;
});
