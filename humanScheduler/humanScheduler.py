### Starting to work for some time ... and do cerntain task in this time

# We have a big steam of time , for example 8 hours

# Now we need to pass in this steam small micro task and random delays

# Total Steam Time = Time for all task + Time of all delays

# The problem is, that some task take longer and some task are done really quick, we have to consider this

#from instapy import InstaPy
### Taskloop





### List of task


# session = InstaPy(username='praisingofcars', password='clubmate123')
# session.login()

import sys
sys.path.insert(0,'..')

import time
import subprocess
import InstaPy.quickstart as ooooo


ooooo.somebullshit()

# def botting():
#     session.like_by_tags(['#car', '#nature'], amount=1)
#     session.set_do_follow(enabled=True, percentage=10, times=1)
#     return True

def executeAndTrackTime(functionToTrack, params):
    # start counter
    start_time = time.time()
    returnvalue = functionToTrack(params)
    # return tracked time
    return (returnvalue, (time.time() - start_time))

func = subprocess.check_output

#result2 = executeAndTrackTime(func, ["php", "../uploadImage/main.php", "praisingofcars", "clubmate123"])
# result = executeAndTrackTime(botting, null)


#
# print (result[0])
# print (result[1])


# the following break time after a Task should be similar to

# minDelay < rnd(timeOfTask) < maxDelay