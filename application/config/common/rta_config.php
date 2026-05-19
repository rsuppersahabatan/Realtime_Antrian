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
