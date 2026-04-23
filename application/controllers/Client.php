<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client extends Public_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Layanan_model', 'Loket_model', 'Antrian_model']);
    }

    public function index()
    {
        // chmod -R 775 /www/wwwroot/antrian/public/assets/frameworks/domprojects/
        // chown -R www:www /www/wwwroot/antrian/public/assets/frameworks/domprojects/

        $this->load->library('minify');
        $this->minify->css_dir = 'assets/frameworks/domprojects/css';
        $this->minify->js_dir = 'assets/frameworks/domprojects/js';
        $this->minify->assets_dir_css = 'assets/frameworks/domprojects/css';
        $this->minify->assets_dir_js = 'assets/frameworks/domprojects/js';
        $this->minify->compression_engine = array('css' => 'minify', 'js' => 'jsmin');
        
        $this->minify->css('client.css');
        $this->minify->js('client.js');

        $this->data['minified_css'] = $this->minify->deploy_css(TRUE, 'client.min.css');
        $this->data['minified_js'] = $this->minify->deploy_js(TRUE, 'client.min.js');

        $this->data['loket'] = $this->Loket_model->get_loket_buka_with_last_nomor();
        $this->load->view('client/display', $this->data);
    }
}