#!/bin/bash

libs=(
    'dustjs-linkedin/dist/dust-full.min.js'
    'dustjs-linkedin-helpers/dist/dust-helpers.min.js'
    'underscore/underscore-min.js'
    'underscore/underscore-min.map'
)

libs_test=(
    '../../../../fosjsrouting/js/router.js'
    '../../../../bazingajstranslation/js/translator.min.js'
    'chai/chai.js'
    'jquery/dist/jquery.min.js'
    'jquery-mockjax/jquery.mockjax.js'
    'es5-shim/es5-shim.min.js'
    'es5-shim/es5-shim.map'
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
    for l in ${libs_test[@]}
    do
        echo 'bower_components/'${l}' > test/lib/'
        cp 'bower_components/'${l} 'test/lib/'
    done
fi
