define(function(require) {
    "use strict";

    var Bootstrap = function(view, model, options, services) {
        $.each(services, function(index, service) {
            if (!$tt._services[service]) {
                var _s = require('core/' + service + 'Service');
                $tt._services[service] = new _s();
            }
        });

        new view(model, options);
    };

    return Bootstrap;
});
