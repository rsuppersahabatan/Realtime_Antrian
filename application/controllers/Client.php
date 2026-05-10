<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client extends Public_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Client_model', 'Layanan_model', 'Loket_model', 'Antrian_model']);
    }

    public function index($id = null)
    {
        // chmod -R 775 /www/wwwroot/antrian/public/assets/frameworks/domprojects/
        // chown -R www:www /www/wwwroot/antrian/public/assets/frameworks/domprojects/

        $id = (int) $id;
        $client = $id ? $this->Client_model->get_by_id($id) : null; //echo "<pre>"; print_r($client); exit;
        if ( ! $client)
        {
            $this->_allView();
            return;
        }

        $this->data['client'] = $client;
        $this->data['display'] = $this->_get_display_settings($id);

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

        $allowed_ids = array_map('intval', array_column((array) $client['lokets'], 'id'));
        $this->data['loket'] = $this->Loket_model->get_by_ids_with_last_nomor($allowed_ids);

        $this->load->view('client/display', $this->data);
    }

    private function _get_display_settings($id_client)
    {
        $row = $this->db->get_where('client_display_settings', array('id_client' => (int) $id_client))->row_array();

        // Default selaras dengan client.css (teal + chartreuse) supaya tampilan tetap rapih
        // walau record client_display_settings belum dibuat.
        $defaults = array(
            'color_scheme' => '#00baae',
            'video_source' => 'youtube',
            'video_link'   => 'ebZwRzwEpT8',
            'footer_text'  => 'Selamat datang. Mohon menunggu nomor antrian Anda dipanggil.',
            'footer_mode'  => 'running',
            'font_family'  => 'Inter',
            'font_size'    => 100,
        );

        if ( ! $row)
        {
            return $defaults;
        }

        foreach ($defaults as $k => $v)
        {
            if ( ! isset($row[$k]) || $row[$k] === '' || $row[$k] === null)
            {
                $row[$k] = $v;
            }
        }
        $row['font_size'] = (int) $row['font_size'];
        return $row;
    }
    
    private function _allView()
    {
        $this->data['clients'] = $this->Client_model->get_all();
        $this->load->view('client/all', $this->data);
    }
}