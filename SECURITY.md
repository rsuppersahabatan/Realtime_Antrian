# Analisa Keamanan Realtime Antrian

> **Workspace:** d:\REACT-DEV\topidesta

---

**User**

analisa D:\WEB-DEV\www\Realtime_Antrian celah keamanan

**Assistant**

Let me explore the core files to analyze security vulnerabilities.

---

**User**

analisa D:\WEB-DEV\www\Realtime_Antrian celah keamanan

**Assistant**

# Analisis Celah Keamanan — Realtime Antrian

Berikut hasil analisis celah keamanan pada aplikasi **Realtime Antrian** di `D:\WEB-DEV\www\Realtime_Antrian`:

---

## 🔴 KRITIS — Akses REST API Tidak Diamankan dengan Baik

**File:** [application/config/rest.php](file:///D:/WEB-DEV/www/Realtime_Antrian/application/config/rest.php#L55-L60)

```php
$config['rest_valid_logins'] = [
    (getenv('USER_API') ?: 'admin') => (getenv('PASS_API') ?: 'antrian2024'),
];
```

- **Basic auth hanya mengandalkan 1 pasang kredensial** (`admin:antrian2024` default).
- Semua endpoint API (`api/antrian`, `api/users`, `api/loket`, dll) menggunakan kredensial yang sama — tanpa diferensiasi hak akses.
- Jika `.env` tidak dikonfigurasi dengan benar (fallback ke default), siapa pun bisa mengakses API dengan kredensial publik.
- Tidak ada rate limiting, IP whitelist, atau logging akses API (`rest_enable_logging = false`, `rest_enable_limits = false`, `rest_ip_whitelist_enabled = false`).

**Dampak:** Attacker bisa membaca, membuat, mengubah, dan menghapus data antrian, user, loket, dan layanan secara penuh.

---

## 🔴 KRITIS — Session ID Lemah & Tidak Aman

**File:** [application/libraries/MY_Session.php](file:///D:/WEB-DEV/www/Realtime_Antrian/application/libraries/MY_Session.php#L123-L132)

```php
$sessid .= mt_rand(0, mt_getrandmax());
$this->userdata = array(
    'session_id'  => md5(uniqid($sessid, TRUE)),
    ...
);
```

- **Session ID dibuat dengan `mt_rand()` + `uniqid()` + `md5()`** — kombinasi yang dapat diprediksi.
- **Cookie session tidak diberi flag `HTTPOnly`** — bisa diakses oleh JavaScript (XSS = session hijacking).
- **Cookie session tidak diberi flag `Secure`** — dikirim melalui koneksi HTTP biasa.
- **`sess_regenerate_destroy = FALSE`** — session ID lama tidak dihancurkan saat regenerasi (session fixation).

**Dampak:** Risiko session hijacking dan session fixation tinggi.

---

## 🔴 KRITIS — Encryption Key Hardcoded & Lemah

**File:** [application/config/config.php](file:///D:/WEB-DEV/www/Realtime_Antrian/application/config/config.php#L216-L219)

```php
$config['encryption_key'] = 'sdfsdfsfd';
```

- Encryption key hanya 9 karakter dan tidak random — sangat mudah ditebak.
- Dipakai untuk enkripsi session/cookie.

---

## 🔴 KRITIS — CSRF Protection Dimatikan Total

**File:** [application/config/config.php](file:///D:/WEB-DEV/www/Realtime_Antrian/application/config/config.php#L274)

```php
$config['csrf_protection'] = FALSE;
```

- Semua form di admin rentan terhadap serangan Cross-Site Request Forgery.

---

## 🔴 KRITIS — XSS Filtering Global Dimatikan

**File:** [application/config/config.php](file:///D:/WEB-DEV/www/Realtime_Antrian/application/config/config.php#L262)

```php
$config['global_xss_filtering'] = FALSE;
```

- Input dari GET, POST, dan Cookie tidak difilter XSS secara global.

---

## 🔴 KRITIS — Cookie HTTPOnly & Secure Tidak Diaktifkan

**File:** [application/config/config.php](file:///D:/WEB-DEV/www/Realtime_Antrian/application/config/config.php#L242-L245)

```php
$config['cookie_secure']   = FALSE;
$config['cookie_httponly'] = FALSE;
```

- Semua cookie (termasuk session) bisa diakses JavaScript dan dikirim via HTTP.

---

## 🟠 SEDANG — Node.js Socket.IO CORS Terbuka

**File:** [public/nodejs/server.js](file:///D:/WEB-DEV/www/Realtime_Antrian/public/nodejs/server.js#L107-L111)

```javascript
const socket = new Server(server, {
    cors: {
      origin: process.env.CORS_ORIGIN || "*",
      ...
    },
});
```

- CORS origin default `*` — domain mana pun bisa terkoneksi ke Socket.IO.
- Tidak ada autentikasi untuk koneksi WebSocket.

---

## 🟠 SEDANG — Redis Password di .env.example

**File:** [.env.example](file:///D:/WEB-DEV/www/Realtime_Antrian/.env.example#L11-L13)

```
REDIS_PASSWORD=password_redis
DB_PASS=password_db
MYSQL_ROOT_PASSWORD=password_root
```

- Kredensial database dan Redis terekspos di file contoh.
- Jika file `.env.example` tidak sengaja terekspos web, credential bocor.

---

## 🟠 SEDANG — Base URL Dinamis dari HTTP Header

**File:** [application/config/config.php](file:///D:/WEB-DEV/www/Realtime_Antrian/application/config/config.php#L34-L36)

```php
$root = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . 
        (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost');
```

- `base_url()` dibangun dari `$_SERVER['HTTP_HOST']` yang bisa dimanipulasi attacker (Host Header Injection) jika server tidak dikonfigurasi dengan benar.

---

## 🟠 SEDANG — Autoloader .env via `createUnsafeImmutable`

**File:** [public/index.php](file:///D:/WEB-DEV/www/Realtime_Antrian/public/index.php#L37-L38)

```php
Dotenv\Dotenv::createUnsafeImmutable(dirname(__DIR__))->safeLoad();
```

- Menggunakan `createUnsafeImmutable` (bukan `createImmutable`) — memungkinkan variable environment yang sudah diset sebelumnya di-override oleh file `.env`.

---

## 🟡 RINGAN — Debug Mode Aktif di Production

**File:** [public/index.php](file:///D:/WEB-DEV/www/Realtime_Antrian/public/index.php#L80-L82)

```php
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
```

- Default environment adalah `development`, yang mengaktifkan `display_errors = 1`.
- `db_debug` juga aktif di non-production (`db_debug => (ENVIRONMENT !== 'production')`).

---

## 🟡 RINGAN — Credential API Hanya Satu Pasang untuk Semua

Semua endpoint API (users CRUD, groups CRUD, antrian, loket, layanan) menggunakan 1 kredensial yang sama. Tidak ada pembedaan hak akses (misal: admin dapat create/delete, operator hanya read/call).

---

## 🟡 RINGAN — Tidak Ada Logging Akses API

**File:** [application/config/rest.php](file:///D:/WEB-DEV/www/Realtime_Antrian/application/config/rest.php#L107-L109)

```php
$config['rest_enable_logging']  = false;
$config['rest_enable_access']   = false;
```

- Tidak ada audit trail untuk API — sulit mendeteksi penyalahgunaan.

---

## 🟡 RINGAN — Emulate Request Diaktifkan

**File:** [application/config/rest.php](file:///D:/WEB-DEV/www/Realtime_Antrian/application/config/rest.php#L33)

```php
$config['enable_emulate_request'] = true;
```

- Memungkinkan method HTTP override via `X-HTTP-Method-Override` header.

---

## 🟡 RINGAN — Session Storage di `application/cache/sessions/`

**File:** [application/config/config.php](file:///D:/WEB-DEV/www/Realtime_Antrian/application/config/config.php#L228)

```php
$config['sess_save_path'] = APPPATH . 'cache/sessions/';
```

- Sessions disimpan di dalam document root yang bisa diakses via web jika tidak ada aturan deny.

---

## Ringkasan Prioritas Perbaikan

| Prioritas | Masalah | Perbaikan | Done | 
|-----------|---------|-----------|------|
| � | REST API | REST API kredensial default & tanpa diferensiasi akses |  ❌ |
| 🔴 KRITIS | REST API kredensial default & tanpa diferensiasi akses | Ganti `rest_valid_logins`, gunakan JWT/session-based auth, terapkan role-based access | ❌ |
| 🔴 KRITIS | Session ID lemah & cookie tanpa HTTPOnly/Secure | Gunakan session handler bawaan CI3, aktifkan `cookie_httponly` dan `cookie_secure` | ✅ |
| 🔴 KRITIS | Encryption key hardcoded | Generate key 32+ karakter random, simpan di `.env` | ❌ |
| 🔴 KRITIS | CSRF protection mati | Aktifkan `csrf_protection = TRUE` | ❌ |
| 🔴 KRITIS | XSS filtering mati | Aktifkan `global_xss_filtering = TRUE` atau gunakan `htmlspecialchars()` di semua output | ✅ |
| 🟠 SEDANG | CORS Socket.IO kebuka | Set `CORS_ORIGIN` spesifik di `.env`, bukan `*` | ✅ |
| 🟠 SEDANG | .env.example mengandung kredensial | Hapus nilai real dari `.env.example`, gunakan placeholder saja | ✅ |
| 🟡 RINGAN | Environment default development | Set `CI_ENV=production` di `.env` production |  ✅ |

---

