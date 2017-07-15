import json
import random
import time
import logging
from .InstaPy.instapy import InstaPy
from .Task import baseTask


# Write your automation here
# Stuck ? Look at the github page or the examples in the examples folder


class InstaPyTask(baseTask):
    def run(self):
        numberOfImagesToLike = random.randint(30, 200)
        logging.info('T: Like ' + str(numberOfImagesToLike) + ' images')
        print('T: Like ' + str(numberOfImagesToLike) + ' images')
        unfollowCount = random.randint(3, 10)
        instaPySession(self.config['username'], self.config['password'], numberOfImagesToLike, unfollowCount)


def getRndTag():
    with open('./savedStatus/hashtags.json') as data_file:
        hashtagarray = json.load(data_file)
        return random.choice(hashtagarray)


def instaPySession(username, password, amoutOfLikes, unfollowCount):
    # If you want to enter your Instagram Credentials directly just enter
    # username=<your-username-here> and password=<your-password> into InstaPy
    # e.g like so InstaPy(username="instagram", password="test1234")
    starttime = time.time()
    InstaPy(username=username, password=password, nogui=False) \
        .login() \
        .set_do_follow(enabled=True, percentage=33, times=2) \
        .set_upper_follower_count(limit=400) \
        .like_by_tags([getRndTag()], amount=amoutOfLikes) \
        .end()
    print ('Done in ' + str(time.time() - starttime) + ' sec')
