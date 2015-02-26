define([
    'model/model',
    'extra'
], function (Model) {
    'use strict';

    var PostModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);

        this.data.keyPoint = null;
    };

    PostModel.extend(Model);

    PostModel.prototype.isNew = function () {
        return String(this.data.id).substr(0, 1) === '0';
    };

    return PostModel;
});
