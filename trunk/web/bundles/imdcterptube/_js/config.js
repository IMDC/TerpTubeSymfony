requirejs.config({
    baseUrl: '/bundles/imdcterptube/_js/app',
    paths: {
        underscore: 'lib/underscore-min',
        dust: 'lib/dust-core.min',
        'dust-helpers': 'lib/dust-helpers.min',
        Sortable: 'lib/Sortable.min',
        sockjs: 'lib/sockjs.min',
        stomp: 'lib/stomp.min',
        templates: 'lib/templates.min',
        extra: 'lib/extra'
    },
    shim: {
        'dust-helpers': {
            deps: ['dust']
        },
        templates: {
            deps: ['dust-helpers']
        },
        Sortable: {
            exports: 'Sortable'
        },
        stomp: {
            exports: 'Stomp'
        },
        main: {
            deps: ['templates', 'extra']
        }
    }
});

require(['main']);
