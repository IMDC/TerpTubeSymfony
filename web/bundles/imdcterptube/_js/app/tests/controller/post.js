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
            this.timeout = _common.PAGE_LOAD_TIMEOUT * 4;

            return this.remote
                .setPageLoadTimeout(_common.PAGE_LOAD_TIMEOUT)
                .get(require.toUrl(_common.BASE_URL + '/thread/2'))
                .getPageTitle()
                .then(function (text) {
                    assert.strictEqual(text, 'testing testing | TerpTube',
                        'title does not match');
                })
                .execute(function() {
                    $.fx.off = true;
                    $('.modal.fade').removeClass('fade');
                    $('.tt-navbar-bottom').remove();
                    $('.sf-toolbar').remove();
                })
                .findByCssSelector('.post-container[data-pid="-1"]')
                .then(_common.moveIntoView(this.remote))
                    .findByCssSelector('.mediachooser-select')
                        .click()
                    .end()
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
                    .findByCssSelector('.post-submit')
                        .click()
                    .end()
                .end()
                .sleep(_common.PAGE_LOAD_TIMEOUT)
                .findAllByCssSelector('.post-container-view > div:nth-child(2)')
                .then(function(elements, setContext) {
                    setContext(elements.slice(-1)[0]);
                })
                    .findAllByCssSelector('[src^="/uploads/media/"]')
                    .then(function(elements) {
                        assert.strictEqual(elements.length, 1,
                            'post media not found');
                    })
                .end()
                .findByCssSelector('div:nth-child(2) > p')
                .getVisibleText()
                .then(function(text) {
                    assert.strictEqual(text, 'test:new',
                        'post content not found');
                });
        }
    });
});
