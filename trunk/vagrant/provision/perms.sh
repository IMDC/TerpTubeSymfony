#!/bin/bash

export VAGRANT_HOME=/home/vagrant
export TERPTUBE_HOME=$VAGRANT_HOME/dev-work/workspace/terptube/trunk

cd $VAGRANT_HOME && \
sudo chown -R vagrant:vagrant ./ && \
cd $TERPTUBE_HOME && \
rm -rf app/cache/* app/logs/* && \
sudo chown -R `whoami`:www-data app/cache app/logs && \
chmod -R 775 app/cache app/logs && \
mkdir -p web/uploads/media/thumbnails/ && \
chmod 777 web/uploads/media/ web/uploads/media/thumbnails/
