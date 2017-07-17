#!/usr/bin/env bash

# init submodules and update
git submodule init
git submodule update

#get dl-links for chrome
export CHROME="https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb"
export CHROMEARM="https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb"
export CRHOMEDRIVER="http://chromedriver.storage.googleapis.com/2.29/chromedriver_linux64.zip"
export CRHOMEDRIVERARM="http://chromedriver.storage.googleapis.com/2.29/chromedriver_linux64.zip"

locale-gen en_US.UTF-8
export LANG=en_US.UTF-8
export LANG=C.UTF-8
# Environment setup
apt-get update \
    && apt-get -y upgrade \
    && apt-get -y install \
        apt-utils \
        locales \
        unzip \
	    sed \
        python3-pip \
        python3-dev \
        build-essential \
        libgconf2-4 \
        libnss3-1d \
        libxss1 \
        libssl-dev \
        libffi-dev \
        xvfb \
        wget \
        libcurl3 \
        gconf-service \
        libasound2 \
        libatk1.0-0 \
        libcairo2 \
        libcups2 \
        libfontconfig1 \
        libgdk-pixbuf2.0-0 \
        libgtk2.0-0 \
        libpango1.0-0 \
        libxcomposite1 \
        libxtst6 \
        fonts-liberation \
        libappindicator1 \
        build-essential\
        curl\
        git\
        npm \
        nodejs\
       xdg-utils \
       software-properties-common\
       xvfb\
   && locale-gen en_US.UTF-8 \
    && dpkg-reconfigure locales \
   && apt-get -f install
ln -s /usr/bin/nodejs /usr/bin/node
export LANG="en_US.UTF-8"
export LANGUAGE="en_US:en"
export LC_ALL="en_US.UTF-8"
export CRHOMEDRIVER="http://chromedriver.storage.googleapis.com/2.29/chromedriver_linux64.zip"


add-apt-repository ppa:ondrej/php -y
apt-get install php7.0-curl -y
apt-get update
apt-get install php7.0-gd -y
apt-get install xvfb -y
apt-get install chromium-browser -y
apt-get install unzip -y
export LC_ALL=C
apt-get install python-pip -y
export LC_ALL=C
pip3 install --upgrade pip
pip3 install pyvirtualdisplay
pip3 install selenium
pip3 install clarifai
pip3 install emoji
pip3 install requests
pip3 install socketIO-client-2


wget ${CRHOMEDRIVER} \
    && unzip chromedriver_linux64 \
    && mv chromedriver ./app/assets/chromedriver \
    && chmod +x ./app/assets/chromedriver

apt-get purge php7.1 -y
apt-get install php7.0 -y
rm -rf /etc/php/7.1/
apt-get install composer -y
apt-get install php7.0-curl
apt-get install php7.0-gd
cd ./app/phpapi/
composer install
cd ..
cd ..
cd dashboard
npm install
cd ..