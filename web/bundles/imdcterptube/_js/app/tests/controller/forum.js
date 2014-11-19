define([
    'intern!object',
    'intern/chai!assert',
    'require',
    'tests/_common'
], function(registerSuite, assert, require, _common) {
    registerSuite({
        name: 'forum',

        'setup': function() {
            return _common.login(this.remote, "test", "test");
        },

        'new': function() {
            this.timeout = _common.PAGE_LOAD_TIMEOUT;

            return this.remote
                .setPageLoadTimeout(_common.PAGE_LOAD_TIMEOUT)
                .get(require.toUrl(_common.BASE_URL + '/forum/new'))
                .getPageTitle()
                .then(function (text) {
                    assert.match(text, /^Create a new Forum/,
                        'title matches');
                })
                .execute(_common.stripUI)
                .findByCssSelector('form .mediachooser-select')
                .then(_common.moveIntoView(this.remote))
                    .click()
                .end()
                .setFindTimeout(_common.PAGE_LOAD_TIMEOUT)
                .findByCssSelector('.mediachooser-container-select .select-button')
                    .click()
                .end()
                .setFindTimeout(0)
                .findByCssSelector('form')
                .then(_common.moveIntoView(this.remote))
                    .findByCssSelector('input[name*="[titleText]"]')
                        .click()
                        .type('test:new')
                    .end()
                    /*.findByCssSelector('.forum-submit')
                        .click()
                    .end()*/
                .submit()
                .end()
                //.sleep(_common.PAGE_LOAD_TIMEOUT)
                .getPageTitle()
                .then(function (text) {
                    assert.match(text, /^test:new/,
                        'title matches');
                })
                .findAllByCssSelector('body > div.container-fluid > div:nth-child(2) > div > div > div.col-lg-offset-1.col-lg-11 > div > div > div:nth-child(2) > div:nth-child(2) [src^="/uploads/media/"]')
                .then(function(elements) {
                    assert.strictEqual(elements.length, 1,
                        'media count is strictly equal');
                })
        },

        'edit': function() {
            this.timeout = _common.PAGE_LOAD_TIMEOUT * 2;

            return this.remote
                .setPageLoadTimeout(_common.PAGE_LOAD_TIMEOUT)
                .get(require.toUrl(_common.BASE_URL + '/forum/26/edit'))
                .getPageTitle()
                .then(function (text) {
                    assert.match(text, /^Edit Forum/,
                        'title matches');
                })
                .execute(_common.stripUI)
                .sleep(_common.PAGE_LOAD_TIMEOUT)
                .findByCssSelector('form')
                    .findByCssSelector('.mediachooser-selected-media')
                    .then(_common.moveIntoView(this.remote))
                        .findAllByCssSelector('.mediachooser-remove')
                            .click()
                        .end()
                    .end()
                    .findByCssSelector('.mediachooser-select')
                    .then(_common.moveIntoView(this.remote))
                        .click()
                    .end()
                .end()
                .setFindTimeout(_common.PAGE_LOAD_TIMEOUT)
                .findByCssSelector('.mediachooser-container-select .select-button[data-val="4"]')
                    .click()
                .end()
                .setFindTimeout(0)
                .findByCssSelector('form')
                    .findByCssSelector('input[name*="[titleText]"]')
                    .then(_common.moveIntoView(this.remote))
                        .click()
                        .clearValue()
                        .type('test:edit')
                    .end()
                    /*.findByCssSelector('.forum-submit')
                    .then(_common.moveIntoView(this.remote))
                        .click()
                    .end()*/
                .submit()
                .end()
                //.sleep(_common.PAGE_LOAD_TIMEOUT)
                .getPageTitle()
                .then(function (text) {
                    assert.match(text, /^test:edit/,
                        'title matches');
                })
                .findAllByCssSelector('body > div.container-fluid > div:nth-child(2) > div > div > div.col-lg-offset-1.col-lg-11 > div > div > div:nth-child(2) > div:nth-child(2) [src^="/uploads/media/"]')
                .then(function(elements) {
                    assert.strictEqual(elements.length, 2,
                        'media count is strictly equal');
                })
        }
    });
});
