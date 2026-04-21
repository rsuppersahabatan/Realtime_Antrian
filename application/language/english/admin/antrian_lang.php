<?php
/**
 * Language file for Antrian module
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/* Menu */
$lang['menu_antrian']        = 'Antrian';
$lang['menu_antrian_create'] = 'Tambah Antrian';
$lang['menu_antrian_delete'] = 'Hapus Antrian';
$lang['menu_antrian_reset']  = 'Reset Antrian';

/* Fields */
$lang['antrian_no']          = 'No';
$lang['antrian_nomor']       = 'Nomor';
$lang['antrian_layanan']     = 'Layanan';
$lang['antrian_loket']       = 'Loket';
$lang['antrian_status']      = 'Status';
$lang['antrian_waktu_ambil'] = 'Waktu Ambil';
$lang['antrian_waktu_panggil'] = 'Waktu Panggil';
$lang['antrian_waktu_selesai'] = 'Waktu Selesai';
$lang['antrian_action']      = 'Aksi';
$lang['antrian_tanggal']     = 'Tanggal';
$lang['antrian_confirm']     = 'Konfirmasi';

/* Status */
$lang['antrian_status_menunggu']  = 'Menunggu';
$lang['antrian_status_dipanggil'] = 'Dipanggil';
$lang['antrian_status_selesai']   = 'Selesai';
$lang['antrian_status_batal']     = 'Batal';

/* Buttons / Labels */
$lang['antrian_create']      = 'Tambah Antrian Manual';
$lang['antrian_reset_hari_ini'] = 'Reset Antrian Hari Ini';
$lang['antrian_btn_selesai'] = 'Selesai';
$lang['antrian_btn_batal']   = 'Batal';

/* Messages */
$lang['antrian_created_success'] = 'Antrian <strong>%s</strong> berhasil dibuat.';
$lang['antrian_created_error']   = 'Gagal membuat antrian. Pastikan layanan valid.';
$lang['antrian_status_updated']  = 'Status antrian berhasil diperbarui.';
$lang['antrian_reset_success']   = 'Semua antrian yang masih menunggu/dipanggil hari ini telah dibatalkan.';
$lang['antrian_deleted_success'] = 'Data antrian berhasil dihapus.';
$lang['antrian_not_found']       = 'Data antrian tidak ditemukan.';
$lang['antrian_delete_confirm']  = 'Apakah Anda yakin ingin menghapus antrian <strong>%s</strong>?';
$lang['antrian_reset_confirm']   = 'Reset akan membatalkan <strong>%d</strong> antrian yang masih berstatus menunggu/dipanggil hari ini. Lanjutkan?';
$lang['antrian_kosong']          = 'Tidak ada data antrian untuk tanggal ini.';
