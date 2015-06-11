#!/bin/sh

VERSIONS="5.3 5.5 5.6"
for VERSION in $VERSIONS; do
  docker push "mixcom/devbox-php-$VERSION"
done

docker push mixcom/devbox-mariadb-10.0
docker push mixcom/devbox-apache-2.4
