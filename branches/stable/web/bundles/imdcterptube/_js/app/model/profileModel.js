define([
    'model/model',
    'extra'
], function (Model) {
    'use strict';

    var ProfileModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);
    };

    ProfileModel.extend(Model);

    return ProfileModel;
});
