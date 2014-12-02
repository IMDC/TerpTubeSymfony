requirejs.config({
    baseUrl: '/bundles/imdcterptube/_js/app',
    paths: {
        templates: 'views/templates',
        extra: 'lib/extra'
    },
    shim: {
        main: {
            deps: ['templates', 'extra']
        }
    }
});

require(['main']);
