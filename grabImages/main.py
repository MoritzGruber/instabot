import requests
import json
import sys


if (sys.argv.__len__() <= 1):
    print('Please provide tag as parameter...exiting.')
    quit()

headers = {"Authorization": "563492ad6f917000010000010f2f5d08c1564ccb66bf4ddebe906a0a"}

requestResult = requests.get('http://api.pexels.com/v1/search?query='+sys.argv[1]+'&per_page=40&page=1', headers=headers)

jsonResult = json.loads(requestResult.text)

jsonToSave = json.loads("{}")

jsonToSave['total_results'] = jsonResult['total_results']
jsonToSave['photos'] = []
numberOfPageesNeeded = ((jsonResult['total_results'] )/ 40 + 1 )

print ('Found '+ str(jsonResult['total_results'])+ ' images')
print (str(numberOfPageesNeeded + 1) + ' api calls used form 200/h' )



for pageNumber in range(numberOfPageesNeeded):
    requestResult = requests.get('http://api.pexels.com/v1/search?query='+sys.argv[1]+'&per_page=40&page='+str(numberOfPageesNeeded), headers=headers)
    for photo in jsonResult['photos']:
        jsonToSave['photos'].append({"id":photo['id'], "src":photo['src']['square']})

with open('images.json', 'w') as f:
    json.dump(jsonToSave, f)

