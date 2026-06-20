<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Assets
|--------------------------------------------------------------------------
| Paths are resolved with base_url(). On this project the webroot is the
| `public/` folder, so `assets/...` maps to `public/assets/...`.
*/
$config['assets_dir']     = 'assets';
$config['frameworks_dir'] = $config['assets_dir'] . '/frameworks';
$config['plugins_dir']    = $config['assets_dir'] . '/plugins';

/*
|--------------------------------------------------------------------------
| Upload
|--------------------------------------------------------------------------
*/
$config['upload_dir']     = 'upload';
$config['avatar_dir']     = $config['upload_dir'] . '/avatar';

/*
|--------------------------------------------------------------------------
| Socket.IO
|--------------------------------------------------------------------------
| URL absolut ke service Socket.IO (Node.js). Kosongkan agar view memakai
| same-origin (butuh reverse proxy /socket.io/ di Nginx/Apache produksi).
| Untuk dev Windows tanpa reverse proxy: set SOCKET_URL=http://127.0.0.1:8085
| di .env.
*/
$config['socket_url']     = getenv('SOCKET_URL') ?: '';

/*
|--------------------------------------------------------------------------
| TTS (Edge-TTS gateway)
|--------------------------------------------------------------------------
| Base URL service TTS yang dipakai display antrian untuk men-generate
| suara panggilan (nomor antrian + nama + loket). View menyetel
| `window.ttsApiBase` dari nilai ini SEBELUM tts.js di-load, sehingga
| override per-deployment cukup via env TTS_API_BASE tanpa mengedit JS.
| Jangan diberi trailing slash — kode JS menggabungkan dengan '/tts'.
*/
$config['tts_api_base']   = getenv('TTS_API_BASE') ?: 'http://localhost:8085';

/*
|--------------------------------------------------------------------------
| UTDRS Self-service API
|--------------------------------------------------------------------------
| Base URL endpoint UTDRS yang dipanggil halaman welcome lewat fetch():
|   - {base}/self-checkin   (tombol "Check in")
|   - {base}/self-register  (tombol "Daftar")
| Override per-deployment via env API_UTDRS_BASE. Jangan beri trailing slash —
| JS menggabungkan path '/self-checkin' & '/self-register' secara eksplisit.
*/
$config['api_utdrs_base'] = getenv('API_UTDRS_BASE') ?: 'http://localhost:8081/server/darah';

/*
|--------------------------------------------------------------------------
| UTDRS Platform credentials (server-side JWT)
|--------------------------------------------------------------------------
| Dipakai Welcome::self_checkin / self_register untuk login ke endpoint
| {host}/login/platform dan memperoleh JWT, lalu memanggil self-checkin /
| self-register dengan header Authorization: Bearer. Kredensial & token
| TIDAK pernah dikirim ke browser. Isi via env UTDRS_PLATFORM_NIP /
| UTDRS_PLATFORM_PASSWORD. Gunakan akun layanan khusus.
*/
$config['utdrs_platform_nip']      = getenv('UTDRS_PLATFORM_NIP') ?: '';
$config['utdrs_platform_password'] = getenv('UTDRS_PLATFORM_PASSWORD') ?: '';

/*
| Token JWT statis (Bearer) untuk endpoint /server/darah/*.
| CATATAN PENTING: endpoint /server memvalidasi NIP di DB group "default",
| sedangkan login/platform (grup "prima") berjalan di DB "prodDB" — token
| hasil login di prima TIDAK valid untuk /server. Maka cara paling andal:
| tempel token yang sudah terbukti bekerja (mis. dari Insomnia) ke
| UTDRS_BEARER_TOKEN. Token platform berumur ~5 tahun. Jika diisi, proxy
| memakai token ini langsung tanpa login.
*/
$config['utdrs_bearer_token']      = getenv('UTDRS_BEARER_TOKEN') ?: '';
