#!/bin/bash

export VAGRANT_HOME=/home/vagrant
export TERPTUBE_HOME=$VAGRANT_HOME/dev-work/workspace/terptube/trunk

SVR_USERNAME=user
MYSQL_PASS=testtest
DB_USER_PASS=FztmbMuUREfAJdWv

# package sources
## chrome
wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | sudo apt-key add - && \
sudo sh -c 'echo "deb http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google.list'

## java
sudo add-apt-repository -y ppa:webupd8team/java

## rabbitmq
wget -q -O - https://www.rabbitmq.com/rabbitmq-signing-key-public.asc | sudo apt-key add - && \
sudo sh -c 'echo "deb http://www.rabbitmq.com/debian/ testing main" >> /etc/apt/sources.list.d/rabbitmq.list'


# apt config
## java
echo debconf shared/accepted-oracle-license-v1-1 select true | sudo debconf-set-selections && \
echo debconf shared/accepted-oracle-license-v1-1 seen true | sudo debconf-set-selections

## mysql
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password '$MYSQL_PASS && \
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password '$MYSQL_PASS


# install
## apt
sudo apt-get update && \
# unity-lens-applications: for application listings in the dash
# install desktop on its own
sudo apt-get -y --no-install-recommends install ubuntu-desktop gnome-terminal unity-lens-applications && \
### common: build-essential ...
### chrome: google-chrome-stable
### java: oracle-java7-installer
### rabbitmq: rabbitmq-server
### nginx: nginx ...
### mysql: mysql-server
### sass: ruby ...
### scripts: npm
sudo apt-get -y install \
  build-essential \
  git \
  subversion \
  htop \
  google-chrome-stable \
  oracle-java7-installer \
  rabbitmq-server \
  nginx \
  php5-fpm \
  php5-cli \
  php5-mysql \
  php5-curl \
  php5-gd \
  mysql-server \
  ruby ruby-dev \
  npm && \
apt-get clean

## web
### ffmpeg
wget -q http://johnvansickle.com/ffmpeg/releases/ffmpeg-release-64bit-static.tar.xz && \
tar -xf ffmpeg-*.tar.xz && \
sudo cp ffmpeg-*/ffmpeg /usr/local/bin/ && \
sudo cp ffmpeg-*/ffprobe /usr/local/bin/ && \
rm -rf ffmpeg-*

### composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

### sass
sudo gem install bundler

### scripts
sudo ln -s /usr/bin/nodejs /usr/bin/node && \
sudo npm install bower -g && \
sudo npm install gulp -g

### intellij
wget -q https://download.jetbrains.com/idea/ideaIU-14.1.4.tar.gz && \
tar -xf ideaIU-*.tar.gz && \
mkdir _local/ && \
mv idea-IU-*/ _local/
rm ideaIU-*.tar.gz


# config
# disable unity-lens online search
gsettings set com.canonical.Unity.Lenses remote-content-search none

## java
sudo apt-get -y install oracle-java7-set-default

## rabbitmq
sudo rabbitmq-plugins enable rabbitmq_management rabbitmq_web_stomp && \
sudo rabbitmqctl add_user test test && \
sudo rabbitmqctl set_user_tags test administrator && \
sudo rabbitmqctl set_permissions test ".*" ".*" ".*"

## nginx
cd /etc/nginx/ && \
sudo cp -r /vagrant/provision/ssl ssl && \
sudo cp /vagrant/provision/terptube sites-available/ && \
sudo chmod -R 644 ssl/* sites-available/terptube && \
sudo sed -i 's/root .*/root '$(echo $TERPTUBE_HOME/web | sed -e 's/\//\\\//g')';/g' sites-available/terptube && \
sudo ln -s /etc/nginx/sites-available/terptube sites-enabled/terptube && \
inplace='s/;date.timezone =.*/date.timezone = America\/Toronto/g' && \
sudo sed -i '''$inplace''' /etc/php5/cli/php.ini && \
sudo sed -i '''$inplace''' /etc/php5/fpm/php.ini && \
sudo sed -i 's/post_max_size = .*/post_max_size = 1G/g' /etc/php5/fpm/php.ini && \
sudo sed -i 's/upload_max_filesize = .*/upload_max_filesize = 1G/g' /etc/php5/fpm/php.ini

## mysql
sudo sed -i 's/bind-address\(.*\)= 127.0.0.1/bind-address\1= 0.0.0.0/g' /etc/mysql/my.cnf

## composer
# composer config -g github-oauth.github.com <key>


# workspace
## source
mkdir -p ~/.ssh/ && \
ssh-keyscan imdc.ca >> ~/.ssh/known_hosts && \
cp /vagrant/provision/keys/id_rsa ~/.ssh/ && \
chmod 600 ~/.ssh/id_rsa && \
cd $VAGRANT_HOME && \
mkdir -p dev-work/workspace/terptube/trunk/ && \
svn checkout svn+ssh://$SVR_USERNAME@imdc.ca/var/svn/terptube-symfony/trunk/ dev-work/workspace/terptube/trunk/ && \
rm ~/.ssh/id_rsa

## composer
cd $TERPTUBE_HOME && \
composer update

## mysql
cd $TERPTUBE_HOME && \
printf "CREATE SCHEMA \`terptube_symfony\` DEFAULT CHARACTER SET utf8 ;
CREATE USER 'symfonyuser'@'localhost' IDENTIFIED BY '"$DB_USER_PASS"' ;
GRANT ALL PRIVILEGES ON \`terptube_symfony\`.* TO 'symfonyuser'@'localhost' ;
UPDATE mysql.user SET host = '%%' WHERE user = 'root' AND host = 'localhost' ;
FLUSH PRIVILEGES ;" | mysql -uroot -p$MYSQL_PASS && \
php app/console doctrine:schema:create # && \
# php app/console doctrine:fixtures:load

## sass
cd $TERPTUBE_HOME/web/bundles/imdcterptube/_css/ && \
bundle install

## scripts
cd $TERPTUBE_HOME && \
npm install && \
bower install --allow-root && \
cd web/bundles/imdcterptube/_js/app/ && \
npm install && \
bower install --allow-root
cd $TERPTUBE_HOME && \
cd web/bundles/imdcterptube/ && \
gulp build

## intellij
cd $VAGRANT_HOME && \
mkdir -p .IntelliJIdea14/config/ && \
cp /vagrant/provision/intellij/idea14.key .IntelliJIdea14/config/idea14.key && \
cp /vagrant/provision/intellij/settings.jar .IntelliJIdea14/config/settings.jar

## perms
sh /vagrant/provision/perms.sh

## misc
sudo sh -c 'printf "\nfs.inotify.max_user_watches = 524288" >> /etc/sysctl.conf'
sudo sysctl -p
