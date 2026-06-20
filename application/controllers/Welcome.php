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

    /**
     * Proxy server-side: Check-in mandiri pendonor UTDRS.
     * Browser cukup POST {nik}; controller ini yang menyimpan JWT & memanggil
     * API UTDRS (Authorization: Bearer) sehingga kredensial tidak ke klien.
     */
    public function self_checkin()
    {
        $this->_proxy_utdrs('/self-checkin');
    }

    /**
     * Proxy server-side: Pendaftaran mandiri pendonor UTDRS.
     */
    public function self_register()
    {
        $this->_proxy_utdrs('/self-register');
    }

    /**
     * Teruskan permintaan self-service ke API UTDRS dengan JWT Bearer.
     * Membaca NIK dari body JSON ({"nik":"..."}) atau form, mengelola token
     * platform (cache file + auto-refresh saat 401), lalu menormalkan respon
     * agar field di JS view (message / nomor_antrian / nama) konsisten.
     */
    private function _proxy_utdrs($path)
    {
        // CATATAN: controller ini SELALU membalas HTTP 200 ke web server. Status
        // sebenarnya ada di body JSON (field "status" = Success/Error, "httpStatus"
        // = kode upstream). Ini menghindari nginx menukar respon 5xx kita dengan
        // halaman "502 Bad Gateway" (fastcgi_intercept_errors) sehingga pesan
        // error tetap sampai ke kiosk.
        $nik = $this->_read_nik();

        if ($nik === '' || ! preg_match('/^\d{16}$/', $nik)) {
            return $this->_json_out(200, [
                'status'  => 'Error',
                'message' => 'NIK harus 16 digit angka.',
            ]);
        }

        // Butuh token statis (UTDRS_BEARER_TOKEN) ATAU kredensial platform (nip+password)
        $staticToken = (string) $this->config->item('utdrs_bearer_token');
        $nip         = (string) $this->config->item('utdrs_platform_nip');
        $pwd         = (string) $this->config->item('utdrs_platform_password');
        if ($staticToken === '' && ($nip === '' || $pwd === '')) {
            return $this->_json_out(200, [
                'status'  => 'Error',
                'message' => 'Konfigurasi UTDRS belum lengkap (isi UTDRS_BEARER_TOKEN atau kredensial platform). Hubungi petugas IT.',
            ]);
        }

        $base = rtrim((string) $this->config->item('api_utdrs_base'), '/');
        $url  = $base . $path;

        // Panggil; bila 401 (token kedaluwarsa/invalid) ambil token baru lalu ulangi sekali.
        $resp = $this->_utdrs_call($url, ['nik' => $nik], false);
        if ($resp['status'] === 401) {
            $resp = $this->_utdrs_call($url, ['nik' => $nik], true);
        }

        if ($resp['status'] === 0) {
            log_message('error', 'UTDRS proxy gagal koneksi ke ' . $url . ' :: ' . $resp['error']);
            return $this->_json_out(200, [
                'status'     => 'Error',
                'httpStatus' => 0,
                'message'    => 'Tidak dapat terhubung ke server UTDRS. Coba lagi atau hubungi petugas.',
                'detail'     => $resp['error'],
            ]);
        }

        if ($resp['status'] < 200 || $resp['status'] >= 300) {
            log_message('error', 'UTDRS proxy upstream HTTP ' . $resp['status'] . ' dari ' . $url
                . ' :: ' . (is_array($resp['body']) ? json_encode($resp['body']) : 'non-JSON'));
        }

        // Normalisasi: SIMRS/proxy balas {status, pesan, results:{nomor_antrian, nama_pendonor}}
        $body    = is_array($resp['body']) ? $resp['body'] : [];
        $results = isset($body['results']) && is_array($body['results']) ? $body['results'] : [];

        $out = array_merge($body, [
            'status'        => isset($body['status']) ? $body['status'] : ($resp['status'] >= 200 && $resp['status'] < 300 ? 'Success' : 'Error'),
            'httpStatus'    => $resp['status'],
            'message'       => isset($body['pesan']) ? $body['pesan'] : (isset($body['message']) ? $body['message'] : ''),
            'nomor_antrian' => isset($results['nomor_antrian']) ? $results['nomor_antrian'] : (isset($body['nomor_antrian']) ? $body['nomor_antrian'] : null),
            'nama'          => isset($results['nama_pendonor']) ? $results['nama_pendonor'] : (isset($body['nama']) ? $body['nama'] : null),
        ]);

        return $this->_json_out(200, $out);
    }

    /**
     * Lakukan satu kali HTTP POST ke endpoint UTDRS dengan Bearer token.
     * $forceFresh = true memaksa ambil token baru (abaikan cache).
     * Return: ['status' => int http code (0 jika gagal koneksi), 'body' => array|null, 'error' => string]
     */
    private function _utdrs_call($url, array $payload, $forceFresh)
    {
        $token = $this->_utdrs_token($forceFresh);
        if ($token === '') {
            return ['status' => 0, 'body' => null, 'error' => 'Token UTDRS tidak tersedia (cek kredensial platform).'];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Persahabatan ' . $token,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $raw  = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            log_message('error', 'UTDRS call cURL error (' . $url . '): ' . $err);
            return ['status' => 0, 'body' => null, 'error' => $err ?: 'koneksi gagal'];
        }

        return ['status' => $code, 'body' => json_decode($raw, true), 'error' => ''];
    }

    /**
     * Ambil JWT platform; cache di file. $forceFresh menghapus cache lalu login ulang.
     */
    private function _utdrs_token($forceFresh = false)
    {
        // Token statis dari .env diutamakan (lihat catatan DB group di rta_config).
        // Tidak ada login & tidak ada cache — relogin tak relevan untuk token statis.
        $staticToken = trim((string) $this->config->item('utdrs_bearer_token'));
        if ($staticToken !== '') {
            return $staticToken;
        }

        $cacheFile = APPPATH . 'cache/utdrs_token.txt';

        if (! $forceFresh && is_file($cacheFile)) {
            $cached = trim((string) @file_get_contents($cacheFile));
            if ($cached !== '') {
                return $cached;
            }
        }

        $nip      = (string) $this->config->item('utdrs_platform_nip');
        $password = (string) $this->config->item('utdrs_platform_password');
        if ($nip === '' || $password === '') {
            return '';
        }

        // Endpoint login platform: berada di bawah grup "prima" di API
        // (Routes.php -> $routes->group("prima", ...){ post("login/platform") }),
        // BUKAN di root host maupun di bawah /server/darah.
        $base      = rtrim((string) $this->config->item('api_utdrs_base'), '/');
        $rootHost  = preg_replace('#/server/darah/?$#', '', $base);
        $loginUrl  = $rootHost . '/prima/login/platform';

        $ch = curl_init($loginUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query(['nip' => $nip, 'password' => $password]),
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $raw  = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            log_message('error', 'UTDRS login/platform cURL error (' . $loginUrl . '): ' . $err);
            return '';
        }

        $data  = json_decode($raw, true);
        $token = is_array($data) && isset($data['access_token']) ? (string) $data['access_token'] : '';

        if ($token === '') {
            log_message('error', 'UTDRS login/platform (' . $loginUrl . ') HTTP ' . $code
                . ' gagal memperoleh access_token. Respons: ' . substr($raw, 0, 500));
            return '';
        }

        @file_put_contents($cacheFile, $token, LOCK_EX);

        return $token;
    }

    /**
     * Baca NIK dari body JSON ({"nik":"..."}) atau form POST, bersihkan non-digit.
     */
    private function _read_nik()
    {
        $nik = (string) $this->input->post('nik');
        if ($nik === '') {
            $raw = json_decode((string) $this->input->raw_input_stream, true);
            if (is_array($raw) && isset($raw['nik'])) {
                $nik = (string) $raw['nik'];
            }
        }
        return preg_replace('/\D+/', '', $nik);
    }

    /**
     * Output JSON dengan status header tertentu.
     */
    private function _json_out($code, array $payload)
    {
        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload));
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
