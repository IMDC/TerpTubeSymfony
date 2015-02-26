define(function () {
    'use strict';

    var Common = {};

    Common.SCHEME_HOST = 'https://terptube.devserv.net';
    Common.BASE_URL = Common.SCHEME_HOST + '/app_dev.php';
    Common.PAGE_LOAD_TIMEOUT = 20000;

    Common.ajaxSetup = function () {
        $.ajaxSetup({
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        });
    };

    Common.login = function (done) {
        console.log('logging in');

        $.ajax({
            url: Common.BASE_URL + '/login'
        })
            .then(function(data, textStatus, jqXHR) {
                console.log('got login form');

                var form = $(data).find('form').eq(0);
                form.find('input[name=_username]').attr('value', 'test');
                form.find('input[name=_password]').attr('value', 'test');

                $.ajax({
                    url: Common.SCHEME_HOST + form.attr('action'),
                    type: 'POST',
                    contentType: false,
                    data: new FormData(form[0]),
                    processData: false
                })
                    .then(function(data, textStatus, jqXHR) {
                        console.log('logged in');
                    },
                    function(jqXHR, textStatus, errorThrown) {
                        console.log('login failed');
                        console.log(jqXHR.responseText);
                    })
                    .always(function() {
                        done();
                    });
            },
            function(jqXHR, textStatus, errorThrown) {
                console.log('get login form failed');
                console.log(jqXHR.responseText);
                done();
            });
    };

    return Common;
});
