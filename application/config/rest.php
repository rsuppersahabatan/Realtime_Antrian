<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| HTTP protocol
|--------------------------------------------------------------------------
|
| Set to force the use of HTTPS for REST API calls
|
*/
$config['force_https'] = false;

/*
|--------------------------------------------------------------------------
| REST Output Format
|--------------------------------------------------------------------------
*/
$config['rest_default_format'] = 'json';

/*
|--------------------------------------------------------------------------
| REST Supported Output Formats
|--------------------------------------------------------------------------
*/
$config['rest_supported_formats'] = [
    'json',
    'array',
    'csv',
    'html',
    'jsonp',
    'php',
    'serialized',
    'xml',
];

$config['rest_status_field_name']  = 'status';
$config['rest_message_field_name'] = 'error';

$config['enable_emulate_request'] = true;

/*
|--------------------------------------------------------------------------
| REST Realm
|--------------------------------------------------------------------------
| Name of the password protected REST API displayed on login dialogs
*/
$config['rest_realm'] = 'Realtime Antrian REST API';

/*
|--------------------------------------------------------------------------
| REST Login
|--------------------------------------------------------------------------
| FALSE      No login required
| 'basic'    Basic HTTP authentication
| 'digest'   Digest HTTP authentication (more secure)
| 'session'  PHP session based auth
*/
$config['rest_auth'] = 'basic';

/*
|--------------------------------------------------------------------------
| REST Login Source
|--------------------------------------------------------------------------
| ''         Pakai $config['rest_valid_logins'] (config-based)
| 'ldap'     LDAP
| 'library'  Auth library custom
*/
$config['auth_source'] = '';

/*
|--------------------------------------------------------------------------
| Allow Authentication and API Keys
|--------------------------------------------------------------------------
| strict_api_and_auth = false  -> cukup salah satu (di sini: basic auth saja)
*/
$config['allow_auth_and_keys'] = false;
$config['strict_api_and_auth'] = false;

$config['auth_library_class']    = '';
$config['auth_library_function'] = '';

/*
|--------------------------------------------------------------------------
| REST Login Usernames
|--------------------------------------------------------------------------
| Daftar username => password untuk basic auth.
| PENTING: ganti kredensial di bawah ini sebelum dipakai di production!
*/
$config['rest_valid_logins'] = [
    'admin' => 'antrian2024',
];

/*
|--------------------------------------------------------------------------
| Global IP White-listing / Blacklisting (dimatikan)
|--------------------------------------------------------------------------
*/
$config['rest_ip_whitelist_enabled'] = false;
$config['rest_ip_whitelist']         = '';
$config['rest_ip_blacklist_enabled'] = false;
$config['rest_ip_blacklist']         = '';

$config['rest_handle_exceptions'] = true;

/*
|--------------------------------------------------------------------------
| API Keys / Logging / Access / Limits — semuanya dimatikan
|--------------------------------------------------------------------------
*/
$config['rest_database_group']  = 'default';
$config['rest_keys_table']      = 'keys';
$config['rest_enable_keys']     = false;
$config['rest_key_column']      = 'key';
$config['rest_limits_method']   = 'ROUTED_URL';
$config['rest_key_length']      = 40;
$config['rest_key_name']        = 'X-API-KEY';
$config['rest_enable_logging']  = false;
$config['rest_logs_table']      = 'logs';
$config['rest_enable_access']   = false;
$config['rest_access_table']    = 'access';
$config['rest_logs_json_params']= false;
$config['rest_enable_limits']   = false;
$config['rest_limits_table']    = 'limits';

$config['rest_ignore_http_accept'] = false;
$config['rest_ajax_only']          = false;
$config['rest_language']           = 'english';

/*
|--------------------------------------------------------------------------
| CORS
|--------------------------------------------------------------------------
*/
$config['check_cors'] = false;
$config['allowed_cors_headers'] = [
    'Origin',
    'X-Requested-With',
    'Content-Type',
    'Accept',
    'Access-Control-Request-Method',
    'Authorization',
];
$config['allowed_cors_methods'] = [
    'GET',
    'POST',
    'OPTIONS',
    'PUT',
    'PATCH',
    'DELETE',
];
$config['allow_any_cors_domain']  = false;
$config['allowed_cors_origins']   = [];
$config['forced_cors_headers']    = [];
