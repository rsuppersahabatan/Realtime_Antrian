<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

/**
 * REST API — Users (User Management via ion_auth)
 *
 * Endpoint:
 *   GET    api/users                    -> list semua user + groups
 *   GET    api/users/{id}               -> detail user + groups
 *   POST   api/users                    -> tambah user baru
 *                                          Body: email (required), password (required),
 *                                                first_name, last_name, phone, company,
 *                                                username (opsional, default = first+last),
 *                                                groups[] (opsional, array id group)
 *   PUT    api/users/{id}               -> update user (partial)
 *                                          Body: first_name, last_name, phone, company,
 *                                                password (opsional), groups[] (opsional)
 *   PUT    api/users/activate/{id}      -> aktifkan user
 *   PUT    api/users/deactivate/{id}    -> nonaktifkan user
 *   DELETE api/users/{id}               -> hapus user
 */
class Users extends RestController {

	public function __construct()
	{
		parent::__construct();
		// ion_auth sudah di-autoload dari config/autoload.php
	}


	/**
	 * GET api/users       — list semua user (dengan daftar groups)
	 * GET api/users/{id}  — detail satu user
	 */
	public function index_get($id = NULL)
	{
		if ($id === NULL)
		{
			$users = $this->ion_auth->users()->result_array();
			foreach ($users as $k => $u)
			{
				unset($users[$k]['password'], $users[$k]['salt'],
					$users[$k]['remember_code'], $users[$k]['forgotten_password_code'],
					$users[$k]['activation_code']);
				$users[$k]['groups'] = $this->ion_auth->get_users_groups($u['id'])->result_array();
			}

			$this->response([
				'status' => TRUE,
				'data'   => $users,
			], RestController::HTTP_OK);
			return;
		}

		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID user tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$user = $this->ion_auth->user($id)->row_array();
		if ( ! $user)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'User tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		unset($user['password'], $user['salt'], $user['remember_code'],
			$user['forgotten_password_code'], $user['activation_code']);
		$user['groups'] = $this->ion_auth->get_users_groups($id)->result_array();

