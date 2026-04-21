<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

/**
 * REST API — Loket (Meja Petugas)
 *
 * Endpoint:
 *   GET    api/loket                 -> list semua loket (termasuk users)
 *   GET    api/loket/{id}            -> detail satu loket (termasuk users)
 *   GET    api/loket/buka            -> list loket yang sedang buka
 *                                       ?with_last=1 untuk menyertakan nomor_terakhir hari ini
 *   GET    api/loket/users/{id}      -> daftar user yang ter-assign ke loket
 *   POST   api/loket                 -> tambah loket baru (opsional: id_users[])
 *   PUT    api/loket/status/{id}     -> update status buka/tutup (body: status_buka)
 *   PUT    api/loket/users/{id}      -> sinkronisasi user loket (body: id_users[])
 *   DELETE api/loket/{id}            -> hapus loket
 */
class Loket extends RestController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Loket_model');
		$this->load->model('Layanan_model');
	}


	/**
	 * GET api/loket       — list
	 * GET api/loket/{id}  — detail
	 */
	public function index_get($id = NULL)
	{
		if ($id === NULL)
		{
			$this->response([
				'status' => TRUE,
				'data'   => $this->Loket_model->get_all(),
			], RestController::HTTP_OK);
			return;
		}

		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID loket tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$row = $this->Loket_model->get_by_id($id);
		if ( ! $row)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Loket tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$this->response([
			'status' => TRUE,
			'data'   => $row,
		], RestController::HTTP_OK);
	}


	/**
	 * GET api/loket/buka
	 * Query: ?with_last=1  -> sertakan nomor antrian terakhir hari ini per loket
	 *        ?tanggal=YYYY-MM-DD (dipakai saat with_last=1)
	 */
	public function buka_get()
	{
		$with_last = (int) $this->get('with_last') === 1;

		if ($with_last)
		{
			$tanggal = $this->get('tanggal');
			if ( ! $tanggal OR ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal))
			{
				$tanggal = NULL;
			}
			$data = $this->Loket_model->get_loket_buka_with_last_nomor($tanggal);
		}
		else
		{
			$data = $this->Loket_model->get_loket_buka();
		}

		$this->response([
			'status' => TRUE,
			'data'   => $data,
		], RestController::HTTP_OK);
	}


	/**
	 * POST api/loket
	 * Body: id_layanan (required), nama_loket (required),
	 *       status_buka (opsional: buka|tutup),
	 *       id_users (opsional: array of user id)
	 */
	public function index_post()
	{
		$id_layanan  = (int) $this->post('id_layanan');
		$nama_loket  = trim((string) $this->post('nama_loket'));
		$status_buka = $this->post('status_buka');
		$id_users    = $this->post('id_users');

		if ($id_layanan <= 0 OR $nama_loket === '')
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Field id_layanan dan nama_loket wajib diisi',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! $this->Layanan_model->get_by_id($id_layanan))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'id_layanan tidak valid / layanan tidak ditemukan',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$status_buka = in_array($status_buka, ['buka', 'tutup'], TRUE) ? $status_buka : 'tutup';

		$user_ids = $this->_normalize_user_ids($id_users);
		$invalid  = $this->_invalid_user_ids($user_ids);
		if ( ! empty($invalid))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'id_users mengandung user yang tidak valid',
				'invalid' => array_values($invalid),
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$data = [
			'id_layanan'  => $id_layanan,
			'nama_loket'  => $nama_loket,
			'status_buka' => $status_buka,
		];

		if ( ! $this->Loket_model->insert($data))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Gagal menambah loket',
			], RestController::HTTP_INTERNAL_ERROR);
			return;
		}

		$new_id = $this->db->insert_id();
		$this->Loket_model->sync_users($new_id, $user_ids);

		$this->response([
			'status'  => TRUE,
			'message' => 'Loket berhasil ditambahkan',
			'data'    => $this->Loket_model->get_by_id($new_id),
		], RestController::HTTP_CREATED);
	}


	/**
	 * PUT api/loket/status/{id}
	 * Body: status_buka (buka|tutup)
	 */
	public function status_put($id = NULL)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID loket tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! $this->Loket_model->get_by_id($id))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Loket tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$status = $this->put('status_buka');
		if ( ! in_array($status, ['buka', 'tutup'], TRUE))
		{
			$this->response([
				'status'  => FALSE,
				'message' => "Field status_buka wajib 'buka' atau 'tutup'",
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$this->Loket_model->update_status($id, $status);
		$this->response([
			'status'  => TRUE,
			'message' => 'Status loket berhasil diupdate',
			'data'    => $this->Loket_model->get_by_id($id),
		], RestController::HTTP_OK);
	}


	/**
	 * DELETE api/loket/{id}
	 */
	public function index_delete($id = NULL)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID loket tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! $this->Loket_model->get_by_id($id))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Loket tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$this->Loket_model->delete($id);
		$this->response([
			'status'  => TRUE,
			'message' => 'Loket berhasil dihapus',
		], RestController::HTTP_OK);
	}


	/**
	 * GET api/loket/users/{id}
	 * Daftar user yang ter-assign ke loket.
	 */
	public function users_get($id = NULL)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID loket tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$loket = $this->Loket_model->get_by_id($id);
		if ( ! $loket)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Loket tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$this->response([
			'status'  => TRUE,
			'id_loket' => $id,
			'data'    => isset($loket['users']) ? $loket['users'] : [],
		], RestController::HTTP_OK);
	}


	/**
	 * PUT api/loket/users/{id}
	 * Body: id_users (array of user id) — replace-all (sync).
	 * Kirim array kosong untuk meng-unassign semua user.
	 */
	public function users_put($id = NULL)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID loket tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! $this->Loket_model->get_by_id($id))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Loket tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$raw = $this->put('id_users');
		if ($raw === NULL)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Field id_users wajib diisi (boleh array kosong)',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$user_ids = $this->_normalize_user_ids($raw);
		$invalid  = $this->_invalid_user_ids($user_ids);
		if ( ! empty($invalid))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'id_users mengandung user yang tidak valid',
				'invalid' => array_values($invalid),
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! $this->Loket_model->sync_users($id, $user_ids))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Gagal menyinkronkan user loket',
			], RestController::HTTP_INTERNAL_ERROR);
			return;
		}

		$this->response([
			'status'  => TRUE,
			'message' => 'User loket berhasil disinkronkan',
			'data'    => $this->Loket_model->get_by_id($id),
		], RestController::HTTP_OK);
	}


	/**
	 * Normalisasi input id_users: terima array, CSV string, atau JSON array string.
	 * Kembalikan array int unik dan > 0.
	 */
	protected function _normalize_user_ids($raw)
	{
		if ($raw === NULL OR $raw === '')
		{
			return [];
		}

		if (is_string($raw))
		{
			$trim = trim($raw);
			if ($trim !== '' AND $trim[0] === '[')
			{
				$decoded = json_decode($trim, TRUE);
				if (is_array($decoded))
				{
					$raw = $decoded;
				}
			}
			if (is_string($raw))
			{
				$raw = array_map('trim', explode(',', $raw));
			}
		}

		if ( ! is_array($raw))
		{
			return [];
		}

		$ids = array_map('intval', $raw);
		$ids = array_filter($ids, function ($v) { return $v > 0; });
		return array_values(array_unique($ids));
	}


	/**
	 * Kembalikan id user yang tidak eksis di tabel users.
	 */
	protected function _invalid_user_ids(array $user_ids)
	{
		if (empty($user_ids))
		{
			return [];
		}
		$this->db->select('id');
		$this->db->from('users');
		$this->db->where_in('id', $user_ids);
		$rows = $this->db->get()->result_array();
		$existing = array_map('intval', array_column($rows, 'id'));
		return array_diff($user_ids, $existing);
	}
}
