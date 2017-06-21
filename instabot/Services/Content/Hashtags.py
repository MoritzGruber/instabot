import subprocess
import json
import time
import sys


def     generate(tag, amount):
    """This function generates a specific number of tags relataed to one main tag"""
    setOfTags = set()

    setOfTags.add("#" + tag)

    # for _ in range(int(sys.argv[2])):
    def search():
        tmpNextSet = setOfTags.copy()
        while (True):
            # 1
            for tag in tmpNextSet:
                time.sleep(0.1)
                relatedTags = subprocess.check_output(["php", "phpapi/relatedTags.php", tag[1:]]).decode('utf8')
                relatedTags = str(relatedTags)
                if('omething went wrong' in relatedTags):
                    print ('sth went wrong, waiting a 300 millsec')
                    time.sleep(0.3)
                    continue
                arrayOfTags = str.split(relatedTags)
                print (relatedTags)
                for element in arrayOfTags:
                    setOfTags.add("#" + element)
                    percentage = ((float(setOfTags.__len__()) / float(amount)) * 100)
                    print("Status: " + "{:.0f}".format(percentage) + "%  -  " + str(setOfTags.__len__()) + "/" + str(
                        amount) + " Hashtags")
                    if setOfTags.__len__() >= int(amount):
                        print('Done')
                        return
                    sys.stdout.write("\033[F")
                    # 5 +1
                tmpNextSet = setOfTags - tmpNextSet

                # tmpNextSet = bla.copy()
                # 5

    search()

    class SetEncoder(json.JSONEncoder):
        def default(self, obj):
            if isinstance(obj, set):
                return list(obj)
            return json.JSONEncoder.default(self, obj)

    with open('savedStatus/hashtags.json', 'w') as f:
        json.dump(setOfTags, f, cls=SetEncoder)
    print('Results saved to hashtags.json file')
    blob = json.dumps(setOfTags, cls=SetEncoder)
    print (blob)
    return blob
