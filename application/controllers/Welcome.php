<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Antrian Online — halaman publik tempat pengunjung memilih layanan dan
 * mencetak nomor antrian. Setelah tiket terbit, nomor akan muncul di display
 * antrian (Client::index) begitu petugas memanggil via admin/panggilan.
 * 
 */
class Welcome extends Public_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper(['form', 'url']);
        $this->load->model(['Layanan_model', 'Loket_model', 'Antrian_model']);
    }

    public function index()
    {
        $this->_init_minify();
        $this->data['mode']           = 'pilih';
        $this->data['layanan']        = $this->Layanan_model->get_all_show_welcome();
        $this->data['loket']          = $this->Loket_model->get_loket_buka();
        $this->data['error']          = $this->session->flashdata('error');
        $this->data['nik_old']        = (string) $this->session->flashdata('nik');
        // Base URL UTDRS dipakai view untuk membentuk endpoint self-checkin /
        // self-register di JS (lihat .env -> API_UTDRS_BASE).
        $this->data['api_utdrs_base'] = rtrim((string) $this->config->item('api_utdrs_base'), '/');
        $this->load->view('welcome_message', $this->data);
    }

    /**
     * Ambil nomor antrian untuk layanan yang dipilih (POST).
     * Memvalidasi NIK (16 digit numerik), mencetak tiket baru, publish
     * notifikasi realtime, lalu redirect ke halaman tiket.
     */
    public function ambil()
    {
        $id_layanan = (int) $this->input->post('id_layanan');
        $nik        = preg_replace('/\D+/', '', (string) $this->input->post('nik'));

        if ($nik === '' || strlen($nik) !== 16)
        {
            $this->session->set_flashdata('error', 'NIK harus 16 digit angka.');
            $this->session->set_flashdata('nik', $nik);
            redirect('welcome', 'refresh');
            return;
        }

        if ( ! $id_layanan)
        {
            $this->session->set_flashdata('error', 'Silakan pilih layanan terlebih dahulu.');
            $this->session->set_flashdata('nik', $nik);
            redirect('welcome', 'refresh');
            return;
        }

        $layanan = $this->Layanan_model->get_by_id($id_layanan);
        if ( ! $layanan)
        {
            $this->session->set_flashdata('error', 'Layanan tidak ditemukan.');
            $this->session->set_flashdata('nik', $nik);
            redirect('welcome', 'refresh');
            return;
        }

        $tiket = $this->Antrian_model->generate_nomor_baru($id_layanan, $nik);
        if ( ! $tiket)
        {
            $this->session->set_flashdata('error', 'Gagal mencetak nomor antrian.');
            $this->session->set_flashdata('nik', $nik);
            redirect('welcome', 'refresh');
            return;
        }

        // Beri tahu display / admin bahwa ada tiket baru diterbitkan. (Sudah di-handle otomatis oleh Antrian_model::generate_nomor_baru)
        // $this->redis->command('publish realtime antrian-baru-'.$tiket['nomor_antrian']);

        $tiket['nama_layanan'] = $layanan['nama_layanan'];
        $tiket['kode_huruf']   = $layanan['kode_huruf'];

        $this->session->set_flashdata('tiket', $tiket);
        redirect('welcome/tiket/'.$tiket['id'], 'refresh');
    }

    /**
     * Tampilkan tiket yang baru dicetak. Data tiket dibaca dari flashdata agar
     * refresh tidak mencetak tiket ganda — hanya sekali tampil, lalu hilang.
     */
    public function tiket($id_antrian = NULL)
    {
        $tiket = $this->session->flashdata('tiket');
        if ( ! $tiket OR (int) $id_antrian !== (int) $tiket['id'])
        {
            redirect('welcome', 'refresh');
            return;
        }

        // Hitung berapa tiket yang masih menunggu di depan tiket ini.
        $this->db->where('id_layanan', $tiket['id_layanan']);
        $this->db->where('tanggal', $tiket['tanggal']);
        $this->db->where('status', 'menunggu');
        $this->db->where('nomor_urut <', $tiket['nomor_urut']);
        $tiket['antrian_di_depan'] = $this->db->count_all_results('antrian');

        $this->_init_minify();
        $this->data['mode']  = 'tiket';
        $this->data['tiket'] = $tiket;
        $this->data['loket'] = $this->Loket_model->get_loket_buka();
        $this->load->view('welcome_message', $this->data);
    }

    private function _init_minify()
    {
        $this->load->library('minify');
        $this->minify->css_dir = 'assets/frameworks/domprojects/css';
        $this->minify->js_dir = 'assets/frameworks/domprojects/js';
        $this->minify->assets_dir_css = 'assets/frameworks/domprojects/css';
        $this->minify->assets_dir_js = 'assets/frameworks/domprojects/js';
        $this->minify->compression_engine = array('css' => 'minify', 'js' => 'jsmin');
        
        $this->minify->css('welcome.css');
        $this->minify->js('welcome.js');

        $this->data['minified_css'] = $this->minify->deploy_css(TRUE, 'welcome.min.css');
        $this->data['minified_js'] = $this->minify->deploy_js(TRUE, 'welcome.min.js');
    }
}

/* End of file Welcome.php */
/* Location: ./application/controllers/Welcome.php */
