var socket = io().connect('localhost:3001');

var topcontr = new Vue({
    el: "#topcontr",
    data: {
        username: 'tr3ndfood',
        statistics: [],
        getStatistics: function () {
            socket.emit('getStatistics', this.username, function (error, message) {
                if (error) {
                    console.log("getStatistics: " + error);
                } else {
                    topcontr.statistics.push(JSON.parse(message));
                    console.log("getStatistics: " + message);
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

// GRID TEMPLATE
Vue.component('demo-grid', {
    template: '#grid-template',
    props: {
        data: Array,
        columns: Array,
        filterKey: String
    },
    data: function () {
        var sortOrders = {}
        this.columns.forEach(function (key) {
            sortOrders[key] = 1
        })
        return {
            sortKey: '',
            sortOrders: sortOrders
        }
    },
    computed: {
        filteredData: function () {
            var sortKey = this.sortKey
            var filterKey = this.filterKey && this.filterKey.toLowerCase()
            var order = this.sortOrders[sortKey] || 1
            var data = this.data
            if (filterKey) {
                data = data.filter(function (row) {
                    return Object.keys(row).some(function (key) {
                        return String(row[key]).toLowerCase().indexOf(filterKey) > -1
                    })
                })
            }
            if (sortKey) {
                data = data.slice().sort(function (a, b) {
                    a = a[sortKey]
                    b = b[sortKey]
                    return (a === b ? 0 : a > b ? 1 : -1) * order
                })
            }
            return data
        }
    },
    filters: {
        capitalize: function (str) {
            return str.charAt(0).toUpperCase() + str.slice(1)
        }
    },
    methods: {
        sortBy: function (key) {
            this.sortKey = key
            this.sortOrders[key] = this.sortOrders[key] * -1
        }
    }
})

var botcontr = new Vue({
    el: "#botcontr",
    data: {
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
                    //botcontr.comments = botcontr.comments.concat(obj.comments);
                    //console.log("getComments: " + message);
                    botcontr.comments.unshift(obj.comments);
                }
            });
        },
        amountHashtags: 10,
        hashtags: [],
        getHashtags: function () {
            socket.emit('getHashtags', this.topic, this.amountHashtags, function (error, message) {
                if (error) {
                    console.log("getHashtags: " + error);

                } else {
                    // console.log(typeof message);
                    hashtags = JSON.parse(message);
                    for (index in hashtags) {
                        botcontr.hashtags.unshift(hashtags[index]);
                    }
                    // botcontr.hashtags.push(message);
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
                    for (index in images.photos) {
                        botcontr.images.unshift(images.photos[index].src);
                    }
                    console.log("getImages found " + images.photos.length + " images");
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

// GRID TEMPLATE
Vue.component('demo-grid', {
    template: '#grid-template',
    props: {
        data: Array,
        columns: Array,
        filterKey: String
    },
    data: function () {
        var sortOrders = {}
        this.columns.forEach(function (key) {
            sortOrders[key] = 1
        })
        return {
            sortKey: '',
            sortOrders: sortOrders
        }
    },
    computed: {
        filteredData: function () {
            var sortKey = this.sortKey
            var filterKey = this.filterKey && this.filterKey.toLowerCase()
            var order = this.sortOrders[sortKey] || 1
            var data = this.data
            if (filterKey) {
                data = data.filter(function (row) {
                    return Object.keys(row).some(function (key) {
                        return String(row[key]).toLowerCase().indexOf(filterKey) > -1
                    })
                })
            }
            if (sortKey) {
                data = data.slice().sort(function (a, b) {
                    a = a[sortKey]
                    b = b[sortKey]
                    return (a === b ? 0 : a > b ? 1 : -1) * order
                })
            }
            return data
        }
    },
    filters: {
        capitalize: function (str) {
            return str.charAt(0).toUpperCase() + str.slice(1)
        }
    },
    methods: {
        sortBy: function (key) {
            this.sortKey = key
            this.sortOrders[key] = this.sortOrders[key] * -1
        }
    }
})

// bootstrap the demo
var demo = new Vue({
    el: '#demo',
    data: {
        searchQuery: '',
        gridColumns: ['timestamp', 'username', 'follower_count', 'following_count', 'media_count', 'usertags_count', 'feed_items', 'likes', 'comments'],
        gridData: topcontr.statistics
    }
})
// GRID TEMPLATE

// GRID TEMPLATEb

socket.on('update', function (data) {
    data.lastUpdate = Date.now();
    topcontr.servers.push(data);
    botcontr.servers.push(data);
});


timemargine_in_milsec = 6000;

setInterval(function () {
    // check if servers are running
    for (var i = 0; i < topcontr.servers.length; i++) {
        var server = topcontr.servers[i];
        var timediff = (Date.now() - server.lastUpdate);
        topcontr.servers[i].running = timediff < timemargine_in_milsec;
    }

    for (var i = 0; i < botcontr.servers.length; i++) {
        var server = botcontr.servers[i];
        var timediff = (Date.now() - server.lastUpdate);
        botcontr.servers[i].running = timediff < timemargine_in_milsec;
    }
}, 10000);
