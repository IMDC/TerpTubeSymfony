define(function () {
    'use strict';

    var PostFactory = {};

    PostFactory._prepForm = function (form, settings) {
        if (form) {
            settings.type = 'POST';
            settings.contentType = false;
            settings.data = new FormData(form);
            settings.processData = false;
        }
    };

    PostFactory.new = function (post, form) {
        var settings = {
            url: Routing.generate('imdc_post_new', {threadId: post.get('parent_thread.id'), pid: (post.get('parent_post.id') || post.get('id'))})
        };

        PostFactory._prepForm(form, settings);

        return $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                return $.Deferred().resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    PostFactory.view = function (post) {
        var settings = {
            url: Routing.generate('imdc_post_view', {pid: post.get('id')})
        };

        return $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                return $.Deferred().resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    PostFactory.edit = function (post, form) {
        var settings = {
            url: Routing.generate('imdc_post_edit', {pid: post.get('id')})
        };

        PostFactory._prepForm(form, settings);

        return $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.wasEdited) {
                    //TODO model merge
                    post.set('start_time', data.post.start_time);
                    post.set('end_time', data.post.end_time);
                    post.set('is_temporal', data.post.is_temporal);
                    post.set('parent_post', data.post.parent_post);
                    post.set('parent_thread', data.post.parent_thread);
                }
                return $.Deferred().resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    PostFactory.delete = function (post) {
        var settings = {
            url: Routing.generate('imdc_post_delete', {pid: post.get('id')}),
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

    return PostFactory;
});
