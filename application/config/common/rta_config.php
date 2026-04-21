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
