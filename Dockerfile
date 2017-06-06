FROM ubuntu:16.04

MAINTAINER Moritz Gruber <moritzgruber@yahoo.de>

# Set env variables
ENV CHROME https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
ENV CRHOMEDRIVER http://chromedriver.storage.googleapis.com/2.29/chromedriver_linux64.zip

# Environment setup
RUN apt-get update \
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
        xdg-utils \
        git \
    && locale-gen en_US.UTF-8 \
    && dpkg-reconfigure locales \
    && apt-get -f install

ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8
ENV CRHOMEDRIVER http://chromedriver.storage.googleapis.com/2.29/chromedriver_linux64.zip



#install general instapy dependicies
RUN locale-gen en_US.UTF-8
RUN export LANG=en_US.UTF-8
RUN export LANG=C.UTF-8
RUN apt-get install software-properties-common -y
RUN add-apt-repository ppa:ondrej/php -y
RUN apt-get install php7.0-curl -y
RUN apt-get update
RUN apt-get install php7.0-gd -y
RUN apt-get install xvfb -y
RUN apt-get install chromium-browser -y
RUN apt-get install unzip -y
RUN export LC_ALL=C

RUN apt-get install python-pip -y
RUN pip install --upgrade pip
RUN pip install pyvirtualdisplay
RUN pip install selenium
RUN pip install clarifai
RUN pip install emoji
RUN pip install requests

RUN wget ${CRHOMEDRIVER} \
    && unzip chromedriver_linux64 \
    && mv chromedriver /usr/bin/chromedriver \
    && chmod +x /usr/bin/chromedriver

RUN apt-get purge php7.1 -y
RUN apt-get install php7.0 -y
RUN rm -rf /etc/php/7.1/
RUN apt-get install composer -y

COPY ./ ./
WORKDIR /instabot/phpapi/
RUN composer install

WORKDIR /instabot/
CMD ["python", "test.py"]



