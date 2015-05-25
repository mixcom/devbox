FROM phusion/baseimage:0.9.16
MAINTAINER Sebastiaan Besselsen <sebastiaan.besselsen@gmail.com>

RUN apt-get -y update
RUN apt-get -y install python-software-properties
RUN apt-get -y install htop git
RUN apt-get -y install software-properties-common

RUN useradd -M mysql
RUN usermod -L mysql
RUN usermod -s /bin/false mysql
