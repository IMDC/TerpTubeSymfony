#!/bin/bash

libs=(
    'dustjs-linkedin/dist/dust-full.min.js'
    'dustjs-linkedin-helpers/dist/dust-helpers.min.js'
    'underscore/underscore-min.js'
    'underscore/underscore-min.map'
    'Sortable/Sortable.min.js'
    'sockjs-client/dist/sockjs.min.js'
    'stomp-websocket/lib/stomp.min.js'
)

libs_test_bower=(
    '../../../../fosjsrouting/js/router.js'
    '../../../../bazingajstranslation/js/translator.min.js'
    'jquery/dist/jquery.min.js'
    'jquery-mockjax/jquery.mockjax.js'
    'es5-shim/es5-shim.min.js'
    'es5-shim/es5-shim.map'
)

libs_test_npm=(
    'q/q.js'
)

mkdir -p lib
mkdir -p test/lib

for l in ${libs[@]}
do
    echo 'bower_components/'${l}' > lib/'
    cp 'bower_components/'${l} 'lib/'
done

if [ "$1" = "test" ]
then
    for l in ${libs_test_bower[@]}
    do
        echo 'bower_components/'${l}' > test/lib/'
        cp 'bower_components/'${l} 'test/lib/'
    done

    for l in ${libs_test_npm[@]}
    do
        echo 'node_modules/'${l}' > test/lib/'
        cp 'node_modules/'${l} 'test/lib/'
    done
fi
