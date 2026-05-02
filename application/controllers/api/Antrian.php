<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

/**
 * REST API — Antrian (Transaksi Antrian Harian)
 *
 * Endpoint:
 *   GET    api/antrian                    -> daftar antrian hari ini
 *                                            ?tanggal=YYYY-MM-DD untuk filter tanggal
 *   POST   api/antrian                    -> generate nomor antrian baru
 *                                            Body: id_layanan (required), nik (optional), keterangan (optional),
 *                                                  nomor_antrian (optional — override nomor tiket manual)
 *   POST   api/antrian/call               -> panggil antrian berikutnya di sebuah loket
 *                                            Body: id_loket (required)
 *   POST   api/antrian/panggilansimpan    -> simpan panggilan (manual / panggil ulang)
 *                                            Body: id_antrian (required), id_loket (required)
 *   PUT    api/antrian/selesai/{id}       -> tandai antrian selesai
 *   PUT    api/antrian/batal/{id}         -> tandai antrian batal
 *   DELETE api/antrian/{id}               -> hapus record antrian
 */
class Antrian extends RestController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Antrian_model');
		$this->load->model('Layanan_model');
		$this->load->model('Loket_model');
	}


	/**
	 * GET api/antrian
	 * Query: ?tanggal=YYYY-MM-DD (default hari ini)
	 */
	public function index_get()
	{
		$tanggal = $this->get('tanggal');
		if ( ! $tanggal OR ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal))
		{
			$tanggal = date('Y-m-d');
		}

		$antrian = $this->Antrian_model->get_antrian_hari_ini($tanggal);

		$rekap = ['menunggu' => 0, 'dipanggil' => 0, 'selesai' => 0, 'batal' => 0];
		foreach ($antrian as $row)
		{
			if (isset($rekap[$row['status']]))
			{
				$rekap[$row['status']]++;
			}
		}

		$this->response([
			'status'  => TRUE,
			'tanggal' => $tanggal,
			'rekap'   => $rekap,
			'data'    => $antrian,
		], RestController::HTTP_OK);
	}


	/**
	 * POST api/antrian
	 * Body: id_layanan (required), nik (optional), keterangan (optional),
	 *       nomor_antrian (optional — override tiket manual)
	 */
	public function index_post()
	{
		$id_layanan    = (int) $this->post('id_layanan');
		$nik           = $this->post('nik');
		$keterangan    = $this->post('keterangan');
		$nomor_antrian = $this->post('nomor_antrian');

		if ($id_layanan <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Field id_layanan wajib diisi',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ($nik !== NULL && $nik !== '' && ! preg_match('/^\d{16}$/', (string) $nik))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Format NIK tidak valid (harus 16 digit angka)',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if (is_string($nomor_antrian))
		{
			$nomor_antrian = trim($nomor_antrian);
			if ($nomor_antrian === '') $nomor_antrian = NULL;
		}

		$tiket = $this->Antrian_model->generate_nomor_baru($id_layanan, $nik, $keterangan, $nomor_antrian);

		if ( ! $tiket)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Gagal membuat antrian: layanan tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$this->response([
			'status'  => TRUE,
			'message' => 'Nomor antrian berhasil dibuat',
			'data'    => $tiket,
		], RestController::HTTP_CREATED);
	}


	/**
	 * POST api/antrian/call
	 * Body: id_loket (required)
	 * Panggil antrian paling lama menunggu untuk layanan loket tersebut.
	 */
	public function call_post()
	{
		$id_loket = (int) $this->post('id_loket');

		if ($id_loket <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Field id_loket wajib diisi',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! $this->Loket_model->get_by_id($id_loket))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Loket tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$nomor_antrian = $this->Antrian_model->call_next_antrian($id_loket);

		if ($nomor_antrian === NULL)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Tidak ada antrian yang menunggu untuk loket ini',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$this->response([
			'status'  => TRUE,
			'message' => 'Antrian berhasil dipanggil',
			'data'    => [
				'id_loket'      => $id_loket,
				'nomor_antrian' => $nomor_antrian,
				'waktu_panggil' => date('Y-m-d H:i:s'),
			],
		], RestController::HTTP_OK);
	}


	/**
	 * POST api/antrian/panggilansimpan
	 * Body: id_antrian (required), id_loket (required)
	 * Simpan panggilan manual — bisa dipakai untuk panggil ulang
	 * atau memanggil nomor tertentu (bukan otomatis "berikutnya").
	 */
	public function panggilansimpan_post()
	{
		$id_antrian = (int) $this->post('id_antrian');
		$id_loket   = (int) $this->post('id_loket');

		if ($id_antrian <= 0 OR $id_loket <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Field id_antrian dan id_loket wajib diisi',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$result = $this->Antrian_model->simpan_panggilan($id_antrian, $id_loket);

		switch ($result['code'])
		{
			case 'antrian_not_found':
				$this->response([
					'status'  => FALSE,
					'message' => 'Antrian tidak ditemukan',
				], RestController::HTTP_NOT_FOUND);
				return;

			case 'loket_not_found':
				$this->response([
					'status'  => FALSE,
					'message' => 'Loket tidak ditemukan',
				], RestController::HTTP_NOT_FOUND);
				return;

			case 'layanan_mismatch':
				$this->response([
					'status'  => FALSE,
					'message' => 'Loket ini tidak melayani layanan antrian tersebut',
				], RestController::HTTP_CONFLICT);
				return;

			case 'status_final':
				$this->response([
					'status'  => FALSE,
					'message' => 'Antrian sudah selesai/batal dan tidak dapat dipanggil',
					'data'    => $result['data'],
				], RestController::HTTP_CONFLICT);
				return;
		}

		$data = $result['data'];
		$this->response([
			'status'  => TRUE,
			'message' => $data['is_ulang']
				? 'Panggilan ulang berhasil disimpan'
				: 'Panggilan berhasil disimpan',
			'data'    => [
				'id_antrian'    => (int) $data['id'],
				'nomor_antrian' => $data['nomor_antrian'],
				'id_loket'      => (int) $data['id_loket'],
				'nama_loket'    => $data['nama_loket'],
				'waktu_panggil' => $data['waktu_panggil'],
				'is_ulang'      => (bool) $data['is_ulang'],
			],
		], RestController::HTTP_OK);
	}


	/**
	 * PUT api/antrian/selesai/{id}
	 */
	public function selesai_put($id = NULL)
	{
		return $this->_update_status($id, 'selesai');
	}


	/**
	 * PUT api/antrian/batal/{id}
	 */
	public function batal_put($id = NULL)
	{
		return $this->_update_status($id, 'batal');
	}


	/**
	 * DELETE api/antrian/{id}
	 */
	public function index_delete($id = NULL)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID antrian tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$exists = $this->db->get_where('antrian', ['id' => $id])->row_array();
		if ( ! $exists)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Antrian tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$this->db->where('id', $id)->delete('antrian');
		$this->response([
			'status'  => TRUE,
			'message' => 'Antrian berhasil dihapus',
		], RestController::HTTP_OK);
	}


	// ------------------------------------------------------------------
	// Internal helper
	// ------------------------------------------------------------------

	private function _update_status($id, $status)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID antrian tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$row = $this->db->get_where('antrian', ['id' => $id])->row_array();
		if ( ! $row)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Antrian tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$data_update = ['status' => $status];
		if ($status === 'selesai')
		{
			$data_update['waktu_selesai'] = date('Y-m-d H:i:s');
		}

		$this->db->where('id', $id)->update('antrian', $data_update);

		$this->response([
			'status'  => TRUE,
			'message' => "Antrian berhasil diupdate ke status '$status'",
			'data'    => array_merge($row, $data_update),
		], RestController::HTTP_OK);
	}
}
