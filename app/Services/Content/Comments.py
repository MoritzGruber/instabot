import subprocess
import json

# import sys
# topic = sys.argv[1]
# maxpp = sys.argv[2]
# num = sys.argv[3]

def getComments(topic, maxpp, num):
    information = subprocess.check_output(["php", "phpapi/comments.php", str(topic), str(maxpp), str(num)]).decode('unicode-escape').encode('latin1').decode('utf8')
    data = {}
    data['topic'] = topic
    data['maxpp'] = maxpp
    data['num'] = num
    data['comments'] = information
    return json.dumps(data, ensure_ascii=False)

def getCommentsSaveToFile(topic, maxpp, num):
    data = getComments(topic, maxpp, num)
    with open('savedStatus/comments.json', 'w') as f:
        json.dump(data, f)
    #open file
    # fh = open('phpapi/resources/comments.json', 'r')
    # # if file does not exist, create it
    # if not fh:
    #     fh = open('phpapi/resources/comments.json', "w")
    #
    # with open('phpapi/resources/comments.json', 'a') as f:
    #     f.write("\n")
    #     f.write("\n")
    #     f.write(json.dumps(data).decode('unicode-escape').encode('utf8'))
    #
    # print('..results added to phpapi/resources/comments.json file.')

    return data

def getUnicodeCommentsSaveToFile(topic, maxpp, num):
    comments = getComments(topic, maxpp, num)

    data = {};
    data['topic'] = topic;
    data['maxpp'] = maxpp;
    data['num'] = num;

    data['comments'] = comments;

    # open file
    fh = open('savedStatus/comments.json', 'r')
    
    # if file does not exist, create it
    if not fh:
        fh = open('savedStatus/comments.json', "w")

    with open('savedStatus/comments.json', 'a') as f:
        f.write("\n")
        f.write("\n")
        f.write(json.dumps(data).decode('unicode-escape').encode('utf8'))

    print('..results added to phpapi/savedStatus/comments.json file.')

    return data