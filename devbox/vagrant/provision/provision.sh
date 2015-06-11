#!/bin/sh
if grep -q 2375 /etc/default/docker; then
  # nothing
  true
else
  echo "DOCKER_OPTS=\"-H unix:///var/run/docker.sock -H tcp://0.0.0.0:2375 \${DOCKER_OPTS}\"" >> /etc/default/docker
  service docker restart
fi
