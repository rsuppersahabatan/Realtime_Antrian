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
 *                                            Body: id_layanan (required), nik (optional)
 *   POST   api/antrian/call               -> panggil antrian berikutnya di sebuah loket
 *                                            Body: id_loket (required)
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
	 * Body: id_layanan (required), nik (optional)
	 */
	public function index_post()
	{
		$id_layanan = (int) $this->post('id_layanan');
		$nik        = $this->post('nik');

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

		$tiket = $this->Antrian_model->generate_nomor_baru($id_layanan, $nik);

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
