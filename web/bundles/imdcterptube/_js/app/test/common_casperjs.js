var Common = {};

Common.login = function (done) {
    casper
        .start('https://terptube.devserv.net/app_dev.php/login', function () {
            this.echo('logging in', 'INFO');

            this.fill('form', {
                _username: 'test',
                _password: 'test'
            }, true);
        })
        .then(function () {
            this.evaluateOrDie(function () {
                return document.title.match(/Home.*/);
            }, 'title should match');

            this.echo('logged in', 'INFO');
        })
        .run(done);
};

module.exports = Common;
