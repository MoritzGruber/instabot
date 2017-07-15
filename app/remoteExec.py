from socketIO_client import SocketIO
import time
import Services.Content.Comments as Comments
import Services.Content.Statistics as Statistics
import Services.Content.Hashtags as Hashtags
import Services.Content.Images as Images

import main



def on_get_comments(*args):
    print('get comments')
    try:
        args[3](None, Comments.getComments(args[0], args[1], args[2]))
    except Exception as e:
        print(str(e))
        args[3](str(e), None)


def on_get_statistics(*args):
    print('get statistics')
    try:
        args[1](None, Statistics.getUserInformation(args[0]))
    except Exception as e:
        print(str(e))
        args[1](str(e), None)

def on_get_hashtags(*args):
    print('get hashtags')
    try:
        args[2](None, Hashtags.generate(args[0], args[1]))
    except Exception as e:
        print(str(e))
        args[2](str(e), None)

def on_get_images(*args):
    print('get images')
    try:
        args[1](None, Images.generate_onepage(args[0]))
    except Exception as e:
        print(str(e))
        args[1](str(e), None)

def on_start_bot(*args):
    print('starting bot')
    try:
        thread = main.startThread()
        args[0](None, 'bot started')
    except Exception as e:
        print(str(e))
        args[0](str(e), None)

def on_stop_bot():
    print('stopping bot')


def on_connect():
    print('connect')
    socketIO.emit('registerRemoteJob')

def on_disconnect():
    print('disconnect')

def on_reconnect():
    print('reconnect')
    time.sleep(0.2)
    socketIO.emit('registerRemoteJob')


def on_aaa_response(*args):
    print('on_aaa_response', args)

socketIO = SocketIO('localhost', 3000)
socketIO.on('connect', on_connect)
socketIO.on('on_aaa_response', on_aaa_response)
socketIO.on('reconnect', on_reconnect)
socketIO.on('disconnect', on_disconnect)
socketIO.on('getComments', on_get_comments)
socketIO.on('getStatistics', on_get_statistics)
socketIO.on('getHashtags', on_get_hashtags)
socketIO.on('getImages', on_get_images)
socketIO.on('startBot', on_start_bot)
socketIO.on('stopBot', on_stop_bot)

socketIO.wait()