from Task import baseTask
import random
import time
import datetime

class SleepTask(baseTask):
    def run(self):
        timeToSleep = (random.uniform(12.0, 22.0)) * 60
        print(str(datetime.datetime.now()) + ' - Sleeping for ' + str(timeToSleep))
        time.sleep(timeToSleep)