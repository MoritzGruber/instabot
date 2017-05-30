### Starting to work for some time ... and do cerntain task in this time

# We have a big steam of time , for example 8 hours

# Now we need to pass in this steam small micro task and random delays

# Total Steam Time = Time for all task + Time of all delays

# The problem is, that some task take longer and some task are done really quick, we have to consider this

# imports
import time
import random
import logging
import datetime
import json
import subprocess
import quickstart

# read config
with open('./../config.json') as data_file:
    config = json.load(data_file)

logging.basicConfig(filename='humanScheduler.log', level=logging.WARN)


### Taskloop

# Define List of task


def sleep():
    timeToSleep = (random.uniform(12.0, 22.0)) * 60
    # timeToSleep = 60
    logging.warn(str(datetime.datetime.now()) + ' - Sleeping for ' + str(timeToSleep))
    print(str(datetime.datetime.now()) + ' - Sleeping for ' + str(timeToSleep))
    time.sleep(timeToSleep)


def upload():
    logging.warn(str(datetime.datetime.now()) + ' - Uploading a Photo')
    print(str(datetime.datetime.now()) + ' - Uploading a Photo')
    subprocess.check_output(["php", "./../uploadImage/main.php", config['username'], config['password']])


def like():
    numberOfImagesToLike = random.randint(50, 150)
    logging.warn(str(datetime.datetime.now()) + ' - Like ' + str(numberOfImagesToLike) + ' images')
    print(str(datetime.datetime.now()) + ' - Like ' + str(numberOfImagesToLike) + ' images')
    unfollowCount = random.randint(3, 10)
    quickstart.instaPySession(config['username'], config['password'], numberOfImagesToLike, unfollowCount)
    # quickstart.instaPySession(config['username'], config['password'], 1, 0)



arrayOfTasks = [sleep, like, upload]


def mainloop(endTime):
    while (int(endTime) > int(time.time())):
        funcToExec = random.choice(arrayOfTasks)
        funcToExec()
        timeToSleep = random.randint(50, 130)
        logging.warn('Sleeping for ' + str(timeToSleep))
        print('Sleeping for ' + str(timeToSleep))
        time.sleep(timeToSleep)


def executeAndTrackTime(functionToTrack):
    # start counter
    start_time = time.time()
    returnvalue = functionToTrack()
    # return tracked time
    return (returnvalue, (time.time() - start_time))
