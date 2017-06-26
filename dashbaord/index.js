var express = require('express');
var app = express();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var port = process.env.PORT || 3000;
var remoteJobSocket = undefined;
app.use("/", express.static(__dirname + '/public'));



io.on('connection', function (socket) {
    socket.on('update', function (data, key) {
        if (typeof key === 'string' || key instanceof String) {
            if (key == 'clubmate123') {
                io.emit('update', data);
            }
        }
    });
    socket.on('connect', function () {
        console.log('new client connected');
    });
    socket.on('registerRemoteJob', function () {
        remoteJobSocket = socket;
        console.log('Server connected with socket id: ' + remoteJobSocket.id);
    });
    socket.on('getStatistics', function (username, callback) {
        console.log('get stats for ' + username);
        remoteJobSocket.emit('getStatistics', username, callback);
    });
    socket.on('getComments', function (topic, maxpp, maxtotal, callback) {
        console.log('get comments for ' + topic);
        remoteJobSocket.emit('getComments', topic, maxpp, maxtotal, callback);
    });
    socket.on('getHashtags', function (topic, amountHashtags, callback) {
        console.log('get hashtags for ' + topic);
        remoteJobSocket.emit('getHashtags', topic, amountHashtags, callback);
    });
    socket.on('getImages', function (topic, callback) {
        console.log('get images for ' + topic);
        remoteJobSocket.emit('getImages', topic, callback);
    });
    socket.on('startBot', function (callback) {
        console.log('starting bot ');
        remoteJobSocket.emit('startBot', callback);
    });
});


http.listen(port, function () {
    console.log('listening on *:' + port);
});
