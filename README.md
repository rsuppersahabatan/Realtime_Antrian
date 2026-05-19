## Donasi ❤

Klik link dibawah untuk mendukung pengembangan

[![Donate trakteer](https://img.shields.io/badge/Donate-Trakteer-red?style=for-the-badge&link=https%3A%2F%2Ftrakteer.id%2Fmdestafadilah%2Ftip&labelColor=%239f39b5&color=%2300bcd4)](https://trakteer.id/mdestafadilah/tip)
[![Donate saweria](https://img.shields.io/badge/Donate-Saweria-red?style=for-the-badge&link=https%3A%2F%2Fsaweria.co%2Fmdestafadilah&labelColor=%239f39b5&color=%2300bcd4)](https://saweria.co/mdestafadilah)

# Realtime Antrian

Sistem antrian realtime berbasis web untuk rumah sakit/klinik/instansi pelayanan publik. Pengunjung mengambil nomor antrian secara mandiri (di halaman publik **NIK wajib**), petugas loket memanggil antrian dari panel admin atau via API yang mem-publish ke Redis, dan layar publik (`/client/{id}`) memperbarui nomor yang dipanggil melalui **Redis Pub/Sub + Socket.IO** — tanpa refresh.

Dibangun di atas **CodeIgniter 3** (backend & admin panel), **Node.js + Socket.IO** (realtime gateway), **Redis** (pub/sub), dan **MySQL** (data antrian). Seluruh stack siap dijalankan via **Docker Compose**.

## Fitur

- **Pengambilan tiket mandiri** (`Welcome`) — pengunjung pilih layanan dan input **NIK 16 digit** untuk mencetak nomor; PHP mem-publish event **`antrian-baru-{nomor}`** ke Redis (berguna bagi subscriber lain; **`POST /api/antrian`** membuat tiket tanpa publish event ini).
- **Panel petugas (admin/panggilan)** — panggil berikutnya, panggil ulang (broadcast saja), memanggil tiket tertentu lewat flow admin; pembaruan loket dapat diatur di master loket (`buka` / `tutup`).
- **Manajemen antrian (admin)** — daftar antrian harian beserta status; operasi **selesai** / **batal** seperti yang tersedia di UI admin.
- **Multi-display TV** — entitas **client** (profil layar): setiap layar dipetakan ke beberapa loket; URL publik **`/client`** memilih layar aktif, **`/client/{id}`** membuka layar untuk `client` tersebut.
- **Pengaturan tampilan display** (`admin/display`) — skema warna, konten/video (mis. YouTube atau lokal), teks footer, mode footer statis/jalan.
- **Layar display publik** — menampilkan nomor yang sedang dipanggil per loket yang di-assign ke client tersebut; menyaring pesan realtime agar hanya loket milik layar tersebut yang mengubah tampilan.
- **Realtime push** — panel memanggil & endpoint **`/api/panggilan/*`** mem-publish pesan **`loketXX-NOMOR`** (opsional sufiks `|KETERANGAN`) ke channel Redis `realtime`; gateway Node membroadcast via Socket.IO. Endpoint **`POST /api/antrian/call`** hanya mengubah basis data **tanpa** publish ke display (gunakan **`POST /api/panggilan/call`** bila layar harus ikut berubah).
- **Master data** — CRUD layanan, loket (termasuk assign user ke loket melalui relasi **`loket_user`**), pengguna, grup (**Ion Auth**), dan **client** (profil layar + loket via **`client_loket`**).
- **Reset harian otomatis** — nomor urut per kombinasi **tanggal** + layanan (`tanggal` di tabel antrian).
- **Multi-layanan** — tiap layanan punya **`kode_huruf`** (A, B, C, …) dan dapat dilayani oleh lebih dari satu loket.
- **REST API** — modul **`layanan`**, **`loket`**, **`antrian`**, **`panggilan`** (panggilan + broadcast seperti admin), **`dashboard`** (statistik ringkas/server), **`users`**, **`groups`**; dilindungi **HTTP Basic Auth** via [chriskacerguis/codeigniter-restserver](https://github.com/chriskacerguis/codeigniter-restserver). Spesifikasi tambahan ada di [docs/api.json](docs/api.json). Detail di [REST API](#rest-api).
- **Legacy / uji cepat** — controller **`Call_Legacy`** untuk skenario uji Redis/panggilan lama (tidak digunakan alur utama).
- **Deploy sekali jalan** — `docker-compose up -d` menyiapkan PHP+Apache, Node.js, Redis, dan MySQL.

## Tampilan Aplikasi

### Halaman Publik

**Landing page — pengambilan nomor antrian oleh pengunjung**

![Landing Page](ss/client-welcome.png)

**Display antrian — layar publik yang menampilkan nomor yang sedang dipanggil**

![Display Antrian](ss/client-display-landing.png)

**Display antrian — tampilan detail saat nomor dipanggil**

![Display Antrian Detail](ss/client-display-landing-detail.png)

### Panel Admin

**Halaman login admin (Ion Auth)**

![Login](ss/login-page.png)

**Dashboard utama — ringkasan antrian hari ini**

![Dashboard](ss/admin-dashboard.png)

**Manajemen antrian — daftar seluruh tiket hari ini beserta statusnya**

![Manajemen Antrian](ss/admin-antrian.png)

**Panel panggilan — dipakai petugas loket untuk memanggil antrian**

![Panel Panggilan](ss/admin-panggilan.png)

**Master layanan — CRUD jenis layanan dan prefix hurufnya**

![Master Layanan](ss/admin-layanan.png)

**Master loket — CRUD loket/meja petugas, status buka/tutup, assign user ke loket, dan layanan yang dilayani**

![Master Loket](ss/admin-loket.png)

**Profil layar TV (client) — mapping loket per layar display**

![Profil Layar TV](ss/admin-display-antrian.png)

**Pengaturan tampilan display — skema warna, konten/video, footer, dan font per client**

![Pengaturan Display](ss/admin-display-antrian-setting.png)

Gunakan tautan **`/client`** dan **`/client/{id}`** di browser untuk daftar pemilih layar dan layar publik aktual.

**Manajemen pengguna — CRUD akun petugas**

![Pengguna](ss/admin-pengguna.png)

**Manajemen grup/role**

![Grup](ss/admin-group.png)

## Arsitektur Singkat

```
[Pengunjung]  --HTTP-->  [CodeIgniter / Welcome]  --INSERT-->  [MySQL]
                                     |
                                     +--PUBLISH "realtime" (antrian-baru-…) -->  [Redis]
                                                                                    |
                                                                                    v
[Petugas / Panel]  <--HTTP-->  [CodeIgniter / admin + api/panggilan]         [Node.js + Socket.IO]
                                     |                                               ^
                                     +--PUBLISH "realtime" (loketXX-NOMOR…)-------+
                                     |                                               |
[Layar Display /client/id]  <======== WebSocket (Socket.IO) ========================+
```

Alur realtime:

1. Tiket baru dari halaman publik menyimpan row di MySQL lalu **`PUBLISH realtime antrian-baru-{nomor_antrian}`** (lihat `Welcome::ambil`).
2. Panggilan dari **panel admin** atau **`POST /api/panggilan/*`** memperbarui data bila diperlukan, lalu **`PUBLISH realtime`** dengan pola **`loketXX-{nomor}`** atau **`loketXX-{nomor}|{keterangan}`** (`XX` = id loket **dua digit**, diisi nol di depan bila perlu, mis. loket `3` → `loket03`).
3. Node.js (`public/nodejs/server.js`) berlangganan channel **`realtime`** dan **`loop`**, lalu meneruskan string pesan ke klien Socket.IO.
4. Halaman **`/client/{id}`** mem-parse payload dan hanya mengubah UI untuk loket yang termasuk profil **client** tersebut; panel admin dapat memakai Socket.IO untuk indikator realtime bila diimplementasikan di view.

## Struktur Folder

```
Realtime_Antrian/
├── application/              # CodeIgniter (controller, model, view)
│   ├── controllers/          # Welcome, Client, Auth, Call_Legacy, admin/*, api/*
│   │   └── api/              # REST: Layanan, Loket, Antrian, Panggilan, Dashboard, Users, Groups
│   ├── config/
│   │   └── rest.php          # REST server + basic auth (username/password bisa dari env)
│   └── views/                # welcome_message, client/display|all, admin/*, dll.
├── bin/                      # install.php, router.php, server.sh, my-codeigniter.sh, check-diff.sh
├── database/
│   └── schema.sql            # Skema + seeder awal (+ client, loket_user, dll.)
├── docs/
│   └── api.json              # Ringkasan OpenAPI/OpenAPI-style untuk REST
├── public/
│   ├── index.php             # Front controller
│   ├── assets/               # CSS/JS (mis. frameworks/domprojects untuk publik/admin)
│   └── nodejs/               # Gateway Socket.IO + subscriber Redis
├── ss/                       # Screenshot README
├── .env.example              # Contoh variabel lingkungan
├── Dockerfile
├── Dockerfile.node
├── docker-compose.yml
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

2. **(Opsional) salin `.env.example` → `.env`** di root dan sesuaikan (nilai di bawah selaras dengan default **`docker-compose.yml`** bila tidak di-override):

   ```env
   # cp .env.example .env

   CI_ENV=production

   # Redis (service compose bernama `redis`; dari host Windows pakai localhost + port map)
   REDIS_HOST=redis
   REDIS_PORT=6379
   REDIS_PASSWORD=

   # MySQL — default aplikasi di compose: user `antrian`, password `antrian123`, DB `antrian_db`
   DB_HOST=mysql
   DB_USER=antrian
   DB_PASS=antrian123
   DB_NAME=antrian_db
   MYSQL_ROOT_PASSWORD=root123

   PHP_PORT=8080
   NODEJS_PORT=8085
   MYSQL_PORT=3306
   # Port Redis di host: default compose memetakan host:(container) = 6380:6379
   REDIS_EXT_PORT=6380

   # Socket.IO — kosongkan di produksi bila di-proxy same-origin; dev lokal contoh:
   # SOCKET_URL=http://127.0.0.1:8085
   SOCKET_URL=

   # REST Basic Auth — jika kosong, fallback di rest.php: admin / antrian2024
   USER_API=
   PASS_API=
   ```

   Lihat juga [`.env.example`](.env.example) untuk contoh lain (port dev alternatif, dll.).

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

5. **Akses aplikasi** (ganti port jika `PHP_PORT` / `NODEJS_PORT` diubah)

   | Halaman                           | URL                                              |
   | --------------------------------- | ------------------------------------------------ |
   | Landing / ambil antrian           | <http://localhost:8080/>                         |
   | Daftar layar display (client)     | <http://localhost:8080/client>                   |
   | Display publik untuk client `{id}` | <http://localhost:8080/client/1> (contoh `id=1`) |
   | Panel admin (redirect ke dashboard) | <http://localhost:8080/admin>                  |
   | Login admin (Ion Auth)            | <http://localhost:8080/auth/login>               |
   | Socket.IO gateway                 | `http://localhost:8085` (WebSocket + polling)    |

   **Login default:** `admin@admin.com` / `password` (ubah segera setelah login pertama).

## Cara Menjalankan — Tanpa Docker

1. Install dependency PHP: `composer install`
2. Import `database/schema.sql` ke MySQL.
3. Sesuaikan koneksi DB di `application/config/database.php` dan Redis di `application/config/redis.php`.
4. Arahkan document root web server ke folder `public/`.

   ```bash
   # jika mode development/localhost
   bin/server.sh
   ```

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

- `layanan` — kategori antrian + `kode_huruf` (A/B/C/…)
- `loket` — meja petugas, `status_buka`, terkait ke `id_layanan`
- `loket_user` — banyak-ke-banyak: user (petugas Ion Auth) yang di-assign ke loket
- `antrian` — transaksi harian (`tanggal`, `nomor_antrian`, `nomor_urut`, `status`, `id_loket`, `nik`, `keterangan`, waktu-waktu)
- `client` — profil layar TV / display
- `client_loket` — loket mana saja yang ditampilkan pada suatu `client`
- `client_display_settings` — preferensi tampilan per client (warna, video, footer, font)
- `users`, `groups`, `users_groups`, `login_attempts` — **Ion Auth**
- `admin_preferences` — preferensi AdminLTE

Detail & seeder: [database/schema.sql](database/schema.sql).

## Channel Redis

| Channel    | Dipakai untuk                                                                                                                                 |
| ---------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| `realtime` | **Event tiket baru (halaman Welcome):** `antrian-baru-{nomor_antrian}`. **Panggilan ke TV / display:** `loketXX-{nomor}` atau `loketXX-{nomor}\|{keterangan}` dari admin atau **`/api/panggilan/*`**. |
| `loop`     | Pesan tambahan untuk ticker/carousel pada display publik (opsional).                                                                        |

View display saat ini (`public/assets/frameworks/domprojects/js/tts.js`) **utamanya memproses** payload **`loketXX-…`** untuk memperbarui nomor yang dipanggil; string lain di channel yang sama bisa diabaikan oleh klien tersebut.

## REST API

Modul **layanan**, **loket**, **antrian**, **panggilan**, **dashboard**, **users**, dan **groups** diekspos sebagai REST JSON di bawah prefix **`/api/*`** ([chriskacerguis/codeigniter-restserver](https://github.com/chriskacerguis/codeigniter-restserver)).

- Controller: [application/controllers/api/](application/controllers/api/)
- Konfigurasi: [application/config/rest.php](application/config/rest.php)
- Ringkasan permukaan API: [docs/api.json](docs/api.json)

### Autentikasi

Seluruh endpoint dilindungi **HTTP Basic Auth**. Username/password dibaca dari environment **`USER_API`** / **`PASS_API`**; jika kosong dipakai fallback di **`rest_valid_logins`** (default aplikasi **`admin`** / **`antrian2024`**):

```php
$config['rest_valid_logins'] = [
    (getenv('USER_API') ?: 'admin') => (getenv('PASS_API') ?: 'antrian2024'),
];
```

> **Produksi:** isi **`USER_API` / `PASS_API`** di `.env` atau ubah fallback di file di atas. Untuk HTTPS wajib, set `$config['force_https'] = true` di [application/config/rest.php](application/config/rest.php).

Request tanpa header `Authorization` mendapat `401 Unauthorized` dan header `WWW-Authenticate: Basic realm="Realtime Antrian REST API"`.

### Daftar Endpoint

Base URL (Docker default): `http://localhost:8080/api`

#### Layanan — `/api/layanan`

| Method   | URI                 | Keterangan                                                        |
| -------- | ------------------- | ----------------------------------------------------------------- |
| `GET`    | `/api/layanan`      | List semua layanan                                                |
| `GET`    | `/api/layanan/{id}` | Detail satu layanan                                               |
| `POST`   | `/api/layanan`      | Tambah layanan. Body: `kode_huruf`, `nama_layanan`, `keterangan?` |
| `PUT`    | `/api/layanan/{id}` | Update partial (`kode_huruf` / `nama_layanan` / `keterangan`)     |
| `DELETE` | `/api/layanan/{id}` | Hapus layanan                                                     |

#### Loket — `/api/loket`

| Method   | URI                      | Keterangan                                                                                                                                 |
| -------- | ------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------- |
| `GET`    | `/api/loket`             | List semua loket (response detail biasanya menyertakan user ter-assign)                                                                     |
| `GET`    | `/api/loket/{id}`        | Detail satu loket                                                                                                                          |
| `GET`    | `/api/loket/buka`        | Loket buka. Query **`?with_last=1`** + opsional **`&tanggal=YYYY-MM-DD`** untuk menyertakan nomor antrian terakhir per loket pada tanggal itu |
| `GET`    | `/api/loket/users/{id}`  | Daftar user yang ter-assign ke loket `{id}`                                                                                                |
| `POST`   | `/api/loket`             | Tambah loket. Body: `id_layanan`, `nama_loket`, `status_buka?` (`buka`\|`tutup`), `id_users?` (array id user)                              |
| `PUT`    | `/api/loket/status/{id}` | Update status buka/tutup. Body: `status_buka`                                                                                                |
| `PUT`    | `/api/loket/users/{id}`  | Sinkron assign user (replace-all). Body: `id_users` (array; boleh kosong untuk mengosongkan assign)                                         |
| `DELETE` | `/api/loket/{id}`        | Hapus loket                                                                                                                                |

#### Antrian — `/api/antrian`

Operasi di bawah ini mengubah data di MySQL. Hanya **`POST /api/panggilan/*`** yang sekaligus **mem-publish ke Redis** agar display Socket.IO ikut berubah (sama seperti panel admin).

| Method   | URI                            | Keterangan                                                                                                                                                                                                                                                             |
| -------- | ------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `GET`    | `/api/antrian`                 | Daftar antrian + rekap per status. Query: `?tanggal=YYYY-MM-DD` (default hari ini)                                                                                                                                                                                     |
| `POST`   | `/api/antrian`                 | Buat nomor baru. Body: **`id_layanan`** (wajib), **`nik?`** (jika diisi harus 16 digit), **`keterangan?`**, **`nomor_antrian?`** (override manual). **Tidak** sama dengan halaman Welcome: **tidak** mem-publish **`antrian-baru-`** ke Redis                                                                  |
| `POST`   | `/api/antrian/call`            | Panggil berikutnya untuk loket. Body: `id_loket`. **Tidak** broadcast Redis (untuk integrasi “diam” / hanya DB)                                                                                                                                                        |
| `POST`   | `/api/antrian/panggilansimpan` | Simpan panggilan manual / panggil ulang. Body: `id_antrian`, `id_loket`. Validasi layanan; tiket `selesai`/`batal` ditolak. Response berisi `is_ulang` bila sudah pernah `dipanggil`                                                                                  |
| `PUT`    | `/api/antrian/selesai/{id}`    | Tandai selesai                                                                                                                                                                                                                                                          |
| `PUT`    | `/api/antrian/batal/{id}`      | Tandai batal                                                                                                                                                                                                                                                           |
| `DELETE` | `/api/antrian/{id}`            | Hapus record                                                                                                                                                                                                                                                           |

#### Panggilan — `/api/panggilan`

Setara perilaku **broadcast** dengan **admin/panggilan**: setelah logika panggilan, PHP **`PUBLISH`** ke Redis (`loketXX-…`).

| Method | URI                      | Keterangan                                                                                          |
| ------ | ------------------------ | --------------------------------------------------------------------------------------------------- |
| `GET`  | `/api/panggilan/loket`   | Loket yang sedang buka                                                                              |
| `POST` | `/api/panggilan/call`    | Panggil antrian berikutnya + broadcast. Body: `id_loket`                                            |
| `POST` | `/api/panggilan/recall`  | Panggil ulang (hanya broadcast). Body: `id_loket`, `nomor` (nomor antrian, mis. `A12`)            |
| `POST` | `/api/panggilan/simpan`  | Panggil tiket tertentu / panggil ulang + broadcast. Body: `id_antrian`, `id_loket`                  |

#### Dashboard — `/api/dashboard`

Statistik ringkas untuk monitoring (juga memakai model dashboard admin).

| Method | URI                            | Keterangan                                                                                |
| ------ | ------------------------------ | ----------------------------------------------------------------------------------------- |
| `GET`  | `/api/dashboard`               | Gabungan: count users/groups/loket, disk/memory, rekap antrian & loket. Query: `?tanggal=` |
| `GET`  | `/api/dashboard/summary`       | Count users, groups, loket                                                                |
| `GET`  | `/api/dashboard/system`        | Pemakaian disk & memory                                                                   |
| `GET`  | `/api/dashboard/antrian_status` | Rekap antrian per status. Query: `?tanggal=`                                            |
| `GET`  | `/api/dashboard/antrian_loket`  | Rekap antrian per loket. Query: `?tanggal=`                                           |
| `GET`  | `/api/dashboard/loket_status`   | Jumlah loket buka vs tutup                                                              |

#### Users — `/api/users`

Wrapper REST atas **Ion Auth**. Semua response user sudah memfilter field sensitif (`password`, `salt`, `remember_code`, `forgotten_password_code`, `activation_code`).

| Method   | URI                          | Keterangan                                                                                                                                                                                                                                                           |
| -------- | ---------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `GET`    | `/api/users`                 | List semua user beserta daftar `groups` masing-masing                                                                                                                                                                                                                |
| `GET`    | `/api/users/{id}`            | Detail satu user + `groups`                                                                                                                                                                                                                                          |
| `POST`   | `/api/users`                 | Tambah user baru. Body: `email` (required), `password` (required, min sesuai `ion_auth.min_password_length`), `first_name?`, `last_name?`, `phone?`, `company?`, `username?` (default: gabungan first+last, fallback local-part email), `groups[]?` (array id group) |
| `PUT`    | `/api/users/{id}`            | Update partial. Body: `first_name?`, `last_name?`, `phone?`, `company?`, `password?`, `groups[]?` (jika dikirim, **replace** seluruh keanggotaan group user)                                                                                                         |
| `PUT`    | `/api/users/activate/{id}`   | Aktifkan user (`active=1`)                                                                                                                                                                                                                                           |
| `PUT`    | `/api/users/deactivate/{id}` | Nonaktifkan user (`active=0`)                                                                                                                                                                                                                                        |
| `DELETE` | `/api/users/{id}`            | Hapus user                                                                                                                                                                                                                                                           |

#### Groups — `/api/groups`

CRUD role/group via **Ion Auth**, plus kolom `bgcolor` untuk label AdminLTE. Group `admin` (sesuai `ion_auth.admin_group`) dilindungi — tidak dapat di-rename maupun dihapus.

| Method   | URI                      | Keterangan                                                                                                        |
| -------- | ------------------------ | ----------------------------------------------------------------------------------------------------------------- |
| `GET`    | `/api/groups`            | List semua group                                                                                                  |
| `GET`    | `/api/groups/{id}`       | Detail satu group                                                                                                 |
| `GET`    | `/api/groups/users/{id}` | List user yang menjadi anggota group {id}                                                                         |
| `POST`   | `/api/groups`            | Tambah group. Body: `name` (required, regex `^[A-Za-z0-9_-]+$`), `description?`, `bgcolor?` (hex, mis. `#2196F3`) |
| `PUT`    | `/api/groups/{id}`       | Update partial. Body: `name?`, `description?`, `bgcolor?`. Rename group `admin` ditolak (`403 Forbidden`)         |
| `DELETE` | `/api/groups/{id}`       | Hapus group. Menghapus group `admin` ditolak (`403 Forbidden`)                                                    |

### Format Response

Semua response menggunakan `Content-Type: application/json` dengan struktur umum:

```json
{
  "status": true,
  "message": "Layanan berhasil ditambahkan",
  "data": {
    "id": 4,
    "kode_huruf": "D",
    "nama_layanan": "Rekam Medis",
    "keterangan": null
  }
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

**cURL — panggil antrian berikutnya (hanya DB, tanpa update display Socket.IO)**

```bash
curl -u admin:antrian2024 \
     -X POST \
     -d "id_loket=1" \
     http://localhost:8080/api/antrian/call
```

**cURL — panggil berikutnya + broadcast ke display (disarankan untuk TV)**

```bash
curl -u admin:antrian2024 \
     -X POST \
     -d "id_loket=1" \
     http://localhost:8080/api/panggilan/call
```

**cURL — simpan panggilan manual / panggil ulang (DB saja, tanpa broadcast)**

```bash
curl -u admin:antrian2024 \
     -X POST \
     -d "id_antrian=12&id_loket=1" \
     http://localhost:8080/api/antrian/panggilansimpan
```

**cURL — simpan panggilan + broadcast (setara perilaku admin untuk TV)**

```bash
curl -u admin:antrian2024 \
     -X POST \
     -d "id_antrian=12&id_loket=1" \
     http://localhost:8080/api/panggilan/simpan
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

- **Display tidak berubah setelah hit API** — pastikan pemanggilan memakai **`/api/panggilan/*`** atau panel admin, bukan hanya **`/api/antrian/call`** (yang sengaja **tidak** mem-publish Redis). Pastikan loket ada di **`client_loket`** untuk **`/client/{id}`** yang dibuka browser.
- **Display tidak update** — cek container `antrian_nodejs` berjalan dan **`NODEJS_PORT`** (default `8085`) terbuka; **`SOCKET_URL`** di `.env`/view mengarah ke gateway bila akses frontend beda origin. Redis **dengan password**: pastikan PHP **dan** proses Node memakai kredensial yang sama (`REDIS_PASSWORD`); di `docker-compose.yml` layanan **`nodejs`** perlu juga menerima variabel tersebut jika Redis dijaga password.
- **`redis` connection refused** — pastikan service `redis` up (`docker-compose ps`) dan `REDIS_HOST`/`REDIS_PORT` konsisten antara PHP dan Node.js.
- **Nomor antrian tidak reset** — reset dilakukan per tanggal (`tanggal = CURDATE()` di tabel `antrian`). Pastikan timezone server sesuai.
- **`server.js` tidak ketemu** di container Node.js — rebuild tanpa cache: `docker-compose up --build -d`.
- **REST API selalu 401** — pastikan header `Authorization: Basic ...` terkirim (Apache `mod_php` biasanya aman, beberapa setup FastCGI perlu menambahkan `SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1` di `.htaccess`). Cek juga password di [application/config/rest.php](application/config/rest.php) sesuai dengan yang dikirim.
- **REST API 404 padahal URL benar** — pastikan `mod_rewrite` aktif dan `public/.htaccess` terbaca; tanpa itu URL harus berbentuk `http://host/index.php/api/layanan`.

## aaPanel Users (Cloud)

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

   Kalau mau lebih ketat (recommended): `sudo chmod 640 .env    # owner rw, group r, other none`

3. Cek parent directory executable

   ```bash
      /www/wwwroot/antrian/ harus x untuk user www:
      ls -ld /www/wwwroot/antrian
      sudo chmod 755 /www/wwwroot/antrian
   ```

4. Cek open_basedir aaPanel

   Di aaPanel → Website → Settings → Config File, cari open_basedir. Pastikan /www/wwwroot/antrian/ ada di daftarnya (seharusnya default sudah termasuk).

5. Verifikasi

   `sudo -u www cat /www/wwwroot/antrian/.env`
   Kalau bisa tampil isinya → permission beres. Reload PHP-FPM:

   `sudo /etc/init.d/php-fpm-* reload    # sesuai versi PHP`
   Setelah itu refresh halaman

## Credits & Referensi

- [CodeIgniter 3](https://github.com/bcit-ci/CodeIgniter) untuk Otak dari Aplikasi
- [Ion Auth](https://github.com/benedmunds/CodeIgniter-Ion-Auth) untuk Authuntifikasi user
- [chriskacerguis/codeigniter-restserver](https://github.com/chriskacerguis/codeigniter-restserver) untuk REST API
- [AdminLTE](https://adminlte.io/) untuk template panel admin
- [Socket.IO](https://socket.io/) & [node-redis](https://github.com/redis/node-redis) untuk realtime gateway
- Pola dasar realtime gateway terinspirasi dari [vanuganti/realtime](http://github.com/vanuganti/realtime)

## Acknowledgements & Disclaimer

- [Forked From Realtime Antrian Bank](https://github.com/siagung/CI_Redis_Realtime_Antrian_Bank)
- [Clone and Modification From CI AdminLTE](https://github.com/domProjects/CI-AdminLTE)
- [Use Composer Style to Use Codeigniter](https://github.com/kenjis/codeigniter-composer-installer.git)
- [Codeigniter 3 Full PHP 8 Supports](https://github.com/pocketarc/codeigniter.git)
- [EDGE TTS For Audio Indonesian](https://github.com/rsuppersahabatan/Edge-TTS-API)
- [Use AI ~ AntiGravity and Cloude For Task List](https://topidesta.my.id/era-ai-untuk-programmer-30an/)

## Lisensi

Project ini dirilis di bawah lisensi MIT.
