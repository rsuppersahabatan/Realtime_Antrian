<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Panggilan extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		/* Load :: Common */
		$this->lang->load('admin/panggilan');
		$this->load->model(['Layanan_model', 'Loket_model', 'Antrian_model']);

		/* Title Page :: Common */
		$this->page_title->push(lang('menu_panggilan'));
		$this->data['pagetitle'] = $this->page_title->show();

		/* Breadcrumbs :: Common */
		$this->breadcrumbs->unshift(1, lang('menu_panggilan'), 'admin/panggilan');
	}


	public function index()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}

		/* Breadcrumbs */
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Data loket yang buka */
		$this->data['loket'] = $this->Loket_model->get_loket_buka();

		/* Load Template */
		$this->template->admin_render('admin/panggilan/index', $this->data);
	}


	/**
	 * Panggil antrian berikutnya untuk loket tertentu (AJAX, JSON).
	 */
	public function call($id_loket = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			return $this->_json(['status' => 'error', 'message' => 'unauthorized'], 401);
		}

		$id_loket = (int) $id_loket;
		$loket    = $id_loket ? $this->Loket_model->get_by_id($id_loket) : NULL;

		if ( ! $loket OR $loket['status_buka'] != 'buka')
		{
			return $this->_json(['status' => 'error', 'message' => lang('panggilan_loket_invalid')]);
		}

		$tiket = $this->Antrian_model->call_next_antrian($id_loket);

		if ( ! $tiket)
		{
			return $this->_json(['status' => 'kosong', 'message' => lang('panggilan_antrian_habis')]);
		}

		$nomor      = $tiket['nomor_antrian'];
		$keterangan = isset($tiket['keterangan']) ? $tiket['keterangan'] : '';
		$channel    = $this->_channel($id_loket);
		$this->_publish($channel, $nomor, $keterangan);

		return $this->_json([
			'status'     => 'ok',
			'nomor'      => $nomor,
			'keterangan' => $keterangan,
			'loket'      => $loket['nama_loket'],
			'channel'    => $channel,
		]);
	}


	/**
	 * Ulangi panggilan antrian terakhir untuk loket tertentu (AJAX, JSON).
	 * Hanya mem-broadcast ulang via Redis, tidak mengubah data antrian.
	 */
	public function recall($id_loket = NULL, $nomor = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			return $this->_json(['status' => 'error', 'message' => 'unauthorized'], 401);
		}

		$id_loket = (int) $id_loket;
		$nomor    = trim((string) $nomor);
		$loket    = $id_loket ? $this->Loket_model->get_by_id($id_loket) : NULL;

		if ( ! $loket OR $nomor === '' OR ! preg_match('/^[A-Za-z0-9]+$/', $nomor))
		{
			return $this->_json(['status' => 'error', 'message' => lang('panggilan_error')]);
		}

		// Cari tiket asli untuk dapat keterangan-nya (recall = broadcast ulang).
		$tiket      = $this->Antrian_model->get_by_nomor_today($nomor, $id_loket);
		$keterangan = ($tiket && isset($tiket['keterangan'])) ? $tiket['keterangan'] : '';

		$channel = $this->_channel($id_loket);
		$this->_publish($channel, $nomor, $keterangan);

		return $this->_json([
			'status'     => 'ok',
			'nomor'      => $nomor,
			'keterangan' => $keterangan,
			'loket'      => $loket['nama_loket'],
			'channel'    => $channel,
		]);
	}


	private function _channel($id_loket)
	{
		return 'loket'.str_pad((int) $id_loket, 2, '0', STR_PAD_LEFT);
	}


	/**
	 * Publish ke channel realtime dengan format:
	 *   loketXX-NOMOR              (tanpa keterangan)
	 *   loketXX-NOMOR|KETERANGAN   (dengan keterangan)
	 *
	 * Karakter pemisah `|` dan baris baru pada keterangan dibersihkan.
	 */
	private function _publish($channel, $nomor, $keterangan = '')
	{
		$payload = $channel.'-'.$nomor;
		$ket     = $this->_sanitize_keterangan($keterangan);
		if ($ket !== '')
		{
			$payload .= '|'.$ket;
		}
		// Pakai magic __call agar payload (yang bisa berisi spasi) di-encode
		// sebagai single Redis bulk-string, bukan dipecah lewat explode(' ').
		$this->redis->publish('realtime', $payload);
	}


	private function _sanitize_keterangan($keterangan)
	{
		$ket = (string) $keterangan;
		// Hilangkan pipe (separator), CR/LF, dan whitespace berlebih.
		$ket = str_replace(['|', "\r", "\n"], ' ', $ket);
		$ket = preg_replace('/\s+/', ' ', $ket);
		return trim($ket);
	}


	private function _json($payload, $status = 200)
	{
		return $this->output
			->set_status_header($status)
			->set_content_type('application/json')
			->set_output(json_encode($payload));
	}
}
