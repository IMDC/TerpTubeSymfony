define(function () {
    'use strict';

    var MyFilesController = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', MyFilesController.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    MyFilesController.TAG = 'MyFiles';

    MyFilesController.prototype.onViewLoaded = function () {

    };

    MyFilesController.prototype.delete = function () {

    };

    return MyFilesController;
});
