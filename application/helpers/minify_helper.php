<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('kompress_css'))
{
    function kompress_css($css)
    {
        // $CI    =& get_instance();
        // $CI->load->library('minify');

        // $CI->minify->css_dir = 'assets/frameworks/domprojects/css';
        // $CI->minify->js_dir = 'assets/frameworks/domprojects/js';
        // $CI->minify->assets_dir_css = 'assets/frameworks/domprojects/css';
        // $CI->minify->assets_dir_js = 'assets/frameworks/domprojects/js';
        // $CI->minify->compression_engine = array('css' => 'minify', 'js' => 'jsmin');
        
        // $CI->minify->css('welcome.css');
        // $CI->minify->js('welcome.js');

        // $data['minified_css'] = $CI->minify->deploy_css(TRUE, 'welcome.min.css');
        // $data['minified_js'] = $CI->minify->deploy_js(TRUE, 'welcome.min.js');
        
        // return $CI->minify->minify($css);
    }
}

if ( ! function_exists('kompress_js'))
{
    function kompress_js($js)
    {
        // $CI    =& get_instance();
        // return $CI->minify->minify($js);
    }
}
