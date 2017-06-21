from Services.Content import Images
from Services.Content import Hashtags
from Services.Scheduling import SleepJob
from Services.Scheduling import HumanScheduler
from Services.Tasks import Task
from Services.Tasks import UploadImage
from Services.Tasks import InstaPySession
from Services import StatusReport
import os.path
import threading
import json

# load config
with open('./../config.json') as data_file:
    config = json.load(data_file)



class Instabot():
    sleeping = True
    Tasks = [Task.baseTask]

    def __init__(self):
        if not os.path.isfile('savedStatus/hashtags.json'):
            print('Generate Hashtags')
            Hashtags.generate(config['topic'], config['amountOfHashtags'])

        if not os.path.isfile('savedStatus/images.json'):
            print('Generate Images')
            Images.generate(config['topic'])

    def start(self):
        if self.sleeping:
            SleepJob.start(HumanScheduler.mainloop, self.Tasks)
        else:
            HumanScheduler.mainloop(self.Tasks)

    def runStatusServer(self):
        thread = threading.Thread(target=StatusReport.statusServer)
        thread.daemon = True  # Daemonize thread so it gets destroyed with main thread together
        thread.start()


if __name__ == '__main__':
    Bot = Instabot()
    Bot.runStatusServer()
    Bot.Tasks = [InstaPySession.InstaPyTask, InstaPySession.InstaPyTask, UploadImage.uploadTask]
    Bot.start()


def startMain():
    Bot = Instabot()
    Bot.Tasks = [InstaPySession.InstaPyTask, InstaPySession.InstaPyTask, UploadImage.uploadTask]
    Bot.start()


def startThread():
    Mainthread = threading.Thread(target=startMain)
    Mainthread.daemon = True
    Mainthread.start()
    return Mainthread
