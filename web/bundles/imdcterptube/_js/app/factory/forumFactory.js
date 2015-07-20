define(function () {
    'use strict';

    var ForumFactory = {};

    ForumFactory.delete = function (model) {
        var deferred = $.Deferred();
        var settings = {
            method: 'DELETE',
            url: Routing.generate('imdc_delete_forum', {forumId: model.get('id')})
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

    return ForumFactory;
});
