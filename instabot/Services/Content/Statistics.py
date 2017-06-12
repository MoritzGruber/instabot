import subprocess
import sys

information = None
username = sys.argv[1]

def getUserInformation():
    global information 
    information = subprocess.check_output(["php", "phpapi/statistics.php", username])

getUserInformation()

print("\n")
print(information)
print("\n")

with open('phpapi/resources/userinformation.json', 'a') as f:
    f.write("\n")
    f.write("\n")
    f.write(information)

print('..results added to phpapi/resources/userinformation.json file.')
