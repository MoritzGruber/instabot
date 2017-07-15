import requests
import json


def generate(tag):
    """This function saves all avaliable images on the pexles api to savedStatus/images.json file"""

    headers = {"Authorization": "563492ad6f917000010000010f2f5d08c1564ccb66bf4ddebe906a0a"}

    requestResult = requests.get('http://api.pexels.com/v1/search?query=' + tag + '&per_page=40&page=1',
                                 headers=headers)

    jsonResult = json.loads(requestResult.text)

    jsonToSave = json.loads("{}")

    jsonToSave['total_results'] = jsonResult['total_results']
    jsonToSave['photos'] = []
    numberOfPageesNeeded = int((jsonResult['total_results']) / 40 + 1)

    print ('Found ' + str(jsonResult['total_results']) + ' images')
    print (str(numberOfPageesNeeded + 1) + ' api calls used form 200/h')

    for pageNumber in range(numberOfPageesNeeded):
        requestResult = requests.get(
            'http://api.pexels.com/v1/search?query=' + tag + '&per_page=40&page=' + str(
                pageNumber), headers=headers)
        for photo in jsonResult['photos']:
            jsonToSave['photos'].append({"id": photo['id'], "src": photo['src']['square']})

    with open('savedStatus/images.json', 'w') as f:
        json.dump(jsonToSave, f)


def generate_onepage(tag):


    headers = {"Authorization": "563492ad6f917000010000010f2f5d08c1564ccb66bf4ddebe906a0a"}

    requestResult = requests.get('http://api.pexels.com/v1/search?query=' + tag + '&per_page=40&page=1',
                                 headers=headers)

    jsonResult = json.loads(requestResult.text)

    jsonToSave = json.loads("{}")

    jsonToSave['total_results'] = jsonResult['total_results']
    jsonToSave['photos'] = []
    numberOfPageesNeeded = 1

    print ('Found ' + str(jsonResult['total_results']) + ' images')
    print (str(numberOfPageesNeeded + 1) + ' api calls used form 200/h')

    for pageNumber in range(numberOfPageesNeeded):
        requestResult = requests.get(
            'http://api.pexels.com/v1/search?query=' + tag + '&per_page=40&page=' + str(
                pageNumber), headers=headers)
        for photo in jsonResult['photos']:
            jsonToSave['photos'].append({"id": photo['id'], "src": photo['src']['square']})

    return json.dumps(jsonToSave)