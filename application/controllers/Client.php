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
        $this->data['loket'] = $this->Loket_model->get_loket_buka();
        $this->load->view('client/display', $this->data);
    }
}