### Starting to work for some time ... and do cerntain task in this time

# We have a big steam of time , for example 8 hours

# Now we need to pass in this steam small micro task and random delays

# Total Steam Time = Time for all task + Time of all delays

# The problem is, that some task take longer and some task are done really quick, we have to consider this

import datetime
import json
import logging
import random
# imports
import time

# read config
with open('./../config.json') as data_file:
    config = json.load(data_file)




def mainloop(arrayOfTasks, endTime=float("inf")):
    for i, task in enumerate(arrayOfTasks):
        arrayOfTasks[i] = task()

    while endTime > time.time():
        fuc = random.choice(arrayOfTasks)
        fuc.run()
        timeToSleep = random.randint(60, 300)
        logging.warn('Sleeping for ' + str(timeToSleep))
        print('Sleeping for ' + str(timeToSleep))
        time.sleep(timeToSleep)
