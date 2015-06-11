# Insert cron lines here
# These will be run in the devbox-php container, so you can use drush, mysql etc.
# 
# Follow the normal cron.d syntax, which is normal cron syntax with a username 
# in front of the command.
# 
# You can usually just run jobs as devbox or as root.
# 
# Example:
# * * * * * devbox [cmd]
# 
# Don't forget to put a line break after your last line, or cron will reject 
# this file!
# 
# After changing this file, you need to restart the Docker containers:
# - devbox/docker/run if you use Docker directly
# - vagrant provision otherwise
#

