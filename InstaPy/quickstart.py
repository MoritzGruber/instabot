from instapy import InstaPy
import time
import json
import random


# Write your automation here
# Stuck ? Look at the github page or the examples in the examples folder

def getRndTag():
    with open('./../hashtagFinder/hashtags.json') as data_file:
        hashtagarray = json.load(data_file)
        return random.choice(hashtagarray)

def instaPySession(username, password, amoutOfLikes, unfollowCount):
    # If you want to enter your Instagram Credentials directly just enter
    # username=<your-username-here> and password=<your-password> into InstaPy
    # e.g like so InstaPy(username="instagram", password="test1234")
    starttime = time.time()
    InstaPy(username=username, password=password, nogui=False) \
        .login() \
        .like_by_tags([getRndTag()], amount=amoutOfLikes) \
        .set_do_follow(enabled=True, percentage=11, times=2) \
        .set_upper_follower_count(limit=220) \
        .unfollow_users(amount=unfollowCount)\
        .end()

    print ('Done in ' + str(time.time() - starttime) + ' sec')

