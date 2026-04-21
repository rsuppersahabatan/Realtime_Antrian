<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load .env once (idempotent — aman kalau redis.php juga sudah memuat).
// File ini berada di application/config/common/, jadi .env di 4 level di atas.
$__env_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/.env';
if (file_exists($__env_path)) {
    foreach (file($__env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $__line) {
        if (strpos(trim($__line), '#') === 0) continue;
        $__parts = explode('=', $__line, 2);
        if (count($__parts) === 2 && getenv(trim($__parts[0])) === FALSE) {
            putenv(trim($__parts[0]) . '=' . trim($__parts[1]));
        }
    }
}
unset($__env_path, $__line, $__parts);

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
