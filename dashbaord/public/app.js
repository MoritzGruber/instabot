var socket = io().connect('localhost:3001');

var vm = new Vue({
    el: "#dashboard",
    data: {
        username: 'tr3ndfood',
        statistics: [],
        getStatistics: function () {
            socket.emit('getStatistics', this.username, function (error, message) {
                if (error) {
                    console.log("getStatistics: " + error);
                } else {
                    vm.statistics.push(message);
                    console.log("getStatistics: " + message);
                }
            });
        },
        topic: 'food',
        maxpp: 3,
        maxtotal: 6,
        comments: [],
        getComments: function () {
            socket.emit('getComments', this.topic, this.maxpp, this.maxtotal, function (error, message) {
                if (error) {
                    console.log("getComments: " + error);

                } else {
                    var obj = JSON.parse(message);
                    vm.comments = vm.comments.concat(obj.comments);
                    console.log("getComments: " + message);
                }
            });
        },
        amountHashtags:10,
        hashtags: [],
        getHashtags: function () {
            socket.emit('getHashtags', this.topic, this.amountHashtags, function (error, message) {
                if (error) {
                    console.log("getHashtags: " + error);

                } else {
                    // console.log(typeof message);
                    hashtags = JSON.parse(message);
                    for (index in hashtags){
                        vm.hashtags.push(hashtags[index]);
                    }
                    // vm.hashtags.push(message);
                    console.log("getHashtags: " + message);
                }
            });
        },
        images: [],
        getImages: function () {
            socket.emit('getImages', this.topic, function (error, images) {
                if (error) {
                    console.log("getImages: " + error);
                } else {
                    var images = JSON.parse(images);
                    for(index in images.photos){
                        vm.images.push(images.photos[index].src);
                    }
                    console.log("getImages found "+images.photos.length+" images");
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
