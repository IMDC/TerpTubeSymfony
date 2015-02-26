define([
    'factory/contactFactory'
], function (ContactFactory) {
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

    Contact.prototype.delete = function (userIds, contactList) {
        return ContactFactory.delete(userIds, contactList)
            .done(function (data) {
                window.location.reload(true);
            });
    };

    return Contact;
});
