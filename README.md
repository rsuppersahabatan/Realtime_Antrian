Panggilan Antrian Realtime
==========================

Codeigniter, Node.js, Socket.IO & Redis
----------------------------------------

Sistem antrian realtime menggunakan CodeIgniter (PHP) sebagai backend, Node.js + Socket.IO untuk komunikasi realtime, dan Redis sebagai message broker (Pub/Sub).

Kebutuhan:
----------
- Docker & Docker Compose **atau**
- PHP 5.6+ / 7.x, Node.js, Redis server (instalasi manual)

Mulai dengan Docker (Rekomendasi):
-----------------------------------

1. Clone repo dan masuk ke folder project

2. Salin file environment

   ```bash
   cp .env.example .env
   ```

3. Sesuaikan konfigurasi di `.env` jika diperlukan (default sudah bisa langsung jalan)

4. Jalankan semua service

   ```bash
   docker-compose up -d
   ```

5. Akses aplikasi

   | Service         | URL                        |
   |-----------------|----------------------------|
   | PHP (Panggilan) | http://localhost:8080       |
   | Client Display  | http://localhost:8085       |
   | Redis           | localhost:6379              |
   | MySQL           | localhost:3306              |

6. Untuk menghentikan semua service

   ```bash
   docker-compose down
   ```

Konfigurasi Environment (`.env`):
----------------------------------

| Variabel            | Default       | Keterangan                  |
|---------------------|---------------|-----------------------------|
| `REDIS_HOST`        | redis         | Hostname Redis              |
| `REDIS_PORT`        | 6379          | Port Redis                  |
| `REDIS_PASSWORD`    |               | Password Redis (opsional)   |
| `DB_HOST`           | mysql         | Hostname MySQL              |
| `DB_USER`           | antrian       | Username MySQL              |
| `DB_PASS`           | antrian123    | Password MySQL              |
| `DB_NAME`           | antrian_db    | Nama database               |
| `MYSQL_ROOT_PASSWORD` | root123     | Password root MySQL         |
| `PHP_PORT`          | 8080          | Port akses PHP dari host    |
| `NODEJS_PORT`       | 8085          | Port akses Node.js dari host|
| `MYSQL_PORT`        | 3306          | Port akses MySQL dari host  |
| `REDIS_EXT_PORT`    | 6379          | Port akses Redis dari host  |

Mulai tanpa Docker (Manual):
-----------------------------

1. Pastikan Redis server sudah berjalan (default port 6379)

2. Clone repo ke folder root http

3. Masuk ke folder `node.js/`, lalu install dependensi dan jalankan server

   ```bash
   cd node.js
   npm install
   node server.js
   ```

4. Buka browser ke root folder project (PHP app)

5. Buka tab baru ke `node.js/client.html` untuk tampilan client

`Start server.js`

![start server](https://raw.githubusercontent.com/siagung/CI_Redis_Realtime_Antrian_Bank/master/assets/image/start-server.png)

`Panggilan`

![start server](https://raw.githubusercontent.com/siagung/CI_Redis_Realtime_Antrian_Bank/master/assets/image/panggil.png)

`Client`

![start server](https://raw.githubusercontent.com/siagung/CI_Redis_Realtime_Antrian_Bank/master/assets/image/client.png)

Arsitektur:
-----------

```
Browser (Client Display)
    |
    | Socket.IO (ws://localhost:8085)
    v
Node.js Server --- subscribe ---> Redis <--- publish --- PHP (CodeIgniter)
                                                            |
                                                            v
                                                          MySQL
```

Informasi Tambahan:
-------------------

- [Realtime Node.js, Socket.io & Redis](http://github.com/vanuganti/realtime)
- [CodeIgniter Redis Library](https://github.com/joelcox/codeigniter-redis)
