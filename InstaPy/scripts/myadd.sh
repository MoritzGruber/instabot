#install general instapy dependicies
sudo apt install php7.0-cli -y
sudo apt-get install php7.0-curl -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update
sudo apt-get install php7.0-gd -y
sudo apt install composer -y
sudo apt-get install xvfb -y
sudo apt-get install chromium-browser -y
sudo apt-get install unzip -y
export LC_ALL=C

sudo apt install python-pip -y
sudo pip install pyvirtualdisplay
sudo pip install selenium
sudo pip install clarifai
sudo pip install emoji

## hashtags gernation
SCRIPTFOLDERPATH = $pwd
echo "Generating Hastags"

cd ./../../hashtagFinder/phpapi/
composer install
cd ..
python main.py INSTATOPIC HASHTAGCOUNT
## image peperation
echo "Grabing Images"

cd ..
cd grabImages/
python main.py INSTATOPIC
cd SCRIPTFOLDERPATH
bash unix.sh



