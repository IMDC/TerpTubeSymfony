define([
    'intern/chai!assert'
], function(assert) {
    var _Common = function() {

    };

    _Common.BASE_URL = 'https://terptube.devserv.net/app_dev.php';
    _Common.PAGE_LOAD_TIMEOUT = 30000;

    _Common.login = function(remote, username, password) {
        return remote
            .setPageLoadTimeout(_Common.PAGE_LOAD_TIMEOUT)
            .get(require.toUrl(_Common.BASE_URL + '/login'))
            .findByCssSelector('input[name=_username]')
                .click()
                .type(username)
            .end()
            .findByCssSelector('input[name=_password]')
                .click()
                .type(password)
            .end()
            .findByCssSelector('form[action*="login_check"]')
                .submit()
            .end()
            .getPageTitle()
            .then(function(text) {
                assert.strictEqual(text, 'Home | TerpTube',
                    'title should be strictly equal');
            });
    };

    _Common.moveIntoView = function(remote) {
        return function(element) {
            remote.moveMouseTo(element);
        };
    };

    _Common.stripUI = function() {
        $.fx.off = true;
        $('.modal.fade').removeClass('fade');
        $('.tt-navbar-bottom').remove();
        $('.sf-toolbar').remove();
    };

    return _Common;
});
