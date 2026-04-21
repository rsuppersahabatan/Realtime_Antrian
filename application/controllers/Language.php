<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Language extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function set($lang = NULL)
    {
        $available = array_keys($this->data['available_languages']);

        if ($lang && in_array($lang, $available, TRUE))
        {
            $this->session->set_userdata('app_language', $lang);
        }

        $referrer = $this->input->server('HTTP_REFERER');
        if ($referrer)
        {
            redirect($referrer, 'refresh');
        }
        else
        {
            redirect('admin/dashboard', 'refresh');
        }
    }
}
