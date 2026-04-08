/*
    Node.js server script
    Required node packages: express, redis, socket.io
*/
const PORT = 8085;
const HOST = '0.0.0.0';
const REDIS_HOST = process.env.REDIS_HOST || 'localhost';
const REDIS_PORT = process.env.REDIS_PORT || 6379;

var express = require('express'),
    http = require('http');

var app = express();
var server = http.createServer(app);

const redis = require('redis');
const client = redis.createClient({ host: REDIS_HOST, port: REDIS_PORT });
log('info', 'connected to redis server at ' + REDIS_HOST + ':' + REDIS_PORT);

const { Server } = require('socket.io');

if (!module.parent) {
    server.listen(PORT, HOST);
    const socket  = new Server(server, { cors: { origin: '*' } });

    socket.on('connection', function(client) {
        const subscribe = redis.createClient({ host: REDIS_HOST, port: REDIS_PORT });
        const subscribe2 = redis.createClient({ host: REDIS_HOST, port: REDIS_PORT });
        subscribe.subscribe('realtime');

        subscribe.on("message", function(channel, message) {
            client.send(message);
            log('msg', "received from channel #" + channel + " : " + message);
        });

         subscribe2.subscribe('loop');

        subscribe2.on("message", function(channel, message) {
            client.send(message);
            log('msg', "received from channel #" + channel + " : " + message);
        });


        client.on('message', function(msg) {
            log('debug', msg);
        });

        client.on('disconnect', function() {
            log('warn', 'disconnecting from redis');
            subscribe.quit();
        });
    });
}

function log(type, msg) {

    var color   = '\u001b[0m',
        reset = '\u001b[0m';

    switch(type) {
        case "info":
            color = '\u001b[36m';
            break;
        case "warn":
            color = '\u001b[33m';
            break;
        case "error":
            color = '\u001b[31m';
            break;
        case "msg":
            color = '\u001b[34m';
            break;
        default:
            color = '\u001b[0m'
    }

    console.log(color + '   ' + type + '  - ' + reset + msg);
}