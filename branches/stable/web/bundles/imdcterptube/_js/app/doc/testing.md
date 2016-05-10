# Testing

Things to do first:

* Install Node.js and npm globally
* Install Bower globally
* Read about:
    * [Karma][1]
    * [Chai.js][2]
    * [jquery-mockjax][3]

## Install dependencies

Go to `web/bundles/imdcterptube/_js/app/` and run the following:

`bower install`  
`npm install`  
`npm run-script test-prep`

## Running tests

Make sure you're here: `web/bundles/imdcterptube/_js/app/`

### Unit

You can use:

`npm run-script test`

to do a single run, or:

`karma start karma.conf`

for continuous integration

### Functional (continuous integration with CasperJS)

`TODO`

### Functional (Selenium)

`TODO`

## Writting tests

`TODO`

## Current state of tests

`TODO`

[1]: http://karma-runner.github.io/0.12/index.html
[2]: http://chaijs.com/
[3]: https://github.com/jakerella/jquery-mockjax
