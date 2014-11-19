define([
    'intern!object',
    'intern/chai!assert',
    'require',
    'tests/_common'
], function(registerSuite, assert, require, _common) {
    registerSuite({
        name: 'post',

        'setup': function() {
            return _common.login(this.remote, "test", "test");
        },

        'new': function() {
            this.timeout = _common.PAGE_LOAD_TIMEOUT;

            return this.remote
                .setPageLoadTimeout(_common.PAGE_LOAD_TIMEOUT)
                .get(require.toUrl(_common.BASE_URL + '/thread/2'))
                .getPageTitle()
                .then(function (text) {
                    assert.match(text, /^testing testing/,
                        'title matches');
                })
                .execute(_common.stripUI)
                .findByCssSelector('.post-container[data-pid="-1"] .mediachooser-select')
                .then(_common.moveIntoView(this.remote))
                    .click()
                .end()
                .setFindTimeout(_common.PAGE_LOAD_TIMEOUT)
                .findByCssSelector('.mediachooser-container-select .select-button')
                    .click()
                .end()
                .setFindTimeout(0)
                .findByCssSelector('.post-container[data-pid="-1"]')
                .then(_common.moveIntoView(this.remote))
                    .findByCssSelector('textarea[name*="[content]"]')
                        .click()
                        .type('test:new')
                    .end()
                    /*.findByCssSelector('.post-submit')
                        .click()
                    .end()*/
                .submit()
                .end()
                //.sleep(_common.PAGE_LOAD_TIMEOUT)
                .findAllByCssSelector('.post-container-view')
                .then(function(elements, setContext) {
                    setContext(elements.slice(-1)[0]);
                })
                    .findAllByCssSelector('[src^="/uploads/media/"]')
                    .then(function(elements) {
                        assert.strictEqual(elements.length, 1,
                            'media count is strictly equal');
                    })
                    .end()
                    .findByCssSelector('p')
                    .getVisibleText()
                    .then(function(text) {
                        assert.strictEqual(text, 'test:new',
                            'content is strictly equal');
                    });
        }
    });
});
