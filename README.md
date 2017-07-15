# instabot
A Instagram bot for educational purposes

After cloning the repository get all neccessary submodules:
```bash
git submodule init
git submodule update
```

## Dashboard 

![dashboard-example-image](https://github.com/MoritzGruber/instabot/blob/master/dashboard/dashboard-img.png)

The Dashboard consists of 3 parts
* Website (Dashboard GUI)
* Nodejs server
* Python Remote Executer

Running on port localhost:3000

### Setup 
```bash
cd dashboard 
npm install
```

```bash
cd app

# FOR LINUX ONLY
bash installwithoutdocker.sh

# OR USE
pip3 install socketIO-client-2
```

```bash
cd app/phpapi/ 
composer install
```

Make sure to get the right chromedriver for your system from [here](https://chromedriver.storage.googleapis.com/index.html?path=2.30/). Just put it in app/assets.

### Run 

NodeJS Server:
```bash
node index.js
```

Python Remote Executer:
```bash
cd instabot
python3 remoteExec.py
```

## Micro Services

You can start and use the micro services on it's own. They are located at app/Services/Content.

### statistics

Returns different information from a Instagram account:

* timestamp
* username
* follower_count
* following_count
* media_count
* usertags_count
* feed_items
* likes
* comments

#### Configuration

Open app/Services/Content/phpapi/statistics.php and add a parser account: $username = 'xxx'; and $password = 'xxx';

#### Start

```python grab-statistics.py tr3ndfood```

Use carefully on users with a lot of posts (1000+), long duration loading times and potential ban of the parser account, because of to many API calls: about one for each ~18 posts.

#### Results

The received data gets written into savedStatus/userinformation.json.

### grabcomments

Grabs different comments for a specified topic.

#### Configuration

Open app/Services/Content/phpapi/comments.php and add a parser account: $username = 'xxx'; and $password = 'xxx';

#### Start

```python grab-comments.py topic maxpp num```

Where topic is your specified hashtag, maxpp is the number of comments grabbed per picture and num is the overall number of comments which get returned.

#### Results

The received data gets written into savedStatus/comments.json.
