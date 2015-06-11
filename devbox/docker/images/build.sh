#!/bin/sh

docker build --tag      mixcom/devbox-base            base/

docker build --tag      mixcom/devbox-php-base        php/base/

VERSIONS="5.6 5.5"
for VERSION in $VERSIONS; do
  docker build --tag    "mixcom/devbox-php-versioned" "php/v$VERSION/"
  docker build --tag    "mixcom/devbox-php-$VERSION"  "php/target/"
done

docker build --tag      mixcom/devbox-php-5.3         php/v5.3-experimental/

docker build --tag      mixcom/devbox-mariadb-10.0    mariadb-10.0/
docker build --tag      mixcom/devbox-apache-2.4      apache-2.4/
