var socket = io().connect('localhost:3001');

var vm = new Vue({
    el: "#dashboard",
    data: {
        username: 'tr3ndfood',
        statistics: function () {
            socket.emit('getStatistics', this.username, function (error, message) {
                if (error) {
                    console.log("getStatistics: " + error);

                } else {
                    console.log("getStatistics: " + message);
                }
            });
        },
        topic: 'food',
        maxpp: 3,
        maxtotal: 6,
        comments: function () {
            socket.emit('getComments', this.topic, this.maxpp, this.maxtotal, function (error, message) {
                if (error) {
                    console.log("getComments: " + error);

                } else {
                    console.log("getComments: " + message);
                }
            });
        },
        amountHashtags:10,
        hashtags: function () {
            socket.emit('getHashtags', this.topic, this.amountHashtags, function (error, message) {
                if (error) {
                    console.log("getHashtags: " + error);

                } else {
                    console.log("getHashtags: " + message);
                }
            });
        },
        images: function () {
            socket.emit('getImages', this.topic, function (error, message) {
                if (error) {
                    console.log("getImages: " + error);

                } else {
                    console.log("getImages: " + message);
                }
            });
        },
        startBot: function () {
            socket.emit('startBot', function (error, message) {
                if (error) {
                    console.log("startBot: " + error);

                } else {
                    console.log("startBot: " + message);
                }
            });
        },
        selected: '',
        servers: [
            {
                'lastUpdate': 22222222222220,
                'name': 'Example',
                'ip': '129.123.3.123',
                'running': true,
                'info': {
                    'Follower': 200,
                    'Images': 80
                }
            }
        ],
        logs: {
            '129.123.3.123': ['bla bla bal', 'bla bal bla '],
            '129.123.3.122': ['bla bla basdfal', 'asdf asdf bla ', 'bla blasdfa bal', 'bla baasdfasdfl bla ']
        }
    }
});

socket.on('update', function (data) {
    data.lastUpdate = Date.now();
    vm.servers.push(data);
});


timemargine_in_milsec = 6000;

setInterval(function () {
    // check if servers are running
    for (var i = 0; i < vm.servers.length; i++) {
        var server = vm.servers[i];
        var timediff = (Date.now() - server.lastUpdate);
        vm.servers[i].running = timediff < timemargine_in_milsec;
    }
}, 10000);
