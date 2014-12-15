define(function() {
    "use strict";

    var PostManager = {};

    PostManager._prepForm = function(form, settings) {
        if (form) {
            settings.type = "POST";
            settings.contentType = false;
            settings.data = new FormData(form);
            settings.processData = false;
        }
    };

    PostManager.new = function(post, form) {
        var settings = {
            url: Routing.generate('imdc_post_new', {threadId: post.threadId, pid: (post.parentPostId || post.id)})
        };

        PostManager._prepForm(form, settings);

        return $.ajax(settings)
            .then(function(data, textStatus, jqXHR) {
                return $.Deferred().resolve(data);
            },
            function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    PostManager.view = function(post) {
        var settings = {
            url: Routing.generate('imdc_post_view', {pid: post.id})
        };

        return $.ajax(settings)
            .then(function(data, textStatus, jqXHR) {
                return $.Deferred().resolve(data);
            },
            function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    PostManager.edit = function(post, form) {
        var settings = {
            url: Routing.generate('imdc_post_edit', {pid: post.id})
        };

        PostManager._prepForm(form, settings);

        return $.ajax(settings)
            .then(function(data, textStatus, jqXHR) {
                if (data.wasEdited) {
                    post.startTime = data.post.startTime;
                    post.endTime = data.post.endTime;
                    post.isTemporal = data.post.isTemporal;
                }
                return $.Deferred().resolve(data);
            },
            function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    PostManager.delete = function(post) {
        var settings = {
            url: Routing.generate('imdc_post_delete', {pid: post.id}),
            type: "POST"
        };

        return $.ajax(settings)
            .then(function(data, textStatus, jqXHR) {
                if (data.wasDeleted) {
                    return $.Deferred().resolve(data);
                } else {
                    return $.Deferred().reject();
                }
            },
            function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    return PostManager;
});
