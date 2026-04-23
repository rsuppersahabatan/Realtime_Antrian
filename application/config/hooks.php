<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/userguide3/general/hooks.html
|
*/

/*
| -------------------------------------------------------------------------
| CI_Minifier - Hanya aktif di mode production
| -------------------------------------------------------------------------
| Minify HTML, CSS, dan JS output untuk mempercepat loading halaman.
| Di mode development/testing, output tidak di-minify agar mudah di-debug.
|
| Untuk mengaktifkan, set environment variable CI_ENV=production
| atau ubah default di public/index.php
*/
if (ENVIRONMENT === 'production') {
    $hook['display_override'][] = array(
        'class'    => '',
        'function' => '',
        'filename' => '',
        'filepath' => ''
    );
}
