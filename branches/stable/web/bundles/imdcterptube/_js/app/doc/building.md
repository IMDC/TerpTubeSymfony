# Building

Things to do first:

* Install Node.js and npm globally
* Install Bower globally
* Setup Sass

## Install dependencies

Go to `web/bundles/imdcterptube/_js/app/` and run the following:

`bower install -p`  
`npm install --production`

## Building

Go to `web/bundles/imdcterptube/` and run the following:

`gulp build`

### Continuous building

During development, you can run the following for continuous automated building.

`gulp watch`
