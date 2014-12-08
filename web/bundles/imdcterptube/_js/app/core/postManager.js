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
            url: Routing.generate('imdc_post_new', {threadId: post.threadId, pid: post.id}),
            success: function(data, textStatus, jqXHR) {

            }.bind(this),
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.statusText);
            }.bind(this)
        };

        PostManager._prepForm(form, settings);
        return $.ajax(settings);
    };

    PostManager.view = function(post) {
        var settings = {
            url: Routing.generate('imdc_post_view', {pid: post.id}),
            success: function(data, textStatus, jqXHR) {

            }.bind(this),
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.statusText);
            }.bind(this)
        };

        return $.ajax(settings);
    };

    PostManager.edit = function(post, form) {
        var settings = {
            url: Routing.generate('imdc_post_edit', {pid: post.id}),
            success: function(data, textStatus, jqXHR) {
                if (data.wasEdited) {
                    post.startTime = data.post.startTime;
                    post.endTime = data.post.endTime;
                    post.isTemporal = data.post.isTemporal;
                }
            }.bind(this),
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.statusText);
            }.bind(this)
        };

        PostManager._prepForm(form, settings);
        return $.ajax(settings);
    };

    PostManager.delete = function(post) {
        var settings = {
            url: Routing.generate('imdc_post_delete', {pid: post.id}),
            type: "POST",
            success: function(data, textStatus, jqXHR) {

            }.bind(this),
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.statusText);
            }.bind(this)
        };

        return $.ajax(settings);
    };

    return PostManager;
});
