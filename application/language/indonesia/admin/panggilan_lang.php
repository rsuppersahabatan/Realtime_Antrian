<?php
/**
 * Language file for Panggilan module
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/* Menu */
$lang['menu_panggilan']           = 'Panggilan';

/* Page title / Section */
$lang['panggilan_title']          = 'Panggilan Antrian';
$lang['panggilan_subtitle']       = 'Panggil antrian berikutnya per loket';

/* Fields / Labels */
$lang['panggilan_loket']          = 'Loket';
$lang['panggilan_layanan']        = 'Layanan';
$lang['panggilan_terakhir']       = 'Panggilan Terakhir';
$lang['panggilan_socket_status']  = 'Status Koneksi';

/* Buttons */
$lang['panggilan_btn_panggil']    = 'Panggil';
$lang['panggilan_btn_ulangi']     = 'Ulangi';

/* Socket status */
$lang['panggilan_connected']      = 'Terhubung';
$lang['panggilan_disconnected']   = 'Terputus';
$lang['panggilan_reconnecting']   = 'Menghubungkan Ulang';

/* Messages */
$lang['panggilan_no_loket']       = 'Belum ada loket yang dibuka. Silakan buka loket terlebih dahulu pada menu %s.';
$lang['panggilan_success']        = 'Antrian <strong>%s</strong> dipanggil ke %s.';
$lang['panggilan_antrian_habis']  = 'Tidak ada antrian menunggu untuk layanan ini.';
$lang['panggilan_error']          = 'Gagal memanggil antrian.';
$lang['panggilan_loket_invalid']  = 'Loket tidak valid atau sedang tutup.';
