import requests
import time
import json

def statusServer():

    with open('./../config.json') as data_file:
        config = json.load(data_file)

    while(True):
        try:
            payload = {'username': config['username'], 'topic': config['topic']}
            requests.post("http://" + config['dashboardIp']+":"+config['dashboardPort'], json=payload)
            time.sleep(10)
        except Exception:
            pass

