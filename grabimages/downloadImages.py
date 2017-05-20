import requests
import json

headers = {"Authorization": "563492ad6f917000010000010f2f5d08c1564ccb66bf4ddebe906a0a"}

requestResult = requests.get('http://api.pexels.com/v1/search?query=car&per_page=40&page=1', headers=headers)

jsonResult = json.loads(requestResult.text)

jsonToSave = json.loads("{}")

print jsonResult['total_results']

jsonToSave['total_results'] = jsonResult['total_results']
jsonToSave['photos'] = []

for photo in jsonResult['photos']:
    jsonToSave['photos'].append({"id":photo['id'], "src":photo['src']['square']})

with open('res.json', 'w') as f:
    json.dump(jsonToSave, f)

