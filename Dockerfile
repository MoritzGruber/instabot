FROM moritzgruber/instabotcontainer

MAINTAINER Moritz Gruber <moritzgruber@yahoo.de>


COPY ./ ./
WORKDIR /instabot/phpapi/
RUN composer install

WORKDIR /instabot/

RUN git clone https://github.com/MoritzGruber/InstaPy.git ./Services/Tasks/InstaPy
CMD ["python", "main.py"]




