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
            url: Routing.generate('imdc_post_new', {threadId: post.get('threadId'), pid: (post.get('parentPostId') || post.get('id'))})
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
                    post.set('startTime', data.post.startTime);
                    post.set('endTime', data.post.endTime);
                    post.set('isTemporal', data.post.isTemporal);
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
