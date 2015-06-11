FROM mixcom/devbox-base
MAINTAINER Sebastiaan Besselsen <sebastiaan.besselsen@gmail.com>

RUN apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0xcbcb082a1bb943db && \
  add-apt-repository 'deb http://mirror.stshosting.co.uk/mariadb/repo/10.0/ubuntu trusty main' && \
  apt-get -y update

RUN DEBIAN_FRONTEND=noninteractive apt-get -y install libcurl4-openssl-dev libcurl3 libyaml-dev mariadb-client-10.0 ruby1.9.1-dev libsqlite3-dev ghostscript imagemagick zip build-essential

RUN mkdir /etc/service/php-fpm
ADD php-fpm.runit /etc/service/php-fpm/run
ADD php-fpm-check.runit /etc/service/php-fpm/check

RUN mkdir /etc/service/devbox-install
ADD devbox-install.runit /etc/service/devbox-install/run

RUN gem install mailcatcher --no-rdoc --no-ri

ADD bashrc /root/.bashrc

VOLUME /devbox
VOLUME /var/www/sites

EXPOSE 9000
