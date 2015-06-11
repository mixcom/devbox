#!/bin/sh
rsync -a /devbox/config/ssh-keys/ /root/.ssh

if [ -f /devbox/config/php.ini ]; then
  cp /devbox/config/php.ini /etc/php.d/zz-devbox-user.ini
fi

exec /usr/sbin/php-fpm --nodaemonize
