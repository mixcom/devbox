FROM mixcom/devbox-base
MAINTAINER Sebastiaan Besselsen <sebastiaan.besselsen@gmail.com>

RUN apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0xcbcb082a1bb943db
RUN add-apt-repository 'deb http://mirror.stshosting.co.uk/mariadb/repo/10.0/ubuntu trusty main'
RUN apt-get -y update

RUN DEBIAN_FRONTEND=noninteractive apt-get -y install mariadb-server-10.0

RUN rm -rf /var/lib/mysql

RUN mkdir /etc/service/mysql
ADD mysql.runit /etc/service/mysql/run
ADD mysql-check.runit /etc/service/mysql/check

RUN mkdir /etc/service/mysql-init
ADD mysql-init.runit /etc/service/mysql-init/run
ADD security.sql /etc/service/mysql-init/security.sql

ADD mysql.cnf /etc/mysql/conf.d/devbox.cnf
RUN chmod 644 /etc/mysql/conf.d/devbox.cnf

EXPOSE 3306
