# Testing

Things to do first:

* Install Node.js and npm
* Install Bower
* Read about:
    * [Karma][1]
    * [Chai.js][2]
    * [jquery-mockjax][3]

## Install dependencies

Go to `web/bundles/imdcterptube/_js/app/` and run the following:

`bower install`  
`npm install`  
`npm run-script test-prep`

## Before running tests

### Unit
#### Semi-functional

Factory tests (app/test/factory/) communicate with Symfony controllers, and test that they return the expected result. As such, the object models defined in these tests require that their identifier(s) `id` exist in the database. For example:

```javascript
// test/factory/forumFactoryTest.js
...
model = new ForumModel({
    id: 120 // this must be set to an existing forum id
});
...
```

## Running tests

**Note:** Symfony tests should be run before any JavaScript tests.

Make sure you're here: `web/bundles/imdcterptube/_js/app/`

### Unit

You can use:

`npm run-script test`

to do a single run, or:

`karma start karma.conf`

for continuous integration

#### Semi-functional

Factory tests should be disabled by default, especially when in continuous integration mode.
Enable them as needed by uncommenting them in the `exclude` property in `karma.conf.js`.

```javascript
// karma.conf.js
...
exclude: [
    // exclude for faster testing
    'test/factory/contact*',
    'test/factory/forum*',
    'test/factory/group*',
    'test/factory/post*',
    'test/factory/thread*'
],
...
```

### Functional (continuous integration with CasperJS)

`TODO`

### Functional (Selenium)

`TODO`

## Writting tests

`TODO`

## Current state of tests

[1]: http://karma-runner.github.io/0.12/index.html
[2]: http://chaijs.com/
[3]: https://github.com/jakerella/jquery-mockjax
