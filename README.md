# Devbox

Devbox is a local PHP development environment that makes it easy to run a powerful, customizable development server on your Mac or Linux PC.

Devbox is based on [Docker](https://www.docker.com), “an open platform for developers and sysadmins of distributed applications”. This makes Devbox extremely pluggable, allowing you to run all kinds of server software locally with just a few keystrokes (and without messing up your configuration).

# Getting started

## Installing Devbox

Installing Devbox on any platform just takes a few easy steps:

* Install [VirtualBox](https://www.virtualbox.org/wiki/Downloads). This allows the Devbox virtual machine to run.
* Install [Vagrant](http://www.vagrantup.com/downloads). This will make it easy to install and run the Devbox VM.
* Clone this repository in a convenient location: `git clone ...`

Some notes:

* If you have Docker installed on Linux, and you are in an adventurous mood, check the section below on using Devbox directly with Docker.
* You can use VMWare or Parallels instead of VirtualBox, but it is not supported by our default configuration. If you understand Vagrant, it should be easy to set up.

## Starting Devbox for the first time

* Open a Terminal in the directory where you cloned this repository (the 'Devbox directory'), and enter this command to start Devbox: `vagrant up`.

Lots of stuff will happen as Vagrant installs the Devbox environment. It downloads a VM image and installs the required software packages on it. This process outputs an avalanche of debug info. Don't worry: almost everything happens within the confines of the VM, so your system stays clean and you can always cleanly delete Devbox in seconds (see below).

Installation may take between 20 and 30 minutes. You may have to enter your root password once, to allow the Devbox VM to read the Devbox directory through NFS. When Devbox is done, you will see this message:

    ==> default: Started all Docker containers.
    ==> default: Devbox is up and running.

## Using Devbox sites

Once Devbox is installed, you can start putting your PHP websites in the `sites` subdirectory of Devbox. Simply use the following directory layout:

```
sites/
    sitename1/
        private files
        public/
            files that should be accessible through the webserver
    sitename2/
        private files
        public/
            files that should be accessible through the webserver
    etc.
```

The `public` directory may also be a symlink to another local directory (like `src/`, `www/`, `html/` etc.)

Devbox can be accessed from your PC at the IP address `192.168.33.11`. If you want to open the site `monkey`, simply open the following URL in your browser:

    http://monkey.192.168.33.11.xip.io/
    
Alternatively, you may add the line `192.168.33.11 monkey.dev` to your `/etc/hosts` file, and open:

    http://monkey.dev/
    
The `sites/` path in your Devbox directory is mapped to `/var/www/sites/` within the Devbox containers.

# Devbox features

Devbox supports the following tools and server software.

## Apache

Devbox runs Apache 2.4. Apache serves all sites through a `VirtualDocumentRoot` setup. Features include:

* `.htaccess` files for configuration overrides
* `FollowSymlinks`
* `mod_rewrite`

Apache connects to PHP using FastCGI over TCP.

## PHP

Devbox runs PHP 5.6, by default, but 5.5 and 5.3 are also available (see below). Features include:

* Xdebug extension
* Memcache extension
* cURL extension
* `composer`
* `drush`
* Mailcatcher (mail sent from PHP will not arrive at its destination, but goes here: see below)

PHP is run by PHP-FPM. It can access MySQL at the hostname `mysql`, using the credentials specified below.

## MySQL

Devbox runs [MariaDB 10.0](https://mariadb.org) (a drop-in MySQL replacement). The access credentials are:

    Username: root
    Password: mixcom

MySQL can be accessed by several methods:

* From PHP at the hostname `mysql`, port `3306`
* From the Devbox management shell using the `mysql` and `mysqldump` command (e.g. `mysql -hmysql -uroot -pmixcom`)
* From your own PC at IP `192.168.33.11`, port `3306` (so you can use a MySQL GUI)
* Through phpMyAdmin (see below)

MySQL data is stored in the Devbox directory `data/mysql/`, and is persisted even when you rebuild your Devbox environment or destroy the Vagrant VM. However, you should not count on this data to be safe forever: Devbox is a development environment, not a place for long-term storage of data.

## phpMyAdmin

You can access phpMyAdmin at the following URL:

    https://192.168.33.11:8081
    
(Mind the `https`, not `http`!) The MySQL credentials can be found above.

## Mailcatcher

When you send mail from PHP, it goes to the Mailcatcher interface. This allows you to easily debug PHP mailings. Mailcatcher can be found at:

    http://192.168.33.11:1080

## Memcached

There is a memcached daemon running which can be accessed from PHP at the hostname `memcache`, port `11211`.

## Apache Solr

Apache Solr is not yet supported, but will be soon.

## Switching versions

Running another PHP version is very simple: create a file `config/versions` and add one of the following lines:

    PHP=5.3
    PHP=5.5
    PHP=5.6

Then run `vagrant provision` or `devbox/docker/run` to activate the selected version.

If the versions file is not present, PHP 5.6 is used.

# The Devbox management shell

You can open a bash shell where you can control Devbox 'from the inside':

* Run cli commands like `composer` and `drush`
* Check local files that aren't accessible from the outside (see below)

You can get this shell by opening a Terminal in the Devbox directory and running:

    devbox/vagrant/shell
    
(If you are using Devbox directly with Docker, without Vagrant, use `devbox/docker/shell`.)

This shell will be running in the same Docker container as PHP-FPM.

# Controlling Devbox

## Stopping and starting Devbox

You can pause and resume the Devbox VM using these Vagrant commands:

* `vagrant suspend`
* `vagrant resume`

When the VM is suspended, it doesn't use memory or CPU. You can also completely shut down and restart the VM using:

* `vagrant halt`
* `vagrant up`

## Restoring Devbox state
If you want to restore the Devbox server software to a clean state, clearing memory, 
wiping caches, emptying logs and simply starting over from scratch, you can run:

    vagrant provision
    
Or, if Vagrant is not running yet:

    vagrant up --provision

## Removing Devbox

* Open a Terminal in the Devbox directory, and run `vagrant destroy` to remove the Devbox VM.
* Then simply delete the Devbox directory. (But watch out for your sites/ directory!)

# Advanced topics

## The filesystem

You may notice that your PHP code sees a different filesystem than your own PC. This is because PHP (and all other Devbox server software) runs within its own safely contained filesystem. Only selected directories are shared between Devbox and the containers. In the case of the PHP container, the following directories are shared (`host => container`):

* `[Devbox directory]/sites/ => /var/www/sites/`
* `[Devbox directory]/       => /devbox/`

You cannot control the owner of the files within Devbox. This depends on your local setup and Vagrant settings. You can, however, set file permissions using `chmod`.

## SSH keys

If you want to use SSH, rsync or Git within the Devbox management shell, and use an SSH key to connect, you can put your keys in the `config/ssh-keys/` directory. These keys will be copied to the `~/.ssh/` directory within the Devbox container.

## PHP configuration

You can set custom php.ini directives by creating a file `config/php.ini`. Everything you put in this file is loaded as global PHP configuration in Devbox. An example is provided in `config/php.ini.tpl`. When you change the configuration, you need to reload PHP by running:

    vagrant provision

You *can not* set PHP configuration directives like `php_value` or `php_flag` in your `.htaccess` files, because we use FastCGI. The way to go is to set the directives using `ini_set()` or in `config/php.ini`. If the directives need to stay in the `.htaccess` for compatibility reasons, wrap them in a `<IfModule mod_php5.c>` block.

## Cron jobs

You can set up cron jobs by creating a file `config/crontab`. An example is provided in `config/crontab.tpl`. When you change the cron jobs, you need to reload cron by running:

    vagrant provision

## Port forwarding

If you want to allow outside users to connect to your Devbox sites, you can set up Vagrant port forwarding. See the example below. Note that you can't easily open ports below port # 1024, because of Unix security restrictions. The usual workaround is to forward ports to higher port numbers, for instance, exposing Devbox port 80 at port 8080 externally (as in the example below).

## Custom Vagrant configuration

You can create your own local Vagrant configuration in `config/Vagrantfile`. Vagrant merges this into the default Devbox configuration. For instance, to forward your Devbox HTTP server to external hosts at port 8080, use:

```ruby
Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.network "forwarded_port", guest: 80, host: 8080
end
```

## Connecting to the Docker daemon

You can connect to the Docker host at `tcp://192.168.33.11:2375` or from the command line at `devbox/vagrant/docker`. TLS verification is in the works.

## If You Can Handle It: using Docker directly on Linux

Since Devbox is entirely Docker-based, you can run the Devbox containers directly in Docker, without Vagrant.

If you have a working Docker environment, simple run `./devbox/docker/run` to start the Devbox containers. (This is exactly what the Vagrant box does internally!)

Using Devbox directly with Docker could be a lot faster, because Docker can access local files directly (through volumes) instead of requiring shared folders or NFS. However, this is only the case if you use Docker on Linux. Using Devbox within [boot2docker](http://boot2docker.io/) also works, but it may actually be slower than using our Vagrant box. The reason is that boot2docker doesn't support NFS yet, and our box does. This can make a noticeable difference in projects with lots of small files.

# Common problems

## NFS error messages

Devbox uses NFS to improve performance. This requires that you have nfsd installed on your system. This is the default on OS X, but on Linux you may have to install it manually.

Nfsd does not work well on encrypted volumes. This may result in the following error message:

```
exportfs: Warning: /exports does not support NFS export.
```

The solution is to run Devbox from a non-encrypted directory. If that's not possible, then comment out this line in `devbox/vagrant/Vagrantfile-core`:

```ruby
#config.vm.synced_folder ".", "/vagrant", type: "nfs", mount_options: ["nolock,vers=3,udp,noatime,actimeo=1"]
```

By commenting it out, you will disable NFS and fall back to VirtualBox file sharing. It's slower, but it should work.
