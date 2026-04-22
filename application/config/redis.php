<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Config for the CodeIgniter Redis library
 *
 * @see ../libraries/Redis.php
 */

// Load .env variables
$env_path = dirname(dirname(dirname(__FILE__))) . '/.env';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(trim($parts[0]) . '=' . trim($parts[1]));
        }
    }
}

// Default connection group
$config['redis_default']['host'] = getenv('REDIS_HOST') ?: '127.0.0.1';
$config['redis_default']['port'] = getenv('REDIS_PORT') ?: '6379';
$config['redis_default']['password'] = getenv('REDIS_PASSWORD') ?: '';

$config['redis_slave']['host'] = '';
$config['redis_slave']['port'] = '6379';
$config['redis_slave']['password'] = '';

$config['redis_host'] = getenv('REDIS_HOST') ?: '127.0.0.1';
$config['redis_port'] = getenv('REDIS_PORT') ?: '6379';
$config['redis_password'] = getenv('REDIS_PASSWORD') ?: '';