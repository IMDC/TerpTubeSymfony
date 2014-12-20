requirejs.config({
    baseUrl: '/bundles/imdcterptube/_js/app',
    paths: {
        dust: 'lib/dust-full.min',
        'dust-helpers': 'lib/dust-helpers.min',
        underscore: 'lib/underscore-min',
        templates: 'template/templates',
        extra: 'lib/extra'
    },
    shim: {
        'dust-helpers': {
            deps: ['dust']
        },
        underscore: {
            exports: '_'
        },
        templates: {
            deps: ['dust-helpers']
        },
        main: {
            deps: ['templates', 'extra']
        }
    }
});

require(['main']);
