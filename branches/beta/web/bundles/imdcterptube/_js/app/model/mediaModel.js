define([
    'model/model',
    'extra'
], function (Model) {
    'use strict';

    var MediaModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);
    };

    MediaModel.extend(Model);

    return MediaModel;
});
