define(function () {
    'use strict';

    var Profile = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Profile.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    Profile.TAG = 'Profile';

    Profile.prototype.onViewLoaded = function () {

    };

    return Profile;
});
