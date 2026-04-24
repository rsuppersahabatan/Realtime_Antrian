# Realtime Antrian

Sistem antrian realtime berbasis web untuk rumah sakit/klinik/instansi pelayanan publik. Pengunjung mengambil nomor antrian secara mandiri dengan input NIK, petugas loket memanggil antrian dari panel admin, dan layar publik memperbarui nomor yang sedang dipanggil secara realtime melalui Redis Pub/Sub + Socket.IO — tanpa refresh.

Dibangun di atas **CodeIgniter 3** (backend & admin panel), **Node.js + Socket.IO** (realtime gateway), **Redis** (pub/sub & cache), dan **MySQL** (data antrian). Seluruh stack siap dijalankan via **Docker Compose**.

## Fitur

- **Pengambilan tiket mandiri** — pengunjung pilih layanan dan input NIK (16 digit) untuk mencetak nomor antrian.
- **Panel petugas (loket)** — panggil berikutnya, panggil ulang, tandai selesai/batal, pindah antrian.
- **Layar display publik** — menampilkan nomor yang sedang dipanggil di masing-masing loket, update realtime tanpa refresh.
- **Realtime push** — perubahan status antrian langsung dikirim ke display & halaman petugas via Socket.IO, dipantik oleh Redis `PUBLISH` dari sisi PHP.
- **Manajemen master data** — CRUD layanan, loket, pengguna, dan grup (role) berbasis Ion Auth.
- **Reset harian otomatis** — nomor urut direset per tanggal, riwayat tetap tersimpan.
- **Multi-layanan** — setiap layanan punya prefix huruf sendiri (A, B, C, …) dan bisa dilayani lebih dari satu loket.
- **REST API** — endpoint JSON untuk modul **layanan**, **loket**, **antrian**, **users**, dan **groups** (list, create, update, delete, panggil antrian, simpan panggilan/panggil ulang, aktivasi user, dsb.) yang dilindungi **HTTP Basic Auth** via [chriskacerguis/codeigniter-restserver](https://github.com/chriskacerguis/codeigniter-restserver). Lihat [REST API](#rest-api).
- **Deploy sekali jalan** — `docker-compose up -d` menyiapkan PHP+Apache, Node.js, Redis, dan MySQL.

## Tampilan Aplikasi

### Halaman Publik

**Landing page — pengambilan nomor antrian oleh pengunjung**

![Landing Page](ss/landing-page.png)

**Display antrian — layar publik yang menampilkan nomor yang sedang dipanggil**

![Display Antrian](ss/display.png)

### Panel Admin

**Halaman login admin (Ion Auth)**

![Login](ss/login-page.png)

**Dashboard utama — ringkasan antrian hari ini**

![Dashboard](ss/dashboard-1.png)

**Manajemen antrian — daftar seluruh tiket hari ini beserta statusnya**

![Manajemen Antrian](ss/dashboard-antrian.png)

**Panel panggilan — dipakai petugas loket untuk memanggil antrian**

![Panel Panggilan](ss/dashboard-panggilan.png)

**Master layanan — CRUD jenis layanan dan prefix hurufnya**

![Master Layanan](ss/dashboard-layanan.png)

**Master loket — CRUD loket/meja petugas, status buka/tutup, dan layanan yang dilayani**

![Master Loket](ss/dashboard-loket.png)

**Manajemen pengguna — CRUD akun petugas**

![Pengguna](ss/dashboard-users.png)

**Manajemen grup/role**

![Grup](ss/dashboard-groups.png)

## Arsitektur Singkat

```
[Pengunjung]  --HTTP-->  [CodeIgniter / Welcome]  --INSERT-->  [MySQL]
                                     |
                                     +--PUBLISH "realtime"-->  [Redis]
                                                                  |
                                                                  v
[Petugas / Panel]  <--HTTP-->  [CodeIgniter / admin]         [Node.js + Socket.IO]
                                     |                            ^
                                     +--PUBLISH "realtime"---------+
                                                                  |
[Layar Display]  <======= WebSocket (Socket.IO) =================+
```

Alur realtime:

1. Aksi tiket (ambil / panggil / selesai) dijalankan oleh PHP dan menulis ke MySQL.
2. PHP memanggil `PUBLISH realtime <pesan>` ke Redis.
3. Node.js (`public/nodejs/server.js`) berlangganan channel `realtime` & `loop`, lalu mem-broadcast ke semua klien Socket.IO.
4. Halaman display/petugas menerima pesan dan memperbarui tampilan.

## Struktur Folder

```
Realtime_Antrian/
├── application/          # Kode CodeIgniter (controller, model, view)
│   ├── controllers/      # Welcome, Client, Auth, admin/*, api/*
│   │   └── api/          # REST controllers: Layanan, Loket, Antrian, Users, Groups
│   ├── config/
│   │   └── rest.php      # Konfigurasi REST server (basic auth, dll.)
│   └── views/            # welcome_message, client/display, admin/*
├── bin/                  # Helper CLI (install.php, server.sh, dll.)
├── database/
│   └── schema.sql        # Skema + seeder awal (layanan, loket, users)
├── public/
│   ├── index.php         # Front controller
│   ├── assets/           # CSS/JS/image admin
│   └── nodejs/           # Gateway realtime (Express + Socket.IO + Redis)
├── ss/                   # Screenshot untuk README
├── Dockerfile            # Image PHP 7.4 + Apache
├── Dockerfile.node       # Image Node.js 18 (Socket.IO gateway)
├── docker-compose.yml    # PHP, Node.js, Redis, MySQL
└── composer.json
```

## Prasyarat

- **Docker** + **Docker Compose** (cara termudah — semua dependency sudah diatur)

Atau, jika ingin menjalankan tanpa Docker:

- PHP **7.4+** dengan ekstensi `mysqli`, `pdo_mysql`, Apache/Nginx + `mod_rewrite`
- **MySQL 5.7+** atau MariaDB setara
- **Redis 6+**
- **Node.js 18+**
- **Composer**

## Cara Menjalankan — Docker (direkomendasikan)

1. **Clone & masuk folder**

   ```bash
   git clone <repo-url> Realtime_Antrian
   cd Realtime_Antrian
   ```

2. **(Opsional) buat file `.env`** di root untuk meng-override default:

   ```env
   # ====================================
   # Realtime Antrian - Environment Config
   # ====================================
   # Copy this file to .env and adjust values as needed
   # cp .env.example .env

   # ----- Redis -----
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   REDIS_PASSWORD=Y5HZk8u07*fY

   # ----- MySQL -----
   DB_HOST=mysql
   DB_USER=root
   DB_PASS=toor
   DB_NAME=antrian_db
   MYSQL_ROOT_PASSWORD=root123

   # ----- Ports -----
   PHP_PORT=8080
   NODEJS_PORT=8085
   MYSQL_PORT=3306
   REDIS_EXT_PORT=6379

   # ----- Socket.IO -----
   # URL absolut ke service Socket.IO. Kosongkan di produksi (agar view pakai
   # same-origin via reverse proxy Nginx/Apache). Untuk dev Windows tanpa proxy,
   # isi dengan endpoint Node.js langsung, misal:
   # SOCKET_URL=http://127.0.0.1:8085
   SOCKET_URL=


   # ---- API BASIC AUTH ----
   USER_API=
   PASS_API=
   ```

3. **Build & jalankan stack**

   ```bash
   # Hentikan stack lama jika ada
   docker-compose down

   # Build ulang tanpa cache supaya server.js terbaru benar-benar tersalin
   docker-compose up --build -d
   ```

4. **Import skema database** (sekali di awal)

   ```bash
   docker exec -i antrian_mysql mysql -uroot -proot123 < database/schema.sql
   ```

5. **Akses aplikasi**

   | Halaman | URL |
   |---|---|
   | Landing / ambil antrian | <http://localhost:8080/> |
   | Display antrian publik | <http://localhost:8080/client> |
   | Panel admin | <http://localhost:8080/auth/login> |
   | Socket.IO gateway | `ws://localhost:8085` |

   **Login default:** `admin@admin.com` / `password` (ubah segera setelah login pertama).

## Cara Menjalankan — Tanpa Docker

1. Install dependency PHP: `composer install`
2. Import `database/schema.sql` ke MySQL.
3. Sesuaikan koneksi DB di `application/config/database.php` dan Redis di `application/config/redis.php`.
4. Arahkan document root web server ke folder `public/`.
5. Jalankan Node.js realtime gateway:

   ```bash
   cd public/nodejs
   npm install
   node server.js
   # atau via PM2
   pm2 start server.js --name "antrian-socket" --watch --ignore-watch "node_modules"
   ```
6. Tambahkan Konfigurasi untuk Nginx Server

   ```bash
   location / {
      try_files $uri $uri/ /index.php?$query_string;
   }

   location /socket.io/ {
      proxy_pass http://127.0.0.1:8085;
      proxy_http_version 1.1;
      proxy_set_header Upgrade $http_upgrade;
      proxy_set_header Connection "upgrade";
      proxy_set_header Host $host;
      proxy_set_header X-Real-IP $remote_addr;
      proxy_read_timeout 3600s;
   }
   ```

## Skema Database (ringkas)

- `layanan` — kategori antrian + prefix huruf (A/B/C/…)
- `loket` — meja petugas, status buka/tutup, terkait ke `layanan`
- `antrian` — transaksi tiket harian (`nomor_antrian`, `nomor_urut`, `status`, `id_loket`, NIK, timestamp)
- `users`, `groups`, `users_groups`, `login_attempts` — **Ion Auth** untuk autentikasi admin
- `admin_preferences` — preferensi tampilan AdminLTE

Detail lengkap & seeder contoh lihat [database/schema.sql](database/schema.sql).

## Channel Redis

| Channel | Dipakai untuk |
|---|---|
| `realtime` | Broadcast event tiket: antrian baru terbit, antrian dipanggil, panggil ulang, selesai, batal. |
| `loop` | Pesan carousel/ticker pada display publik (opsional). |

Format pesan mengikuti konvensi string sederhana (mis. `antrian-baru-A12`, `panggil-A12-loket-1`) agar mudah di-parse oleh frontend display.

## REST API

Modul **layanan**, **loket**, **antrian**, **users**, dan **groups** diekspos sebagai REST API JSON di bawah prefix `/api/*`, dibangun dengan [chriskacerguis/codeigniter-restserver](https://github.com/chriskacerguis/codeigniter-restserver). Controller ada di [application/controllers/api/](application/controllers/api/) dan konfigurasi server REST di [application/config/rest.php](application/config/rest.php).

### Autentikasi

Seluruh endpoint dilindungi **HTTP Basic Auth**. Kredensial default ada di [application/config/rest.php](application/config/rest.php) pada array `$config['rest_valid_logins']`:

```php
$config['rest_valid_logins'] = [
    'admin' => 'antrian2024',
];
```

> **Ganti kredensial default sebelum deploy ke production.** Tambah user baru dengan menambah entry pada array di atas. Untuk memaksa HTTPS, set `$config['force_https'] = true` di file yang sama.

Request tanpa header `Authorization` akan menerima response `401 Unauthorized` beserta header `WWW-Authenticate: Basic realm="Realtime Antrian REST API"`.

### Daftar Endpoint

Base URL (Docker default): `http://localhost:8080/api`

#### Layanan — `/api/layanan`

| Method | URI | Keterangan |
|---|---|---|
| `GET` | `/api/layanan` | List semua layanan |
| `GET` | `/api/layanan/{id}` | Detail satu layanan |
| `POST` | `/api/layanan` | Tambah layanan. Body: `kode_huruf`, `nama_layanan`, `keterangan?` |
| `PUT` | `/api/layanan/{id}` | Update partial (`kode_huruf` / `nama_layanan` / `keterangan`) |
| `DELETE` | `/api/layanan/{id}` | Hapus layanan |

#### Loket — `/api/loket`

| Method | URI | Keterangan |
|---|---|---|
| `GET` | `/api/loket` | List semua loket |
| `GET` | `/api/loket/{id}` | Detail satu loket |
| `GET` | `/api/loket/buka` | Loket yang sedang buka. Query: `?with_last=1&tanggal=YYYY-MM-DD` untuk sertakan nomor antrian terakhir hari tersebut per loket |
| `POST` | `/api/loket` | Tambah loket. Body: `id_layanan`, `nama_loket`, `status_buka?` (`buka`\|`tutup`) |
| `PUT` | `/api/loket/status/{id}` | Update status. Body: `status_buka` (`buka`\|`tutup`) |
| `DELETE` | `/api/loket/{id}` | Hapus loket |

#### Antrian — `/api/antrian`

| Method | URI | Keterangan |
|---|---|---|
| `GET` | `/api/antrian` | Daftar antrian + rekap per status. Query: `?tanggal=YYYY-MM-DD` (default hari ini) |
| `POST` | `/api/antrian` | Generate nomor antrian baru. Body: `id_layanan`, `nik?` (16 digit) |
| `POST` | `/api/antrian/call` | Panggil antrian berikutnya untuk sebuah loket. Body: `id_loket` |
| `POST` | `/api/antrian/panggilansimpan` | Simpan panggilan manual / panggil ulang tiket tertentu. Body: `id_antrian`, `id_loket`. Validasi: layanan loket harus cocok dengan layanan antrian; tiket `selesai`/`batal` ditolak. Response memuat flag `is_ulang` bila tiket sudah berstatus `dipanggil` sebelumnya |
| `PUT` | `/api/antrian/selesai/{id}` | Tandai antrian selesai (isi `waktu_selesai`) |
| `PUT` | `/api/antrian/batal/{id}` | Tandai antrian batal |
| `DELETE` | `/api/antrian/{id}` | Hapus record antrian |

#### Users — `/api/users`

Wrapper REST atas **Ion Auth**. Semua response user sudah memfilter field sensitif (`password`, `salt`, `remember_code`, `forgotten_password_code`, `activation_code`).

| Method | URI | Keterangan |
|---|---|---|
| `GET` | `/api/users` | List semua user beserta daftar `groups` masing-masing |
| `GET` | `/api/users/{id}` | Detail satu user + `groups` |
| `POST` | `/api/users` | Tambah user baru. Body: `email` (required), `password` (required, min sesuai `ion_auth.min_password_length`), `first_name?`, `last_name?`, `phone?`, `company?`, `username?` (default: gabungan first+last, fallback local-part email), `groups[]?` (array id group) |
| `PUT` | `/api/users/{id}` | Update partial. Body: `first_name?`, `last_name?`, `phone?`, `company?`, `password?`, `groups[]?` (jika dikirim, **replace** seluruh keanggotaan group user) |
| `PUT` | `/api/users/activate/{id}` | Aktifkan user (`active=1`) |
| `PUT` | `/api/users/deactivate/{id}` | Nonaktifkan user (`active=0`) |
| `DELETE` | `/api/users/{id}` | Hapus user |

#### Groups — `/api/groups`

CRUD role/group via **Ion Auth**, plus kolom `bgcolor` untuk label AdminLTE. Group `admin` (sesuai `ion_auth.admin_group`) dilindungi — tidak dapat di-rename maupun dihapus.

| Method | URI | Keterangan |
|---|---|---|
| `GET` | `/api/groups` | List semua group |
| `GET` | `/api/groups/{id}` | Detail satu group |
| `GET` | `/api/groups/users/{id}` | List user yang menjadi anggota group {id} |
| `POST` | `/api/groups` | Tambah group. Body: `name` (required, regex `^[A-Za-z0-9_-]+$`), `description?`, `bgcolor?` (hex, mis. `#2196F3`) |
| `PUT` | `/api/groups/{id}` | Update partial. Body: `name?`, `description?`, `bgcolor?`. Rename group `admin` ditolak (`403 Forbidden`) |
| `DELETE` | `/api/groups/{id}` | Hapus group. Menghapus group `admin` ditolak (`403 Forbidden`) |

### Format Response

Semua response menggunakan `Content-Type: application/json` dengan struktur umum:

```json
{
  "status": true,
  "message": "Layanan berhasil ditambahkan",
  "data": { "id": 4, "kode_huruf": "D", "nama_layanan": "Rekam Medis", "keterangan": null }
}
```

Status HTTP mengikuti konvensi: `200 OK`, `201 Created`, `400 Bad Request`, `401 Unauthorized`, `403 Forbidden` (mis. menghapus group `admin`), `404 Not Found`, `409 Conflict` (mis. `panggilansimpan` dengan layanan loket tidak cocok atau tiket sudah `selesai`/`batal`), `500 Internal Error`.

### Contoh Pemakaian

**cURL — list layanan**

```bash
curl -u admin:antrian2024 http://localhost:8080/api/layanan
```

**cURL — generate nomor antrian baru**

```bash
curl -u admin:antrian2024 \
     -X POST \
     -d "id_layanan=1&nik=3201010101010001" \
     http://localhost:8080/api/antrian
```

**cURL — panggil antrian berikutnya untuk loket 1**

```bash
curl -u admin:antrian2024 \
     -X POST \
     -d "id_loket=1" \
     http://localhost:8080/api/antrian/call
```

**cURL — simpan panggilan manual / panggil ulang tiket tertentu**

```bash
curl -u admin:antrian2024 \
     -X POST \
     -d "id_antrian=12&id_loket=1" \
     http://localhost:8080/api/antrian/panggilansimpan
```

**cURL — tandai antrian selesai**

```bash
curl -u admin:antrian2024 \
     -X PUT \
     http://localhost:8080/api/antrian/selesai/12
```

**cURL — tambah user dengan assignment group**

```bash
curl -u admin:antrian2024 \
     -X POST \
     -d "email=petugas1@contoh.id&password=rahasia123&first_name=Budi&last_name=Santoso&groups[]=2" \
     http://localhost:8080/api/users
```

**cURL — aktivasi / deaktivasi user**

```bash
curl -u admin:antrian2024 -X PUT http://localhost:8080/api/users/activate/5
curl -u admin:antrian2024 -X PUT http://localhost:8080/api/users/deactivate/5
```

**cURL — list user dalam sebuah group**

```bash
curl -u admin:antrian2024 http://localhost:8080/api/groups/users/2
```

**Postman / Insomnia** — pilih Auth type **Basic Auth**, isi username & password.

**Header manual**

```
Authorization: Basic YWRtaW46YW50cmlhbjIwMjQ=
```

(value = `base64("admin:antrian2024")`)

## Troubleshooting

- **Display tidak update** — cek container `antrian_nodejs` berjalan dan port `8085` terbuka. Pastikan browser tidak diblokir CORS/mixed-content.
- **`redis` connection refused** — pastikan service `redis` up (`docker-compose ps`) dan `REDIS_HOST`/`REDIS_PORT` konsisten antara PHP dan Node.js.
- **Nomor antrian tidak reset** — reset dilakukan per tanggal (`tanggal = CURDATE()` di tabel `antrian`). Pastikan timezone server sesuai.
- **`server.js` tidak ketemu** di container Node.js — rebuild tanpa cache: `docker-compose up --build -d`.
- **REST API selalu 401** — pastikan header `Authorization: Basic ...` terkirim (Apache `mod_php` biasanya aman, beberapa setup FastCGI perlu menambahkan `SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1` di `.htaccess`). Cek juga password di [application/config/rest.php](application/config/rest.php) sesuai dengan yang dikirim.
- **REST API 404 padahal URL benar** — pastikan `mod_rewrite` aktif dan `public/.htaccess` terbaca; tanpa itu URL harus berbentuk `http://host/index.php/api/layanan`.

## aaPanel Users by Cloude

1. Cek user PHP-FPM

   ```bash
   ps aux | grep php-fpm | head -3
   ```

   Biasanya di aaPanel: www.

2. Fix ownership & permission

   ```bash
      cd /www/wwwroot/antrian
      sudo chown www:www .env
      sudo chmod 644 .env
   ```
   Kalau mau lebih ketat (recommended): ```sudo chmod 640 .env    # owner rw, group r, other none```

3. Cek parent directory executable

   ```bash
      /www/wwwroot/antrian/ harus x untuk user www:
      ls -ld /www/wwwroot/antrian
      sudo chmod 755 /www/wwwroot/antrian
   ```

4. Cek open_basedir aaPanel

   Di aaPanel → Website → Settings → Config File, cari open_basedir. Pastikan /www/wwwroot/antrian/ ada di daftarnya (seharusnya default sudah termasuk).

5. Verifikasi

   ``` sudo -u www cat /www/wwwroot/antrian/.env ```
   Kalau bisa tampil isinya → permission beres. Reload PHP-FPM:

   ``` sudo /etc/init.d/php-fpm-* reload    # sesuai versi PHP ```
   Setelah itu refresh halaman 

## Credits & Referensi

- [CodeIgniter 3](https://github.com/bcit-ci/CodeIgniter)
- [Ion Auth](https://github.com/benedmunds/CodeIgniter-Ion-Auth)
- [chriskacerguis/codeigniter-restserver](https://github.com/chriskacerguis/codeigniter-restserver) untuk REST API
- [AdminLTE](https://adminlte.io/) untuk template panel admin
- [Socket.IO](https://socket.io/) & [node-redis](https://github.com/redis/node-redis) untuk realtime gateway
- Pola dasar realtime gateway terinspirasi dari [vanuganti/realtime](http://github.com/vanuganti/realtime)

## Unknowledge & Disclaimer

- [Forked From Realtime Antrian Bank](https://github.com/siagung/CI_Redis_Realtime_Antrian_Bank)
- [Clone and Modification From CI AdminLTE](https://github.com/domProjects/CI-AdminLTE)
- [Use Composer Style to Use Codeigniter](https://github.com/kenjis/codeigniter-composer-installer.git)
- [Codeigniter 3 Full PHP 8 Supports](https://github.com/pocketarc/codeigniter.git)
- [Use AI ~ AntiGravity and Cloude For Task List](https://topidesta.my.id/era-ai-untuk-programmer-30an/)

## Lisensi

Project ini dirilis di bawah lisensi MIT.
