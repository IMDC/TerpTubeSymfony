#!/bin/bash

domain='terptube.devserv.net'

mkdir -p test/exports

curl 'https://'${domain}'/app_dev.php/js/routing?callback=fos.Router.setData' --insecure | sed -E 's/"base_url":"(.+)"/"base_url":"https:\/\/'${domain}'\1"/g' > test/exports/fos_routes.js
curl 'https://'${domain}'/app_dev.php/translations/IMDCTerpTubeBundle' --insecure -o test/exports/bazinga_translations.js
