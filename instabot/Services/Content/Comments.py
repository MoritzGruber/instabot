import subprocess
import sys
import json

information = None
topic = sys.argv[1]
maxpp = sys.argv[2]
num = sys.argv[3]

def getComments():
    global information 
    information = subprocess.check_output(["php", "phpapi/comments.php", topic, maxpp, num])

getComments()

information_dict=json.loads(information)

print("\n")
for val in information_dict:
    print(val)
print("\n")
print '..grabbed ' + str(len(information_dict)) + '/' + str(num) + ' comments..';
print("\n")

data = {};
data['topic'] = topic;
data['maxpp'] = maxpp;
data['num'] = num;

data['comments'] = information_dict;

with open('phpapi/resources/comments.json', 'a') as f:
    f.write("\n")
    f.write("\n")
    f.write(json.dumps(data).decode('unicode-escape').encode('utf8'))

print('..results added to phpapi/resources/comments.json file.');