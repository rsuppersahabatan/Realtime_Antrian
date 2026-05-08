<?php
/**
 * Language file for Antrian module
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/* Menu */
$lang['menu_antrian']        = 'Queue';
$lang['menu_antrian_create'] = 'Add Queue';
$lang['menu_antrian_delete'] = 'Delete Queue';
$lang['menu_antrian_reset']  = 'Reset Queue';

/* Fields */
$lang['antrian_no']          = 'No';
$lang['antrian_nomor']       = 'Number';
$lang['antrian_nama']        = 'Nama';
$lang['antrian_layanan']     = 'Service';
$lang['antrian_loket']       = 'Counter';
$lang['antrian_status']      = 'Status';
$lang['antrian_waktu_ambil'] = 'Taken At';
$lang['antrian_waktu_panggil'] = 'Called At';
$lang['antrian_waktu_selesai'] = 'Finished At';
$lang['antrian_action']      = 'Action';
$lang['antrian_tanggal']     = 'Date';
$lang['antrian_confirm']     = 'Confirmation';
$lang['antrian_nik']         = 'NIK';
$lang['antrian_keterangan']  = 'Description';

/* Status */
$lang['antrian_status_menunggu']  = 'Waiting';
$lang['antrian_status_dipanggil'] = 'Called';
$lang['antrian_status_selesai']   = 'Finished';
$lang['antrian_status_batal']     = 'Cancelled';

/* Buttons / Labels */
$lang['antrian_create']      = 'Add Manual Queue';
$lang['antrian_reset_hari_ini'] = 'Reset Today\'s Queue';
$lang['antrian_btn_selesai'] = 'Finish';
$lang['antrian_btn_batal']   = 'Cancel';

/* Messages */
$lang['antrian_created_success'] = 'Queue <strong>%s</strong> successfully created.';
$lang['antrian_created_error']   = 'Failed to create queue. Make sure the service is valid.';
$lang['antrian_status_updated']  = 'Queue status successfully updated.';
$lang['antrian_reset_success']   = 'All queues still waiting/called today have been cancelled.';
$lang['antrian_deleted_success'] = 'Queue data successfully deleted.';
$lang['antrian_not_found']       = 'Queue data not found.';
$lang['antrian_delete_confirm']  = 'Are you sure you want to delete queue <strong>%s</strong>?';
$lang['antrian_reset_confirm']   = 'Reset will cancel <strong>%d</strong> queues still waiting/called today. Continue?';
$lang['antrian_kosong']          = 'No queue data for this date.';
