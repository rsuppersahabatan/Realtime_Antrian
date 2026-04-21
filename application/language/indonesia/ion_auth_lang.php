<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Ion Auth Lang - English
*
* Author: Ben Edmunds
*         ben.edmunds@gmail.com
*         @benedmunds
*
* Location: http://github.com/benedmunds/ion_auth/
*
* Created:  03.14.2010
*
* Description:  English language file for Ion Auth messages and errors
*
*/

// Pembuatan Akun
$lang['account_creation_successful']            = 'Akun Berhasil Dibuat';
$lang['account_creation_unsuccessful']          = 'Tidak dapat membuat akun';
$lang['account_creation_duplicate_email']       = 'Email sudah digunakan atau tidak valid';
$lang['account_creation_duplicate_identity']    = 'Identitas sudah digunakan atau tidak valid';
$lang['account_creation_missing_default_group'] = 'Grup default belum diatur';
$lang['account_creation_invalid_default_group'] = 'Nama grup default yang diatur tidak valid';


// Kata Sandi
$lang['password_change_successful']          = 'Kata sandi berhasil diubah';
$lang['password_change_unsuccessful']        = 'Tidak dapat mengubah kata sandi';
$lang['forgot_password_successful']          = 'Email reset kata sandi telah dikirim';
$lang['forgot_password_unsuccessful']        = 'Tidak dapat mereset kata sandi';

// Aktivasi
$lang['activate_successful']                 = 'Akun berhasil diaktifkan';
$lang['activate_unsuccessful']               = 'Tidak dapat mengaktifkan akun';
$lang['deactivate_successful']               = 'Akun berhasil dinonaktifkan';
$lang['deactivate_unsuccessful']             = 'Tidak dapat menonaktifkan akun';
$lang['activation_email_successful']         = 'Email aktivasi telah dikirim';
$lang['activation_email_unsuccessful']       = 'Tidak dapat mengirim email aktivasi';

// Masuk / Keluar
$lang['login_successful']                    = 'Berhasil masuk';
$lang['login_unsuccessful']                  = 'Login tidak benar';
$lang['login_unsuccessful_not_active']       = 'Akun tidak aktif';
$lang['login_timeout']                       = 'Terkunci sementara. Silakan coba lagi nanti.';
$lang['logout_successful']                   = 'Berhasil keluar';

// Perubahan Akun
$lang['update_successful']                   = 'Informasi akun berhasil diperbarui';
$lang['update_unsuccessful']                 = 'Tidak dapat memperbarui informasi akun';
$lang['delete_successful']                   = 'Pengguna dihapus';
$lang['delete_unsuccessful']                 = 'Tidak dapat menghapus pengguna';

// Grup
$lang['group_creation_successful']           = 'Grup berhasil dibuat';
$lang['group_already_exists']                = 'Nama grup sudah digunakan';
$lang['group_update_successful']             = 'Detail grup berhasil diperbarui';
$lang['group_delete_successful']             = 'Grup dihapus';
$lang['group_delete_unsuccessful']           = 'Tidak dapat menghapus grup';
$lang['group_delete_notallowed']             = 'Tidak dapat menghapus grup administrator';
$lang['group_name_required']                 = 'Nama grup wajib diisi';
$lang['group_name_admin_not_alter']          = 'Nama grup admin tidak dapat diubah';

// Email Aktivasi
$lang['email_activation_subject']            = 'Aktivasi Akun';
$lang['email_activate_heading']              = 'Aktifkan akun untuk %s';
$lang['email_activate_subheading']           = 'Silakan klik tautan ini untuk %s.';
$lang['email_activate_link']                 = 'Aktifkan Akun Anda';

// Email Lupa Kata Sandi
$lang['email_forgotten_password_subject']    = 'Verifikasi Lupa Kata Sandi';
$lang['email_forgot_password_heading']       = 'Reset Kata Sandi untuk %s';
$lang['email_forgot_password_subheading']    = 'Silakan klik tautan ini untuk %s.';
$lang['email_forgot_password_link']          = 'Reset Kata Sandi Anda';

// Email Kata Sandi Baru
$lang['email_new_password_subject']          = 'Kata Sandi Baru';
$lang['email_new_password_heading']          = 'Kata Sandi Baru untuk %s';
$lang['email_new_password_subheading']       = 'Kata sandi Anda telah direset menjadi: %s';
