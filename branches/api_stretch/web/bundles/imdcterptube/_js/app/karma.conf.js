// Karma configuration
// Generated on Fri Dec 12 2014 03:39:42 GMT-0500 (EST)

module.exports = function(config) {
  config.set({

    // base path that will be used to resolve all patterns (eg. files, exclude)
    basePath: '',


    // frameworks to use
    // available frameworks: https://npmjs.org/browse/keyword/karma-adapter
    frameworks: ['mocha', 'requirejs'],


    // list of files / patterns to load in the browser
    files: [
      'test-main.js',
      '../../js/player/player.js',
      {pattern: 'service.js', included: false},
      {pattern: 'controller/*.js', included: false},
      {pattern: 'core/*.js', included: false},
      {pattern: 'factory/*.js', included: false},
      {pattern: 'model/*.js', included: false},
      {pattern: 'service/*.js', included: false},
      {pattern: 'lib/*.js', included: false},
      {pattern: 'test/common.js', included: false},
      {pattern: 'test/exports/*.js', included: false},
      {pattern: 'test/lib/*.js', included: false},
      {pattern: 'test/**/*Test.js', included: false}
    ],


    // list of files to exclude
    exclude: [
        'test/view/**/*Test.js', // view tests are handled by CasperJS
        // exclude for faster testing
        'test/factory/contact*',
        'test/factory/forum*',
        'test/factory/group*',
        'test/factory/media*',
        'test/factory/message*',
        'test/factory/myFiles*',
        'test/factory/post*',
        'test/factory/thread*'
    ],


    // preprocess matching files before serving them to the browser
    // available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
    preprocessors: {
    },


    // test results reporter to use
    // possible values: 'dots', 'progress'
    // available reporters: https://npmjs.org/browse/keyword/karma-reporter
    reporters: ['progress'],


    // web server port
    port: 9876,


    // enable / disable colors in the output (reporters and logs)
    colors: true,


    // level of logging
    // possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
    logLevel: config.LOG_INFO,


    // enable / disable watching file and executing tests whenever any file changes
    autoWatch: true,


    // start these browsers
    // available browser launchers: https://npmjs.org/browse/keyword/karma-launcher
    browsers: ['PhantomJS2_custom'],


      customLaunchers: {
          PhantomJS_custom: {
              base: 'PhantomJS',
              flags: ['--ignore-ssl-errors=true', '--web-security=false']
          },
          PhantomJS2_custom: {
              base: 'PhantomJS2',
              flags: ['--ignore-ssl-errors=true', '--web-security=false']
          },
          Chrome_without_security: {
              base: 'Chrome',
              flags: ['--disable-web-security', '--allow-file-access-from-files']
          }
      },


      browserNoActivityTimeout: 120000,


    // Continuous Integration mode
    // if true, Karma captures browsers, runs the tests and exits
    singleRun: false
  });
};
