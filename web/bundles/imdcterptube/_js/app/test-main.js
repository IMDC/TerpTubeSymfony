var allTestFiles = [];
var TEST_REGEXP = /(spec|test)\.js$/i;

var pathToModule = function(path) {
  return path.replace(/^\/base\//, '').replace(/\.js$/, '');
};

Object.keys(window.__karma__.files).forEach(function(file) {
  if (TEST_REGEXP.test(file)) {
    // Normalize paths to RequireJS module names.
    allTestFiles.push(pathToModule(file));
  }
});

require.config({
  // Karma serves files under /base, which is the basePath from your config file
  baseUrl: '/base',

    paths: {
        dust: 'lib/dust-full.min',
        'dust-helpers': 'lib/dust-helpers.min',
        underscore: 'lib/underscore-min',
        templates: 'template/templates',
        extra: 'lib/extra',
        // test libs
        fos_router: 'test/lib/router',
        fos_routes: 'test/exports/fos_routes',
        bazinga_translator: 'test/lib/translator.min',
        bazinga_translations: 'test/exports/bazinga_translations',
        chai: 'test/lib/chai',
        jquery: 'test/lib/jquery.min',
        'jquery-mockjax': 'test/lib/jquery.mockjax',
        'es5-shim': 'test/lib/es5-shim.min'
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
        // test libs
        fos_router: {
            exports: 'Routing'
        },
        fos_routes: {
            deps: ['fos_router']
        },
        bazinga_translator: {
            exports: 'Translator'
        },
        bazinga_translations: {
            deps: ['bazinga_translator']
        },
        chai: {
            exports: 'chai.assert'
        },
        jquery: {
            exports: '$'
        },
        'jquery-mockjax': {
            deps: ['jquery']
        }/*,
        main: {
            deps: ['templates', 'extra', 'fos_routes', 'bazinga_translations']
        }*/
    },

  // dynamically load all test files
  deps: allTestFiles,

  // we have to kickoff jasmine, as it is asynchronous
  callback: window.__karma__.start
});
