# Instabot
Disclaimer: This whole Application is for educational purposes only.
![dashboard-example-image](https://github.com/MoritzGruber/instabot/blob/master/dashboard/dashboard-img.png)

# Setup
Make sure to get the right chromedriver for your system from [here](https://chromedriver.storage.googleapis.com/index.html?path=2.30/). Put it in app/assets and add a valid instagram account in the config.json.

## Linux x64 Ubuntu 16.04 LTS
```bash
cd instabot 
bash linuxsetup.sh
```
Make sure to put a valid instagram account in the config.json
## Mac
Requirements: Python3, Pip3, node, npm, php, php compose, chrome browser
```bash
cd instabot 
./macsetup
```
# Dashboard 

The Dashboard consists of 3 parts
* Website (Dashboard GUI)
* Nodejs server
* Python Remote Executer

Running on port localhost:3000

### Run 
NodeJS Server:
```bash
cd dashboard
node index.js
```
Python Remote Executer:
```bash
cd app
python3 remoteExec.py
```

# Micro Services



You can start and use the micro services on it's own. They are located at app/Services/Content.

## statistics

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

#### Usage

```python 
from Services.Content import Statistics
username = 'someusername'
res = Statistics.getUserInformation(username)
```

Use carefully on users with a lot of posts (1000+), long duration loading times and potential ban of the parser account, because of to many API calls: about one for each ~18 posts.

#### Results

The received data gets returned and written into savedStatus/userinformation.json.
## Content Services
### grabcomments

Grabs different comments for a specified topic.

##### Usage

```python 
from Services.Content import Comments
topic='dog'
maxpp=2
num=10
res = Comments.getComments(topic, maxpp, num)
```

Where topic is your specified hashtag, maxpp is the number of comments grabbed per picture and num is the overall number of comments which get returned.

##### Results
The received data gets returned and written into savedStatus/comments.json.

### generateHashtags

Finds similar hashtags to one given hashtag.

##### Usage

```python
from Services.Content import Hashtags
topic= 'dog'
amount= 15
res = Hashtags.generate(topic, amount)
```
#### Results
The received data gets returned and written into savedStatus/hashtags.json.

### generateImages

Finds similar hashtags to one given hashtag.

##### Usage

```python
from Services.Content import Images
topic= 'dog'
res = Images.generate(topic)
```
##### Results
The received image links gets returned and written into savedStatus/images.json.

## Scheduling Services

### HumanScheduler
The HumanScheduler adds some random delay between executed tasks

##### Usage
```python
from Services.Scheduling import HumanScheduler
from Services.Tasks import InstaPySession

Tasks = [InstaPySession.InstaPyTask, InstaPySession.InstaPyTask, UploadImage.uploadTask]
HumanScheduler.mainloop(Tasks)

```

### SleepJob
The SleepJob stops the bot in the evening and wakes him up in the morning like a real human.
##### Usage
```python
from Services.Scheduling import SleepJob
from Services.Scheduling import HumanScheduler
from Services.Tasks import InstaPySession

Tasks = [InstaPySession.InstaPyTask, InstaPySession.InstaPyTask, UploadImage.uploadTask]
SleepJob.start(HumanScheduler.mainloop, Tasks)
```
This is used as another layer around the HumanScheduler

### Upload Image Service

This service uses gathered data to make posts. This can be a Task for the Scheduler

#### Usage

```python
from Services.Tasks import UploadImage
UploadImage.uploadTask.run()
```
The following code will access the saved data under ./app/savedStatus -> So make sure hashtags and Images have already been generated.

## Configuration
To configure the project change the example config file (./config.json)
```json
{
  "username": "someinstagramaccount",
  "password": "somepassword",
  "amountOfHashtags": 10,
  "topic": "computer",
  "wakeUpHour": 8,
  "sleepHour": 22,
  "marginTime": 1.5,
  "maxImagesPerDay": 18,
  "minImagesPerDay": 4,
  "maxLikePerDay": 900,
  "minLikePerDay": 100,
  "percentageToFollow": 6.66,
  "lowerFollowerCount": 2,
  "upperFollowerCount": 400,
  "minBreakAfterATaskInMinutes": 1.00,
  "maxBreakAfterATaskInMinutes": 60.00
}

```

# FAQ
* What meens: checkpoint_required response? -> Account blocked, login manually and unlock it with sms code.

Licence MIT

Credits: David Bochan, Moritz Gruber