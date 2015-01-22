define(function () {
    'use strict';

    var ForumFactory = {};

    ForumFactory.delete = function (model) {
        var settings = {
            url: Routing.generate('imdc_forum_delete', {forumid: model.get('id')}),
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

    return ForumFactory;
});
