FROM mixcom/devbox-base
MAINTAINER Sebastiaan Besselsen <sebastiaan.besselsen@gmail.com>

VOLUME /var/www/sites
VOLUME /var/www/web-ui
EXPOSE 80

RUN add-apt-repository "deb http://archive.ubuntu.com/ubuntu trusty multiverse"
RUN add-apt-repository "deb http://archive.ubuntu.com/ubuntu trusty-updates multiverse"
RUN add-apt-repository "deb http://security.ubuntu.com/ubuntu trusty-security multiverse"
RUN apt-get update

RUN apt-get install -y apache2 apache2-mpm-worker libapache2-mod-fastcgi
RUN a2enmod actions fastcgi alias vhost_alias rewrite

ADD devbox.conf /etc/apache2/conf-available/devbox.conf
RUN a2enconf devbox

RUN mkdir /etc/service/apache2
ADD apache2.runit /etc/service/apache2/run

ADD vhosts.conf /etc/apache2/sites-available/devbox.conf
RUN a2dissite 000-default
RUN a2ensite devbox