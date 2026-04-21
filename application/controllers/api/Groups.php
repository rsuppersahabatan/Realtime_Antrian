<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

/**
 * REST API — Groups (Role / Group Management via ion_auth)
 *
 * Endpoint:
 *   GET    api/groups                 -> list semua group
 *   GET    api/groups/{id}            -> detail satu group
 *   GET    api/groups/users/{id}      -> list user yang tergabung dalam group {id}
 *   POST   api/groups                 -> tambah group baru
 *                                        Body: name (required), description (opsional),
 *                                              bgcolor (opsional)
 *   PUT    api/groups/{id}            -> update group (partial)
 *                                        Body: name, description, bgcolor
 *   DELETE api/groups/{id}            -> hapus group
 */
class Groups extends RestController {

	/** Nama group admin dari config ion_auth — tidak boleh dihapus/rename */
	private $admin_group;

	public function __construct()
	{
		parent::__construct();
		$this->admin_group = $this->config->item('admin_group', 'ion_auth');
	}


	/**
	 * GET api/groups       — list semua group
	 * GET api/groups/{id}  — detail satu group
	 */
	public function index_get($id = NULL)
	{
		if ($id === NULL)
		{
			$this->response([
				'status' => TRUE,
				'data'   => $this->ion_auth->groups()->result_array(),
			], RestController::HTTP_OK);
			return;
		}

		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID group tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$group = $this->ion_auth->group($id)->row_array();
		if ( ! $group)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Group tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$this->response([
			'status' => TRUE,
			'data'   => $group,
		], RestController::HTTP_OK);
	}


	/**
	 * GET api/groups/users/{id}
	 * List user yang menjadi anggota group.
	 */
	public function users_get($id = NULL)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID group tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$group = $this->ion_auth->group($id)->row_array();
		if ( ! $group)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Group tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$users = $this->ion_auth->users([$group['name']])->result_array();
		foreach ($users as $k => $u)
		{
			unset($users[$k]['password'], $users[$k]['salt'],
				$users[$k]['remember_code'], $users[$k]['forgotten_password_code'],
				$users[$k]['activation_code']);
		}

		$this->response([
			'status' => TRUE,
			'group'  => $group,
			'data'   => $users,
		], RestController::HTTP_OK);
	}


	/**
	 * POST api/groups
	 * Body: name (required), description (opsional), bgcolor (opsional)
	 */
	public function index_post()
	{
		$name        = trim((string) $this->post('name'));
		$description = (string) $this->post('description');
		$bgcolor     = $this->post('bgcolor');

		if ($name === '')
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Field name wajib diisi',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		// ion_auth membatasi nama group hanya alfanumerik + dash/underscore
		if ( ! preg_match('/^[A-Za-z0-9_-]+$/', $name))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Nama group hanya boleh berisi huruf, angka, dash, dan underscore',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$new_id = $this->ion_auth->create_group($name, $description);
		if ( ! $new_id)
		{
			$this->response([
				'status'  => FALSE,
				'message' => strip_tags($this->ion_auth->errors()) ?: 'Gagal menambah group',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ($bgcolor !== NULL && $bgcolor !== '')
		{
			$this->db->where('id', $new_id)->update('groups', ['bgcolor' => $bgcolor]);
		}

		$this->response([
			'status'  => TRUE,
			'message' => 'Group berhasil ditambahkan',
			'data'    => $this->ion_auth->group($new_id)->row_array(),
		], RestController::HTTP_CREATED);
	}


	/**
	 * PUT api/groups/{id}
	 * Body: name, description, bgcolor (partial)
	 */
	public function index_put($id = NULL)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID group tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$group = $this->ion_auth->group($id)->row_array();
		if ( ! $group)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Group tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$name        = $this->put('name');
		$description = $this->put('description');
		$bgcolor     = $this->put('bgcolor');

		// Nama admin group tidak boleh diubah agar akses superuser tidak terkunci.
		if ($name !== NULL && $group['name'] === $this->admin_group && $name !== $this->admin_group)
		{
			$this->response([
				'status'  => FALSE,
				'message' => "Nama group '{$this->admin_group}' tidak boleh diubah",
			], RestController::HTTP_FORBIDDEN);
			return;
		}

		if ($name !== NULL)
		{
			$name = trim((string) $name);
			if ($name === '' OR ! preg_match('/^[A-Za-z0-9_-]+$/', $name))
			{
				$this->response([
					'status'  => FALSE,
					'message' => 'Nama group hanya boleh berisi huruf, angka, dash, dan underscore',
				], RestController::HTTP_BAD_REQUEST);
				return;
			}
		}

		$new_name = $name !== NULL ? $name : $group['name'];
		$new_desc = $description !== NULL ? $description : $group['description'];

		if ($name !== NULL OR $description !== NULL)
		{
			if ( ! $this->ion_auth->update_group($id, $new_name, $new_desc))
			{
				$this->response([
					'status'  => FALSE,
					'message' => strip_tags($this->ion_auth->errors()) ?: 'Gagal update group',
				], RestController::HTTP_INTERNAL_ERROR);
				return;
			}
		}

		if ($bgcolor !== NULL)
		{
			$this->db->where('id', $id)->update('groups', ['bgcolor' => $bgcolor]);
		}

		$this->response([
			'status'  => TRUE,
			'message' => 'Group berhasil diupdate',
			'data'    => $this->ion_auth->group($id)->row_array(),
		], RestController::HTTP_OK);
	}


	/**
	 * DELETE api/groups/{id}
	 */
	public function index_delete($id = NULL)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID group tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$group = $this->ion_auth->group($id)->row_array();
		if ( ! $group)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Group tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		if ($group['name'] === $this->admin_group)
		{
			$this->response([
				'status'  => FALSE,
				'message' => "Group '{$this->admin_group}' tidak boleh dihapus",
			], RestController::HTTP_FORBIDDEN);
			return;
		}

		if ( ! $this->ion_auth->delete_group($id))
		{
			$this->response([
				'status'  => FALSE,
				'message' => strip_tags($this->ion_auth->errors()) ?: 'Gagal menghapus group',
			], RestController::HTTP_INTERNAL_ERROR);
			return;
		}

		$this->response([
			'status'  => TRUE,
			'message' => 'Group berhasil dihapus',
		], RestController::HTTP_OK);
	}
}
