import json

class baseTask():
    maxCountPerDay = 0
    currRunCounter = 0

    def __init__(self):
        with open('./../config.json') as data_file:
            self.config = json.load(data_file)

    def run(self):
        if self.currRunCounter > self.maxCountPerDay:
            return "{} has run enough today".format(self.__class__.__name__)
        self.currRunCounter += 1
        print('Task runned')
