define([
    'factory/forumFactory'
], function (ForumFactory) {
    'use strict';

    var Forum = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Forum.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    Forum.TAG = 'Forum';

    Forum.prototype.onViewLoaded = function () {

    };

    Forum.prototype.delete = function () {
        return ForumFactory.delete(this.model)
            .done(function (data) {
                window.location.assign(data.redirectUrl);
            });
    };

    return Forum;
});
