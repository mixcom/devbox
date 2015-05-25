#!/bin/sh

docker build --tag      mixcom/devbox-base          base/
docker build --tag      mixcom/devbox-php-5.6       php-5.6/
docker build --tag      mixcom/devbox-mariadb-10.0  mariadb-10.0/
docker build --tag      mixcom/devbox-apache-2.4    apache-2.4/
