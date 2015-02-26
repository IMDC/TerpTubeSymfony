define([
    'model/model',
    'extra'
], function (Model) {
    'use strict';

    var GroupModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);
    };

    GroupModel.extend(Model);

    return GroupModel;
});
