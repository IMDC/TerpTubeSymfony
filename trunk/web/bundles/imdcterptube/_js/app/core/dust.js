define([
    'underscore'
], function () {
    'use strict';

    var helpers = {
        generateUrl: function (chunk, context, bodies, params) {
            if (!params || !params.hasOwnProperty('path'))
                return chunk;

            // ignore full and blob urls
            if (params.path.indexOf('://') > 0 || params.path.indexOf('blob:') == 0)
                return chunk.write(params.path);

            return chunk.write(
                (Routing.getBaseUrl() + '/').replace(/\w+\.php\/$/gi, '') + params.path);
        },
        generateRouteUrl: function (chunk, context, bodies, params) {
            if (!params || !params.hasOwnProperty('name') || !params.hasOwnProperty('str_params'))
                return chunk;

            var str_params = params.str_params.split('|');
            var opt_params = {};

            str_params.forEach(function (element, index, array) {
                var keyContext = element.split(':');
                opt_params[keyContext[0]] = context.get(keyContext[1]);
            });

            return chunk.write(Routing.generate(params.name, opt_params, params.absolute));
        },
        //TODO unused. consider dropping
        sizeOf: function (chunk, context, bodies, params) {
            // pass a dummy chunk object so that @size doesn't write the resulting value,
            // so it doesn't render if bodies.block is present but its context(s) return(s) false
            var result;
            this.size({
                write: function (value) {
                    result = value;
                }
            }, context, bodies, params);
            if (bodies && bodies.block) {
                chunk.render(bodies.block, context.push({key: result}));
            } else {
                chunk = chunk.write(result);
            }
            return chunk;
        },
        isLoggedInUser: function (chunk, context, bodies, params) {
            if (!params || !params.hasOwnProperty('id') || !window.user)
                return chunk;

            var result = params.id == window.user.get('id');
            if (params.inverse)
                result = !result;
            if (bodies) {
                if (result && bodies.block)
                    chunk.render(bodies.block, context);
                if (!result && bodies.else)
                    chunk.render(bodies.else, context);
            } else {
                chunk = chunk.write(result ? 'Yes' : 'No');
            }
            return chunk;
        },
        isNotLoggedInUser: function (chunk, context, bodies, params) {
            params.inverse = true;
            return this.isLoggedInUser(chunk, context, bodies, params);
        },
        isUserOnList: function (chunk, context, bodies, params) {
            if (!params || !params.hasOwnProperty('list') || !params.hasOwnProperty('username') || !window.user)
                return chunk;

            var result = window.user['isUserOn' + params.list + 'List'](params.username);
            if (bodies) {
                if (result && bodies.block)
                    chunk.render(bodies.block, context);
                if (!result && bodies.else)
                    chunk.render(bodies.else, context);
            } else {
                chunk = chunk.write(result ? 'Yes' : 'No');
            }
            return chunk;
        }
    };

    var filters = {
        nl2br: function (value) {
            return value.replace(/(?:\r\n|\r|\n)/g, '<br />');
        },
        date: function (value) {
            var dateTime = new Date(value);
            if (_.isNaN(dateTime.getTime()))
                return '';

            var amPM = (dateTime.getHours() > 11) ? 'pm' : 'am';
            var hours = dateTime.getHours() % 12;
            var minutes = dateTime.getMinutes();
            var dateString = dateTime.toDateString();

            if (hours == 0)
                hours = 12;
            if (hours < 10)
                hours = '0' + hours;
            if (minutes < 10)
                minutes = '0' + minutes;

            dateString = dateString.substring(dateString.indexOf(' ') + 1);

            return dateString + ' ' + hours + ':' + minutes + ' ' + amPM;
        }
    };

    var Dust = function () {
    };

    Dust.inject = function () {
        _.each(helpers, function (value, key, list) {
            dust.helpers[key] = value;
        });

        _.each(filters, function (value, key, list) {
            dust.filters[key] = value;
        });
    };

    return Dust;
})
;
