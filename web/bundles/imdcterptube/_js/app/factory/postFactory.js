define([
    'model/postModel'
], function (PostModel) {
    'use strict';

    var PostFactory = {};

    PostFactory._prepForFormSubmit = function (form, settings) {
        settings.contentType = false;
        if (form) {
            settings.data = new FormData(form);
        }
        settings.processData = false;
    };

    PostFactory._newPost = function (model, settings, isPost) {
        var deferred = $.Deferred();

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                // don't use model.update since any new post will not remain in the caller's context
                data.post = new PostModel(data.post);
                data.post.set('form', data.form);
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                if (isPost)
                    model.set('form', jqXHR.responseJSON.form);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    PostFactory.new = function (model) {
        var settings = {
            url: Routing.generate('imdc_new_post', {
                threadId: model.get('parent_thread_id'),
                parentPostId: model.get('parent_post_id') || model.get('id')
            })
        };

        return PostFactory._newPost(model, settings, false);
    };

    PostFactory.post = function (model, form) {
        var settings = {
            method: 'POST',
            url: Routing.generate('imdc_post_post', {
                threadId: model.get('parent_thread_id'),
                parentPostId: model.get('parent_post_id') || model.get('id')
            })
        };

        PostFactory._prepForFormSubmit(form, settings);

        return PostFactory._newPost(model, settings, true);
    };

    PostFactory.get = function (model) {
        var deferred = $.Deferred();
        var settings = {
            url: Routing.generate('imdc_get_post', {postId: model.get('id')})
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                model.update(data.post);
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    PostFactory._editPut = function (model, settings, isPut) {
        var deferred = $.Deferred();

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                model.update(data.post);
                model.set('form', data.form);
                model.set('keyPoint.startTime', model.get('start_time'));
                model.set('keyPoint.endTime', model.get('end_time'));
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                if (isPut)
                    model.set('form', jqXHR.responseJSON.form);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    PostFactory.edit = function (model) {
        var settings = {
            url: Routing.generate('imdc_edit_post', {postId: model.get('id')})
        };

        return PostFactory._editPut(model, settings, false);
    };

    PostFactory.put = function (model, form) {
        var settings = {
            method: 'POST',
            url: Routing.generate('imdc_put_post', {postId: model.get('id')})
        };

        PostFactory._prepForFormSubmit(form, settings);

        return PostFactory._editPut(model, settings, true);
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
