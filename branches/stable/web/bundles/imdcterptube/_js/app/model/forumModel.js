define([
    'model/model',
    'extra'
], function (Model) {
    'use strict';

    var ForumModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);
    };

    ForumModel.extend(Model);

    return ForumModel;
});
