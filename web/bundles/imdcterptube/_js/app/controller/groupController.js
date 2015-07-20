define([
    'factory/groupFactory'
], function (GroupFactory) {
    'use strict';

    var Group = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Group.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    Group.TAG = 'Group';

    Group.prototype.onViewLoaded = function () {

    };

    Group.prototype.delete = function () {
        return GroupFactory.delete(this.model)
            .done(function (data) {
                window.location.assign(data.redirect_url);
            });
    };

    return Group;
});
