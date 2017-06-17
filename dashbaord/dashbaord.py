from bottle import run, route, request
import time
import threading
import json


class Bot():
    def __init__(self, ip, currtime, images=0, username="unknown", topic="unknown"):
        self.ip = ip
        self.currtime = currtime
        self.images = images
        self.username = username
        self.topic = topic
        self.running = True


bots = {}


def checkTimeout():
    while (True):
        for key in bots:
            if bots[key].currtime < time.time() - 60:
                bots[key].running = False
            else:
                bots[key].running = True
        time.sleep(2)


@route('/', method='POST')
def index():
    if request.environ.get('REMOTE_ADDR') in bots.keys():
        print "updating " + request.environ.get('REMOTE_ADDR')
        bots[request.environ.get('REMOTE_ADDR')].currtime = time.time()
    else:
        print "creating " + request.environ.get('REMOTE_ADDR')
        for line in request.body:
            jsondata = json.loads(line)
        bots[request.environ.get('REMOTE_ADDR')] = Bot(ip=request.environ.get('REMOTE_ADDR'), currtime=time.time(),
                                                       username=jsondata['username'], topic=jsondata['topic'])


@route('/', method='GET')
def index():
    returnstring = ""
    for key in bots:
        returnstring = returnstring + bots[key].ip + " "
        returnstring = returnstring + bots[key].username + " "
        returnstring = returnstring + str(bots[key].running) + " <br>"

    return returnstring


@route('/test')
def index():
    return "hello world"


t = threading.Thread(target=checkTimeout)
t.daemon = True
t.start()

run(host='46.101.111.251', port=8001)
