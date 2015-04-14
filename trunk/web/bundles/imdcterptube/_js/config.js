requirejs.config({
    baseUrl: '/bundles/imdcterptube/_js/app',
    paths: {
        dust: 'lib/dust-full.min',
        'dust-helpers': 'lib/dust-helpers.min',
        underscore: 'lib/underscore-min',
        Sortable: 'lib/Sortable.min',
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
        Sortable: {
            exports: 'Sortable'
        },
        templates: {
            deps: ['dust-helpers']
        },
        main: {
            deps: ['underscore', 'templates', 'extra']
        }
    }
});

require(['main']);
