define(['model/model', 'extra', 'underscore'], function (Model) {
    'use strict';

    var ThreadModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);

        this.data.keyPoints = [];
    };

    ThreadModel.extend(Model);

    ThreadModel.prototype.addKeyPoint = function (keyPoint) {
        this.removeKeyPoint(keyPoint.id);

        this.data.keyPoints.push(keyPoint);
        this._dispatch(Model.Event.CHANGE, 'keyPoints.' + (this.data.keyPoints.length - 1));
    };

    ThreadModel.prototype._findKeyPoint = function (keyPointId) {
        for (var index in this.data.keyPoints) {
            if (this.data.keyPoints[index].id == keyPointId) {
                return index;
            }
        }
    };

    ThreadModel.prototype.setKeyPointProperty = function (keyPointId, keyPath, value, doDispatch) {
        var index = this._findKeyPoint(keyPointId);
        if (index) {
            this.set('keyPoints.' + index + '.' + keyPath, value, doDispatch);
        }
    };

    ThreadModel.prototype.removeKeyPoint = function (keyPointId) {
        var index = this._findKeyPoint(keyPointId);
        if (index) {
            this.data.keyPoints.splice(index, 1);
            this._dispatch(Model.Event.CHANGE, 'keyPoints');
        }
    };

    ThreadModel.prototype.forceChangeKeyPoint = function (keyPointId, keyPath) {
        var index = this._findKeyPoint(keyPointId);
        if (index) {
            this.forceChange('keyPoints.' + index + '.' + keyPath);
        }
    };

    return ThreadModel;
});
