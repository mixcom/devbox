#!/bin/sh
yum groupinstall -y "Development Tools"
yum install -y gcc gcc-c++ g++ patch readline readline-devel zlib libyaml-devel libffi-devel bzip2 libtool bison iconv-devel make automake autoconf curl-devel openssl-devel zlib-devel httpd-devel apr-devel apr-util-devel sqlite-devel which tar libyaml-devel wget
cd /root
wget http://ftp.ruby-lang.org/pub/ruby/1.9/ruby-1.9.3-p194.tar.gz
tar xvzf ruby-1.9.3-p194.tar.gz
cd ruby-1.9.3-p194
git apply ../ruby-patch.patch
./configure
make
make install
cd /root
rm -rf /root/ruby-1.9.3-p194
gem update --system
