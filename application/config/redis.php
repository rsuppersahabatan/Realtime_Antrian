<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Config for the CodeIgniter Redis library
 *
 * @see ../libraries/Redis.php
 */

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