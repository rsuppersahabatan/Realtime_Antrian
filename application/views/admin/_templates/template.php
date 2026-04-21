<?php
defined('BASEPATH') OR exit('No direct script access allowed');

header('X-Powered-By: Prod-domProjects.com');
header('X-XSS-Protection: 1');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Vary: Accept-Encoding');

if (isset($header))
{
    echo $header;
}

if (isset($main_header))
{
    echo $main_header;
}

if (isset($main_sidebar))
{
    echo $main_sidebar;
}

if (isset($content))
{
    echo $content;
}

if (isset($control_sidebar) AND ! empty($admin_prefs['ctrl_sidebar']))
{
    echo $control_sidebar;
}

if (isset($footer))
{
    echo $footer;
}
