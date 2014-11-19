define([
    'intern!object',
    'intern/chai!assert',
    'intern/dojo/node!leadfoot/helpers/pollUntil',
    'require'
], function(registerSuite, assert, pollUntil, require) {
    var baseUrl = 'https://terptube.devserv.net/app_dev.php';
    var pageLoadTimeout = 30000;

    registerSuite({
        name: 'mediaChooser',

        'setup': function() {
            return this.remote
                .setPageLoadTimeout(pageLoadTimeout)
                .get(require.toUrl(baseUrl + '/login'))
                .findByCssSelector('input[name=_username]')
                .click()
                .type("test")
                .end()
                .findByCssSelector('input[name=_password]')
                .click()
                .type("test")
                .end()
                .findByCssSelector('form[action*="login_check"]')
                .submit()
                .getPageTitle()
                .then(function(text) {
                    assert.strictEqual(text, 'Home | TerpTube',
                        'title does not match');
                });
        },

        'threadReply': function() {
            this.timeout = pageLoadTimeout * 4;

            var remote = this.remote;
            var moveIntoView = function(element) {
                return remote.moveMouseTo(element);
            };

            return remote
                .get(require.toUrl(baseUrl + '/thread/2'))
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
                .then(moveIntoView)
                .findByCssSelector('.mediachooser-select')
                .click()
                .end()
                .end()
                .setFindTimeout(pageLoadTimeout)
                .findByCssSelector('.mediachooser-container-select .select-button')
                .click()
                .end()
                .setFindTimeout(0)
                .findByCssSelector('.post-container[data-pid="-1"]')
                .then(moveIntoView)
                .findByCssSelector('textarea[name*="[content]"]')
                .click()
                .type('test:threadReply')
                .end()
                .findByCssSelector('.post-submit')
                .click()
                .end()
                .end()
                .sleep(pageLoadTimeout)
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
                    assert.strictEqual(text, 'test:threadReply',
                        'post content not found');
                });
        }
    });
});
