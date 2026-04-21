<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

/**
 * REST API — Layanan (Kategori Antrian)
 *
 * Endpoint:
 *   GET    api/layanan           -> list semua layanan
 *   GET    api/layanan/{id}      -> detail satu layanan
 *   POST   api/layanan           -> tambah layanan baru
 *   PUT    api/layanan/{id}      -> update layanan
 *   DELETE api/layanan/{id}      -> hapus layanan
 */
class Layanan extends RestController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Layanan_model');
	}


	/**
	 * GET api/layanan       — list
	 * GET api/layanan/{id}  — detail
	 */
	public function index_get($id = NULL)
	{
		if ($id === NULL)
		{
			$data = $this->Layanan_model->get_all();
			$this->response([
				'status' => TRUE,
				'data'   => $data,
			], RestController::HTTP_OK);
			return;
		}

		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID layanan tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$row = $this->Layanan_model->get_by_id($id);
		if ( ! $row)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Layanan tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$this->response([
			'status' => TRUE,
			'data'   => $row,
		], RestController::HTTP_OK);
	}


	/**
	 * POST api/layanan
	 * Body: kode_huruf (required), nama_layanan (required), keterangan (optional)
	 */
	public function index_post()
	{
		$kode_huruf   = trim((string) $this->post('kode_huruf'));
		$nama_layanan = trim((string) $this->post('nama_layanan'));
		$keterangan   = $this->post('keterangan');

		if ($kode_huruf === '' OR $nama_layanan === '')
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Field kode_huruf dan nama_layanan wajib diisi',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$data = [
			'kode_huruf'   => $kode_huruf,
			'nama_layanan' => $nama_layanan,
			'keterangan'   => ($keterangan !== NULL && $keterangan !== '') ? $keterangan : NULL,
		];

		if ( ! $this->Layanan_model->insert($data))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Gagal menambah layanan (cek duplikasi kode_huruf)',
			], RestController::HTTP_INTERNAL_ERROR);
			return;
		}

		$data['id'] = $this->db->insert_id();
		$this->response([
			'status'  => TRUE,
			'message' => 'Layanan berhasil ditambahkan',
			'data'    => $data,
		], RestController::HTTP_CREATED);
	}


	/**
	 * PUT api/layanan/{id}
	 * Body: kode_huruf / nama_layanan / keterangan (partial)
	 */
	public function index_put($id = NULL)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID layanan tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! $this->Layanan_model->get_by_id($id))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Layanan tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$allowed = ['kode_huruf', 'nama_layanan', 'keterangan'];
		$data    = [];
		foreach ($allowed as $field)
		{
			$val = $this->put($field);
			if ($val !== NULL)
			{
				$data[$field] = ($val === '') ? NULL : $val;
			}
		}

		if (empty($data))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Tidak ada field yang diupdate',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$this->Layanan_model->update($id, $data);
		$this->response([
			'status'  => TRUE,
			'message' => 'Layanan berhasil diupdate',
			'data'    => $this->Layanan_model->get_by_id($id),
		], RestController::HTTP_OK);
	}


	/**
	 * DELETE api/layanan/{id}
	 */
	public function index_delete($id = NULL)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID layanan tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! $this->Layanan_model->get_by_id($id))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Layanan tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$this->Layanan_model->delete($id);
		$this->response([
			'status'  => TRUE,
			'message' => 'Layanan berhasil dihapus',
		], RestController::HTTP_OK);
	}
}
