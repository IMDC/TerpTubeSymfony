requirejs.config({
    baseUrl: '/bundles/imdcterptube/_js/app',
    paths: {
        extra: 'lib/extra'
    },
    shim: {
        main: {
            deps: ['extra']
        }
    }
});

require(['main']);
