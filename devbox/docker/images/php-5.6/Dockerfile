FROM mixcom/devbox-base
MAINTAINER Sebastiaan Besselsen <sebastiaan.besselsen@gmail.com>

RUN LANG=C.UTF-8 add-apt-repository ppa:ondrej/php5-5.6
RUN apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0xcbcb082a1bb943db
RUN add-apt-repository 'deb http://mirror.stshosting.co.uk/mariadb/repo/10.0/ubuntu trusty main'
RUN apt-get -y update

RUN DEBIAN_FRONTEND=noninteractive apt-get -y install libcurl4-openssl-dev libcurl3 libyaml-dev php5 php5-mhash php5-mcrypt php5-curl php5-cli php5-mysql php5-gd php5-intl php-pear php5-dev php5-fpm php5-memcache php5-xdebug mariadb-client-10.0 ruby1.9.1-dev libsqlite3-dev

RUN yes | pecl install yaml
RUN echo "extension=yaml.so" >> /etc/php5/mods-available/yaml.ini
RUN php5enmod yaml

RUN rm /etc/php5/fpm/php-fpm.conf
ADD php-fpm.conf /etc/php5/fpm/php-fpm.conf

RUN rm /etc/php5/fpm/pool.d/www.conf
ADD www.conf /etc/php5/fpm/pool.d/www.conf

RUN mkdir /etc/service/php-fpm
ADD php-fpm.runit /etc/service/php-fpm/run
ADD php-fpm-check.runit /etc/service/php-fpm/check

RUN mkdir /etc/service/devbox-install
ADD devbox-install.runit /etc/service/devbox-install/run

RUN gem install mailcatcher --no-rdoc --no-ri

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

RUN composer global require drush/drush:6.*
ADD bashrc /root/.bashrc

ADD php.ini /etc/php5/mods-available/devbox.ini
RUN php5enmod devbox

VOLUME /devbox
VOLUME /var/www/sites

EXPOSE 9000