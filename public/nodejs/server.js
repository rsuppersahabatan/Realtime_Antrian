/*
    Node.js server script
    Required node packages: express, redis, socket.io

    If Use PM2 Use this
    > pm2 start public/nodejs/server.js --name "antrian-socket" --watch --ignore-watch "node_modules"
*/
const fs = require("fs");
const path = require("path");
const envPath = path.resolve(__dirname, "../../.env");
if (fs.existsSync(envPath)) {
  const envConfig = fs.readFileSync(envPath, "utf8");
  envConfig.split("\n").forEach((line) => {
    const match = line.trim().match(/^([^#\s][^\s=]+)\s*=\s*(.*)$/);
    if (match) {
      process.env[match[1]] = match[2];
    }
  });
}

const PORT = parseInt(process.env.NODEJS_PORT || "8085", 10);
const HOST = "0.0.0.0";
const REDIS_HOST = process.env.REDIS_HOST || "localhost";
const REDIS_PORT = parseInt(process.env.REDIS_PORT || "6379", 10);
const APP_VERSION = "1.0.2";
// const APP_NAME = "Antrian Online Realtime - With Socket Power!";

var express = require("express"),
  http = require("http");

var app = express();
var server = http.createServer(app);

// --- Security headers for health-check endpoint ---
app.use((req, res, next) => {
  res.setHeader("X-Content-Type-Options", "nosniff");
  res.setHeader("X-Frame-Options", "DENY");
  res.setHeader("X-XSS-Protection", "1; mode=block");
  next();
});

// Health-check endpoint (useful for Docker healthcheck & load balancer)
app.get("/health", (_req, res) => {
  res.json({ status: "ok", version: APP_VERSION, uptime: process.uptime() });
});

const redis = require("redis");

const REDIS_PASSWORD = process.env.REDIS_PASSWORD || "";

// Build Redis URL safely
function buildRedisUrl() {
  let url = `redis://`;
  if (REDIS_PASSWORD) {
    // Encode password to handle special characters
    url += `:${encodeURIComponent(REDIS_PASSWORD)}@`;
  }
  url += `${REDIS_HOST}:${REDIS_PORT}`;
  return url;
}

const REDIS_URL = buildRedisUrl();

// Graceful shutdown handling
let shuttingDown = false;

function shutdown(signal) {
  if (shuttingDown) return;
  shuttingDown = true;
  log("info", `Received ${signal}, shutting down gracefully...`);

  // Close the HTTP server first (stop accepting new connections)
  server.close(() => {
    log("info", "HTTP server closed.");
    // Give pending operations a moment, then exit
    setTimeout(() => process.exit(0), 1000);
  });

  // Force exit after 10s if graceful shutdown hangs
  setTimeout(() => {
    log("error", "Forced shutdown after timeout.");
    process.exit(1);
  }, 10000);
}

process.on("SIGTERM", () => shutdown("SIGTERM"));
process.on("SIGINT", () => shutdown("SIGINT"));

// --- Global error handlers to prevent silent crashes ---
process.on("uncaughtException", (err) => {
  log("error", "UNCAUGHT EXCEPTION: " + (err && err.stack ? err.stack : err));
  // In production, you might want to exit and let Docker restart
  setTimeout(() => process.exit(1), 1000);
});

process.on("unhandledRejection", (reason) => {
  log(
    "error",
    "UNHANDLED REJECTION: " +
    (reason && reason.stack ? reason.stack : reason),
  );
  // Don't exit on unhandled rejection (recoverable), but log loudly
});

// Main redis client (for general use)
const client = redis.createClient({ url: REDIS_URL });
client.on("error", (err) =>
  log("error", "Redis client error: " + err.message),
);
client
  .connect()
  .then(() => {
    log(
      "info",
      "connected to redis server at " + REDIS_HOST + ":" + REDIS_PORT,
    );
  })
  .catch((err) => log("error", "Redis connect failed: " + err.message));

const { Server } = require("socket.io");

if (!module.parent) {
  server.listen(PORT, HOST, () => {
    log("info", `Socket.IO server listening on ${HOST}:${PORT}`);
  });

  const socket = new Server(server, {
    cors: {
      origin: process.env.CORS_ORIGIN || "*",
      methods: ["GET", "POST"],
    },
    transports: ["websocket", "polling"],
    pingTimeout: 60000,
    pingInterval: 25000,
  });

  socket.on("connection", function (socketClient) {
    log("info", "Client connected: " + socketClient.id);

    // Emit current server version to client
    socketClient.emit("version", APP_VERSION);

    // Create subscriber clients but DO NOT await in connection handler
    // to avoid blocking the WebSocket handshake
    const subscribe = client.duplicate();
    subscribe.on("error", (err) =>
      log("error", "Redis subscriber error: " + err.message),
    );

    const subscribe2 = client.duplicate();
    subscribe2.on("error", (err) =>
      log("error", "Redis subscriber2 error: " + err.message),
    );

    let isConnected = false;
    let timeoutId;

    // Set a timeout for subscribers to connect (safety net)
    timeoutId = setTimeout(() => {
      if (!isConnected) {
        log(
          "warn",
          "Redis subscribers timeout for client: " + socketClient.id,
        );
        // Cleanup stale subscribers
        try {
          subscribe.disconnect();
        } catch (e) {
          /* ignore */
        }
        try {
          subscribe2.disconnect();
        } catch (e) {
          /* ignore */
        }
      }
    }, 15000);

    Promise.all([subscribe.connect(), subscribe2.connect()])
      .then(() => {
        isConnected = true;
        clearTimeout(timeoutId);
        log(
          "info",
          "Redis subscribers connected for client: " + socketClient.id,
        );

        return Promise.all([
          subscribe.subscribe("realtime", (message, channel) => {
            if (socketClient.connected) {
              socketClient.send(message);
              log(
                "msg",
                "received from channel #" + channel + " : " + message,
              );
            }
          }),
          subscribe2.subscribe("loop", (message, channel) => {
            if (socketClient.connected) {
              socketClient.send(message);
              log(
                "msg",
                "received from channel #" + channel + " : " + message,
              );
            }
          }),
        ]);
      })
      .catch((err) => {
        clearTimeout(timeoutId);
        log("error", "Redis subscriber connect failed: " + err.message);
        // Optionally disconnect the client if Redis is unavailable
        if (socketClient.connected) {
          socketClient.disconnect(true);
        }
      });

    socketClient.on("message", function (msg) {
      log("debug", msg);
    });

    socketClient.on("disconnect", async function () {
      log("log", "Client disconnected: " + socketClient.id);
      clearTimeout(timeoutId);
      if (isConnected) {
        try {
          await subscribe.quit();
        } catch (e) {
          /* ignore */
        }
        try {
          await subscribe2.quit();
        } catch (e) {
          /* ignore */
        }
      } else {
        // If not yet connected, just disconnect without quit
        try {
          subscribe.disconnect();
        } catch (e) {
          /* ignore */
        }
        try {
          subscribe2.disconnect();
        } catch (e) {
          /* ignore */
        }
      }
    });
  });
}

function log(type, msg) {
  var color = "\u001b[0m",
    reset = "\u001b[0m";

  switch (type) {
    case "info":
      color = "\u001b[36m";
      break;
    case "warn":
      color = "\u001b[33m";
      break;
    case "error":
      color = "\u001b[31m";
      break;
    case "msg":
      color = "\u001b[34m";
      break;
    default:
      color = "\u001b[0m";
  }

  console.log(color + "   " + type + "  - " + reset + msg);
}