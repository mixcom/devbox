FROM mixcom/devbox-php-versioned
MAINTAINER Sebastiaan Besselsen <sebastiaan.besselsen@gmail.com>

RUN DEBIAN_FRONTEND=noninteractive apt-get -y install php5 php5-mhash php5-mcrypt php5-curl php5-cli php5-mysql php5-gd php5-intl php-pear php5-dev php5-fpm php5-memcache php5-xdebug php5-imagick

RUN yes | pecl install yaml
RUN echo "extension=yaml.so" >> /etc/php5/mods-available/yaml.ini
RUN php5enmod yaml

RUN rm /etc/php5/fpm/php-fpm.conf
ADD php-fpm.conf /etc/php5/fpm/php-fpm.conf

RUN rm /etc/php5/fpm/pool.d/www.conf
ADD www.conf /etc/php5/fpm/pool.d/www.conf

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

RUN composer global require drush/drush:6.*

ADD php.ini /etc/php5/mods-available/devbox.ini
RUN php5enmod devbox
