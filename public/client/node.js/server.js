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
    server.listen(PORT, HOST, () => {
        log('info', `Socket.IO server listening on ${HOST}:${PORT}`);
    });

    const socket = new Server(server, {
        cors: { origin: '*' },
        transports: ['websocket', 'polling'],
        pingTimeout: 60000,
        pingInterval: 25000,
    });

    socket.on('connection', function(socketClient) {
        log('info', 'Client connected: ' + socketClient.id);

        // Create subscriber clients but DO NOT await in connection handler
        // to avoid blocking the WebSocket handshake
        const subscribe = client.duplicate();
        const subscribe2 = client.duplicate();

        let isConnected = false;

        // Connect asynchronously without blocking the connection handler
        Promise.all([subscribe.connect(), subscribe2.connect()])
            .then(() => {
                isConnected = true;
                log('info', 'Redis subscribers connected for client: ' + socketClient.id);

                return Promise.all([
                    subscribe.subscribe('realtime', (message, channel) => {
                        if (socketClient.connected) {
                            socketClient.send(message);
                            log('msg', "received from channel #" + channel + " : " + message);
                        }
                    }),
                    subscribe2.subscribe('loop', (message, channel) => {
                        if (socketClient.connected) {
                            socketClient.send(message);
                            log('msg', "received from channel #" + channel + " : " + message);
                        }
                    }),
                ]);
            })
            .catch((err) => {
                log('error', 'Redis subscriber connect failed: ' + err.message);
            });

        socketClient.on('message', function(msg) {
            log('debug', msg);
        });

        socketClient.on('disconnect', async function() {
            log('log', 'Client disconnected: ' + socketClient.id);
            if (isConnected) {
                try { await subscribe.quit(); } catch(e) { /* ignore */ }
                try { await subscribe2.quit(); } catch(e) { /* ignore */ }
            } else {
                // If not yet connected, just disconnect without quit
                try { subscribe.disconnect(); } catch(e) { /* ignore */ }
                try { subscribe2.disconnect(); } catch(e) { /* ignore */ }
            }
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