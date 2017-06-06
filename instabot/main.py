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


class Instabot():
    sleeping = True
    Tasks = [Task.baseTask]

    def __init__(self):
        if not os.path.isfile('savedStatus/hashtags.json'):
            print('Generate Hashtags')
            Hashtags.generate('car', 30)

        if not os.path.isfile('savedStatus/images.json'):
            print('Generate Images')
            Images.generate('car')

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
