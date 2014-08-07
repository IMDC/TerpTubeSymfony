requirejs.config({
    baseUrl: '/bundles/imdcterptube/_js/app',
    paths: {
        extra: 'lib/extra',
        dust: 'lib/dust-full-0.3.0.min'
    },
    shim: {
        main: {
            deps: ['extra', 'dust']
        }
    }
});

require(['main']);
