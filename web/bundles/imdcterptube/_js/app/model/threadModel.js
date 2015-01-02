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

    ThreadModel.prototype.setKeyPointProperty = function (keyPointId, keyPath, value, doDispatch) {
        for (var index in this.data.keyPoints) {
            if (this.data.keyPoints[index].id == keyPointId) {
                this.set('keyPoints.' + index + '.' + keyPath, value, doDispatch);
                return;
            }
        }
    };

    ThreadModel.prototype.removeKeyPoint = function (keyPointId) {
        for (var index in this.data.keyPoints) {
            if (this.data.keyPoints[index].id == keyPointId) {
                this.data.keyPoints.splice(index, 1);
                this._dispatch(Model.Event.CHANGE, 'keyPoints');
                return;
            }
        }
    };

    return ThreadModel;
});
