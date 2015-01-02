define(function (require) {
    'use strict';

    var Service = function () {

    };

    Service.get = function (name) {
        if (!$tt._services[name]) {
            var service = require('service/' + name + 'Service');
            $tt._services[name] = new service();
        }

        return $tt._services[name];
    };

    return Service;
});
