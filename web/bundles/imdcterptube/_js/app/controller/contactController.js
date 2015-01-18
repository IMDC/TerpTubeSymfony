define(function () {
    'use strict';

    var Contact = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Contact.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    Contact.TAG = 'Contact';

    Contact.prototype.onViewLoaded = function () {

    };

    return Contact;
});
