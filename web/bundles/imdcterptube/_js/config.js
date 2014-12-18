requirejs.config({
    baseUrl: '/bundles/imdcterptube/_js/app',
    paths: {
        dust: 'lib/dust-full.min',
        'dust-helpers': 'lib/dust-helpers.min',
        templates: 'template/templates',
        extra: 'lib/extra'
    },
    shim: {
        'dust-helpers': {
            deps: ['dust']
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
