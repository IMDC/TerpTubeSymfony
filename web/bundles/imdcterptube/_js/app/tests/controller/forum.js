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
                    assert.strictEqual(text, 'Create a new Forum | TerpTube',
                        'title does not match');
                })
        }
    });
});