		$this->response([
			'status' => TRUE,
			'data'   => $user,
		], RestController::HTTP_OK);
	}


	/**
	 * POST api/users
	 * Body: email, password (required)
	 *       first_name, last_name, phone, company (opsional)
	 *       username (opsional), groups (opsional, array id group)
	 */
	public function index_post()
	{
		$email    = trim((string) $this->post('email'));
		$password = (string) $this->post('password');

		if ($email === '' OR $password === '')
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Field email dan password wajib diisi',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Format email tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$min = (int) $this->config->item('min_password_length', 'ion_auth');
		if ($min > 0 && strlen($password) < $min)
		{
			$this->response([
				'status'  => FALSE,
				'message' => "Password minimal $min karakter",
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$first_name = trim((string) $this->post('first_name'));
		$last_name  = trim((string) $this->post('last_name'));
		$phone      = $this->post('phone');
		$company    = $this->post('company');
		$username   = trim((string) $this->post('username'));

		if ($username === '')
		{
			$username = strtolower(trim($first_name . ' ' . $last_name));
			if ($username === '')
			{
				$username = strtolower(strstr($email, '@', TRUE));
			}
		}

		$additional_data = [
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'phone'      => $phone,
			'company'    => $company,
		];

		$groups = $this->post('groups');
		$group_ids = [];
		if (is_array($groups))
		{
			foreach ($groups as $gid)
			{
				$gid = (int) $gid;
				if ($gid > 0) $group_ids[] = $gid;
			}
		}

		$new_user_id = $this->ion_auth->register($username, $password, $email, $additional_data, $group_ids);

		if ( ! $new_user_id)
		{
			$this->response([
				'status'  => FALSE,
				'message' => strip_tags($this->ion_auth->errors()) ?: 'Gagal menambah user',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$user = $this->ion_auth->user($new_user_id)->row_array();
		unset($user['password'], $user['salt'], $user['remember_code'],
			$user['forgotten_password_code'], $user['activation_code']);
		$user['groups'] = $this->ion_auth->get_users_groups($new_user_id)->result_array();

		$this->response([
			'status'  => TRUE,
			'message' => 'User berhasil ditambahkan',
			'data'    => $user,
		], RestController::HTTP_CREATED);
	}


	/**
	 * PUT api/users/{id}
	 * Body: first_name, last_name, phone, company, password (opsional),
	 *       groups[] (opsional — replace all)
	 */
	public function index_put($id = NULL)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID user tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! $this->ion_auth->user($id)->row())
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'User tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$allowed = ['first_name', 'last_name', 'phone', 'company'];
		$data    = [];
		foreach ($allowed as $field)
		{
			$val = $this->put($field);
			if ($val !== NULL)
			{
				$data[$field] = $val;
			}
		}

		$password = $this->put('password');
		if ($password !== NULL && $password !== '')
		{
			$min = (int) $this->config->item('min_password_length', 'ion_auth');
			if ($min > 0 && strlen($password) < $min)
			{
				$this->response([
					'status'  => FALSE,
					'message' => "Password minimal $min karakter",
				], RestController::HTTP_BAD_REQUEST);
				return;
			}
			$data['password'] = $password;
		}

		$groups = $this->put('groups');
		$update_groups = is_array($groups);

		if (empty($data) && ! $update_groups)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Tidak ada field yang diupdate',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! empty($data) && ! $this->ion_auth->update($id, $data))
		{
			$this->response([
				'status'  => FALSE,
				'message' => strip_tags($this->ion_auth->errors()) ?: 'Gagal update user',
			], RestController::HTTP_INTERNAL_ERROR);
			return;
		}

		if ($update_groups)
		{
			$this->ion_auth->remove_from_group(NULL, $id);
			foreach ($groups as $gid)
			{
				$gid = (int) $gid;
				if ($gid > 0)
				{
					$this->ion_auth->add_to_group($gid, $id);
				}
			}
		}

		$user = $this->ion_auth->user($id)->row_array();
		unset($user['password'], $user['salt'], $user['remember_code'],
			$user['forgotten_password_code'], $user['activation_code']);
		$user['groups'] = $this->ion_auth->get_users_groups($id)->result_array();

		$this->response([
			'status'  => TRUE,
			'message' => 'User berhasil diupdate',
			'data'    => $user,
		], RestController::HTTP_OK);
	}


	/**
	 * PUT api/users/activate/{id}
	 */
	public function activate_put($id = NULL)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID user tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! $this->ion_auth->user($id)->row())
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'User tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		if ( ! $this->ion_auth->activate($id))
		{
			$this->response([
				'status'  => FALSE,
				'message' => strip_tags($this->ion_auth->errors()) ?: 'Gagal mengaktifkan user',
			], RestController::HTTP_INTERNAL_ERROR);
			return;
		}

		$this->response([
			'status'  => TRUE,
			'message' => 'User berhasil diaktifkan',
		], RestController::HTTP_OK);
	}


	/**
	 * PUT api/users/deactivate/{id}
	 */
	public function deactivate_put($id = NULL)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID user tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! $this->ion_auth->user($id)->row())
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'User tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		if ( ! $this->ion_auth->deactivate($id))
		{
			$this->response([
				'status'  => FALSE,
				'message' => strip_tags($this->ion_auth->errors()) ?: 'Gagal menonaktifkan user',
			], RestController::HTTP_INTERNAL_ERROR);
			return;
		}

		$this->response([
			'status'  => TRUE,
			'message' => 'User berhasil dinonaktifkan',
		], RestController::HTTP_OK);
	}


	/**
	 * DELETE api/users/{id}
	 */
	public function index_delete($id = NULL)
	{
		$id = (int) $id;
		if ($id <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'ID user tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! $this->ion_auth->user($id)->row())
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'User tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		if ( ! $this->ion_auth->delete_user($id))
		{
			$this->response([
				'status'  => FALSE,
				'message' => strip_tags($this->ion_auth->errors()) ?: 'Gagal menghapus user',
			], RestController::HTTP_INTERNAL_ERROR);
			return;
		}

		$this->response([
			'status'  => TRUE,
			'message' => 'User berhasil dihapus',
		], RestController::HTTP_OK);
	}
}
