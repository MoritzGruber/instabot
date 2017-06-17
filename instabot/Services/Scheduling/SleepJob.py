import datetime
import json
import logging
import random
import time


def start(dayScheduler, tasks):
    #  config logging
    logging.basicConfig(filename='sleepJob.log', level=logging.DEBUG)

    # load configuration
    with open('./../config.json') as data_file:
        config = json.load(data_file)

    ##########Define functions ###############

    # humanize function
    def humanize(number, margin):
        return number + random.uniform(-1 * margin, margin)

    def getTimestamp(timeInFloat):
        fhours = timeInFloat
        ihours = int(timeInFloat)
        # get current time
        lst = list(datetime.datetime.now().timetuple())
        # if the hours are lower then curr hour, then it is the next day
        if (lst[3] + (2 * config['marginTime']) > ihours):
            lst[2] = lst[2] + 1
        # overrite time with new hours, mins, secs
        lst[3] = ihours
        lst[4] = int((fhours - ihours) * 60)
        lst[5] = int(((fhours - ihours) * 3600) % 60)
        return time.mktime(time.struct_time(tuple(lst)))

    ############## Main Loop ##################
    while (True):
        # 1. generate new sleep and wakeup time
        wakeUpTime = getTimestamp(humanize(config['wakeUpHour'], config['marginTime']))
        sleepTime = getTimestamp(humanize(config['sleepHour'], config['marginTime']))
        print ('Going to bed at: ' + str(datetime.datetime.fromtimestamp(sleepTime)))
        print ('Waking up at: ' + str(datetime.datetime.fromtimestamp(wakeUpTime)))
        # 2. Work until sleep
        while (sleepTime > time.time()):
            print('Calling Human Scheduler')
            time.sleep(5)
            # try:
            dayScheduler(tasks, sleepTime)
            # except Exception as e:
            #     logging.error(e.message, e.args)
            print('Return from Human Scheduler')

        time.sleep(5)
        # 3. Sleep Until Wakeup
        while (wakeUpTime > time.time()):
            time.sleep(5)
        # 4. Start again
        print ('Starting a new Cycle...')
