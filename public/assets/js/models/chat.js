define(['socket.io', 'backbone'], function (io, backbone) {
    var model = backbone.Model.extend({
        defaults: {
            name: 'chat',
            authToken: null,
            lastMesssage: null,
            bindRooms: {},
            socket: null,
            isConnected: false
        },

        bindRoom: function (roomName, chatEvent, userlistEvent) {
            console.log('Bind Room: ' + roomName);

            var bindRooms = this.get('bindRooms');
            bindRooms[roomName] = {chatEvent: chatEvent, userlistEvent: userlistEvent};

            this.set('bindRooms', bindRooms);
        },

        connect: function () {
            var $this = this;
            console.log('Initializing Chat Model...');

            var socket = this.get('socket');
            if (socket == null) {
                //socket = io.connect(chatServerAddr + '/?auth=' + authToken, {
                socket = io.connect(chatServerAddr, {
                    'connect timeout': 5000, // 5 sec
                    'max reconnection attempts': 99999,
                    'reconnection limit': 4000,
                    'sync disconnect on unload': true,
                    'force new connection': true
                });

                this.set('socket', socket);
            }

            socket.on('connecting', function () {
                console.log('Connect to server...');
            });

            socket.on('close', function () {
                console.log('Closed');
                $this.set('isConnected', false);
            });

            socket.on('connect_failed', function () {
                console.log('Connect_failed');
            });

            socket.on('error', function (reason) {
                console.log('Server connect fail. Retry 3sec after...' + reason);
                $this.set('isConnected', false);

                setTimeout(function () {
                    $this.connect();
                }, 3000);
            });

            socket.on('reconnect_failed', function () {
                console.log('Reconnect_failed');
            });

            socket.on('reconnect', function () {
                console.log('Reconnected');
            });

            socket.on('reconnecting', function () {
                console.log('Reconnecting...');
            });

            socket.on('connect', function () {
                console.log('Connected Chat Server');
                $this.set('isConnected', true);
            });

            socket.on('broadcast', function (data) {
                var roomName = data.roomName;
                var message = data.text;

                // console.log('Receved broadcast');

                var bindRooms = $this.get('bindRooms');
                $.each(bindRooms, function (k, v) {
                    if (k == roomName) {
                        v.chatEvent(data);
                    }
                });

                $this.set('lastMesssage', message);
            });

            socket.on('disconnect', function () {
                console.log('disconnected');
                $this.set('isConnected', false);
            });

            socket.on('chat', function (data) {
                var roomName = data.roomName;
                var message = data.message;

                var bindRooms = $this.get('bindRooms');

                if ($.inArray(roomName, bindRooms) > -1) {
                    bindRooms.roomName.chatEvent(message);
                }

                console.log(data);
            });

            socket.on('userlist', function (data) {
                var roomName = data.roomName;
                var bindRooms = $this.get('bindRooms');

                try {
                    bindRooms[roomName].userlistEvent(data);
                } catch (e) {

                }
            });
        },

        isConnected: function () {
            return this.get('socket').socket.connected;
        }
    });

    var chatModel = new model;

    chatModel.on('change:lastMessage', function () {
        //
    });

    return chatModel;
});
