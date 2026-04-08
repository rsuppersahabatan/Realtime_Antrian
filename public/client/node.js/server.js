/*
    Node.js server script
    Required node packages: express, redis, socket.io
*/
const fs = require('fs');
const path = require('path');
const envPath = path.resolve(__dirname, '../../../.env');
if (fs.existsSync(envPath)) {
    const envConfig = fs.readFileSync(envPath, 'utf8');
    envConfig.split('\n').forEach(line => {
        const match = line.trim().match(/^([^#\s][^\s=]+)\s*=\s*(.*)$/);
        if (match) {
            process.env[match[1]] = match[2];
        }
    });
}

const PORT = 8085;
const HOST = '0.0.0.0';
const REDIS_HOST = process.env.REDIS_HOST || 'localhost';
const REDIS_PORT = process.env.REDIS_PORT || 6379;

var express = require('express'),
    http = require('http');

var app = express();
var server = http.createServer(app);

const redis = require('redis');

const REDIS_PASSWORD = process.env.REDIS_PASSWORD || '';

let REDIS_URL = `redis://`;
if (REDIS_PASSWORD) {
    REDIS_URL += `:${REDIS_PASSWORD}@`;
}
REDIS_URL += `${REDIS_HOST}:${REDIS_PORT}`;

// Main redis client (for general use)
const client = redis.createClient({ url: REDIS_URL });
client.on('error', (err) => log('error', 'Redis client error: ' + err.message));
client.connect().then(() => {
    log('info', 'connected to redis server at ' + REDIS_HOST + ':' + REDIS_PORT);
}).catch((err) => log('error', 'Redis connect failed: ' + err.message));

const { Server } = require('socket.io');

if (!module.parent) {
    server.listen(PORT, HOST);
    const socket = new Server(server, { cors: { origin: '*' } });

    socket.on('connection', async function(socketClient) {
        // Redis v4: subscriber harus client terpisah yang di-duplicate
        const subscribe = client.duplicate();
        const subscribe2 = client.duplicate();

        await subscribe.connect();
        await subscribe2.connect();

        // Redis v4: subscribe menggunakan callback dalam fungsi subscribe()
        await subscribe.subscribe('realtime', (message, channel) => {
            socketClient.send(message);
            log('msg', "received from channel #" + channel + " : " + message);
        });

        await subscribe2.subscribe('loop', (message, channel) => {
            socketClient.send(message);
            log('msg', "received from channel #" + channel + " : " + message);
        });

        socketClient.on('message', function(msg) {
            log('debug', msg);
        });

        socketClient.on('disconnect', async function() {
            log('log', 'disconnecting from redis');
            await subscribe.quit();
            await subscribe2.quit();
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