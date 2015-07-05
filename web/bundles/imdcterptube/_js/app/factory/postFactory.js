define(function () {
    'use strict';

    var PostFactory = {};

    PostFactory._prepForFormPost = function (form, settings) {
        settings.type = 'POST';
        settings.contentType = false;
        if (form) {
            settings.data = new FormData(form);
        }
        settings.processData = false;
    };

    PostFactory.new = function (model, form) {
        var pid = model.get('id', -1);
        var route = Routing.getRoute('imdc_post_new');
        var deferred = $.Deferred();
        var settings = {
            url: Routing.generate('imdc_post_new', {
                threadId: model.get('parent_thread.id'),
                pid: (model.get('parent_post.id') || (pid > 0 ? pid : null) || route.defaults.pid)
            })
        };

        PostFactory._prepForFormPost(form, settings);

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject();
            });

        return deferred.promise();
    };

    PostFactory.view = function (model) {
        var deferred = $.Deferred();
        var settings = {
            url: Routing.generate('imdc_post_view', {pid: model.get('id')})
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject();
            });

        return deferred.promise();
    };

    PostFactory.edit = function (model, form) {
        var deferred = $.Deferred();
        var settings = {
            url: Routing.generate('imdc_post_edit', {pid: model.get('id')})
        };

        PostFactory._prepForFormPost(form, settings);

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.wasEdited) {
                    //TODO model merge
                    model.set('start_time', data.post.start_time);
                    model.set('end_time', data.post.end_time);
                    model.set('is_temporal', data.post.is_temporal);
                    model.set('parent_post', data.post.parent_post);
                    model.set('parent_thread', data.post.parent_thread);
                    model.set('keyPoint.startTime', model.get('start_time'));
                    model.set('keyPoint.endTime', model.get('end_time'));
                }
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject();
            });

        return deferred.promise();
    };

    PostFactory.delete = function (model) {
        var deferred = $.Deferred();
        var settings = {
            method: 'DELETE',
            url: Routing.generate('imdc_delete_post', {postId: model.get('id')})
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    return PostFactory;
});
