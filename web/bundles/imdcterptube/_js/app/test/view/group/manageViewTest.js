var common = require('../test/common_casperjs');

var userIds = [13, 14];

casper.on('page.initialized', function () {
    this.page.injectJs('test/lib/es5-shim.min.js');
});

casper.test.setUp(function (done) {
    common.login(done);
});

casper.test.begin('manageView tests', 1 + (2 * (userIds.length)), function suite(test) {
    casper
        .start('https://terptube.devserv.net/app_dev.php/group/4/manage', function () {
            test.comment('update search form');

            this.click('[href="#tabCommunity"]');
            test.assertField('user_group_manage_search[active_tab]', '#tabCommunity');
        });

    casper
        .then(function () {
            test.comment('add members');

            this.click('[href="#tabCommunity"]');
            for (var index in userIds) {
                this.click('#tabCommunity input[type="checkbox"][data-uid="' + userIds[index] + '"]');
            }
            this.click('button.group-add');
        })
        .waitForSelector('#tabMembers input[type="checkbox"][data-uid="' + userIds[0] + '"]', function () {
            for (var index in userIds) {
                test.assertExists('#tabMembers input[type="checkbox"][data-uid="' + userIds[index] + '"]');
            }
        }, function () {
            test.fail('added members not found');
        }, 20000);

    casper
        .then(function () {
            test.comment('remove members');

            this.click('[href="#tabMembers"]');
            for (var index in userIds) {
                this.click('#tabMembers input[type="checkbox"][data-uid="' + userIds[index] + '"]');
            }
            this.click('button.group-remove');
        })
        .waitForSelector('#tabMembers input[type="checkbox"][data-uid="' + userIds[0] + '"]', function () {
            for (var index in userIds) {
                test.assertExists('#tabCommunity input[type="checkbox"][data-uid="' + userIds[index] + '"]');
            }
        }, function () {
            test.fail('removed members not found');
        }, 20000);

    casper
        .run(function () {
            test.done();
        });
});
