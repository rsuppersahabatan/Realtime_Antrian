<?php
/**
 * Language file for Panggilan module
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/* Menu */
$lang['menu_panggilan']           = 'Calling';

/* Page title / Section */
$lang['panggilan_title']          = 'Queue Calling';
$lang['panggilan_subtitle']       = 'Call the next queue per counter';

/* Fields / Labels */
$lang['panggilan_loket']          = 'Counter';
$lang['panggilan_layanan']        = 'Service';
$lang['panggilan_terakhir']       = 'Last Called';
$lang['panggilan_socket_status']  = 'Connection Status';

/* Buttons */
$lang['panggilan_btn_panggil']    = 'Call';
$lang['panggilan_btn_ulangi']     = 'Repeat';

/* Socket status */
$lang['panggilan_connected']      = 'Connected';
$lang['panggilan_disconnected']   = 'Disconnected';
$lang['panggilan_reconnecting']   = 'Reconnecting';

/* Messages */
$lang['panggilan_no_loket']       = 'No counter is open yet. Please open a counter first in the %s menu.';
$lang['panggilan_success']        = 'Queue <strong>%s</strong> called to %s.';
$lang['panggilan_antrian_habis']  = 'There are no waiting queues for this service.';
$lang['panggilan_error']          = 'Failed to call queue.';
$lang['panggilan_loket_invalid']  = 'Counter is invalid or currently closed.';
