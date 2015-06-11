FROM centos:6

RUN yum -y update && yum clean all

RUN yum install -y epel-release

RUN yum -y install git mysql php-fpm php-cli php-mbstring php-mysql php-gd php-curl php-memcache php-devel php-pear php-pecl-xdebug ghostscript ImageMagick gcc gcc-c++ g++ patch readline readline-devel zlib libyaml-devel libffi-devel bzip2 libtool bison iconv-devel make automake autoconf curl-devel openssl-devel zlib-devel httpd-devel apr-devel apr-util-devel sqlite-devel which tar libyaml-devel && yum clean all

RUN sed -e 's/127.0.0.1:9000/9000/' \
        -e '/allowed_clients/d' \
        -e '/catch_workers_output/s/^;//' \
        -e '/error_log/d' \
        -i /etc/php-fpm.d/www.conf

ADD ruby-patch.patch /root/ruby-patch.patch
ADD install-ruby.sh /root/install-ruby.sh
RUN gpg2 --keyserver hkp://keys.gnupg.net --recv-keys 409B6B1796C275462A1703113804BB82D39DC0E3 && /root/install-ruby.sh

RUN gem install mailcatcher --no-rdoc --no-ri

RUN yes | pecl install yaml
RUN echo "extension=yaml.so" >> /etc/php.d/yaml.ini

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

RUN composer global require drush/drush:6.*

ADD php.ini /etc/php.d/devbox.ini

ADD bashrc /root/.bashrc
ADD run.sh /usr/local/devbox-run.sh

VOLUME /devbox
VOLUME /var/www/sites
EXPOSE 9000
ENTRYPOINT /usr/local/devbox-run.sh
