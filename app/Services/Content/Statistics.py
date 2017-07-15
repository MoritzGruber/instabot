import subprocess
import os
# import sys
#
# information = None
# username = sys.argv[1]

def getUserInformation(username):
    # global information
    information = str(subprocess.check_output(["php", "phpapi/statistics.php", username]).decode('utf-8'))
    # print("\n")
    print(information)
    # print("\n")

    fileAleadyExit = os.path.isfile('savedStatus/userinformation.json')
    with open('savedStatus/userinformation.json', 'a') as f:
        if fileAleadyExit:
            f.write("\n")
            f.write("\n")
        f.write(information)

    # print('..results added to phpapi/resources/userinformation.json file.')

    return information

# getUserInformation()


