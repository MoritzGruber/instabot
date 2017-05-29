import subprocess
import sys

information = None
username = sys.argv[1]

def getUserInformation():
    global information 
    information = subprocess.check_output(["php", "phpapi/statistics.php", username])

getUserInformation()

print(information)

with open('phpapi/resources/userinformation.csv', 'a') as f:
    f.write(information)

print('Results added to userinformation.csv file')
