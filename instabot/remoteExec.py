from socketIO_client import SocketIO
import time
import Services.Content.Comments as Comments
import Services.Content.Statistics as Statistics

def on_get_comments(*args):
    print (args)

    try:
        args[3](None, Comments.getComments(args[0], args[1], args[2]))
    except Exception as e:
        print(str(e))
        args[3](str(e), None)


def on_get_statistics(*args):
    try:
        args[1](None, Statistics.getUserInformation(args[0]))
    except Exception as e:
        args[1](str(e), None)



def on_connect():
    print('connect')

def on_disconnect():
    print('disconnect')

def on_reconnect():
    print('reconnect')

def on_aaa_response(*args):
    print('on_aaa_response', args)

socketIO = SocketIO('localhost', 3000)
socketIO.on('connect', on_connect)
socketIO.on('reconnect', on_reconnect)
socketIO.on('disconnect', on_disconnect)
socketIO.on('getComments', on_get_comments)
socketIO.on('getStatistics', on_get_statistics)

socketIO.wait()