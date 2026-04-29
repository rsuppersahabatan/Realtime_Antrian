<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Antrian extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		/* Load :: Common */
		$this->lang->load('admin/antrian');
		$this->load->model('Antrian_model');
		$this->load->model('Layanan_model');
		$this->load->model('Loket_model');

		/* Title Page :: Common */
		$this->page_title->push(lang('menu_antrian'));
		$this->data['pagetitle'] = $this->page_title->show();

		/* Breadcrumbs :: Common */
		$this->breadcrumbs->unshift(1, lang('menu_antrian'), 'admin/antrian');
	}


	/**
	 * Daftar antrian hari ini (default) atau filter tanggal
	 */
	public function index()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}

		/* Breadcrumbs */
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Filter tanggal */
		$tanggal = $this->input->get('tanggal');
		if ( ! $tanggal OR ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal))
		{
			$tanggal = date('Y-m-d');
		}
		$this->data['tanggal'] = $tanggal;

		/* Get antrian */
		$this->data['antrian'] = $this->Antrian_model->get_antrian_hari_ini($tanggal);

		/* Rekap per status */
		$rekap = ['menunggu' => 0, 'dipanggil' => 0, 'selesai' => 0, 'batal' => 0];
		foreach ($this->data['antrian'] as $row)
		{
			if (isset($rekap[$row['status']]))
			{
				$rekap[$row['status']]++;
			}
		}
		$this->data['rekap'] = $rekap;

		/* Load Template */
		$this->template->admin_render('admin/antrian/index', $this->data);
	}


	/**
	 * Tampilkan antrian per tanggal tertentu (alias filter)
	 */
	public function rekap($tanggal = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}

		if ( ! $tanggal OR ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal))
		{
			redirect('admin/antrian', 'refresh');
		}

		redirect('admin/antrian?tanggal='.$tanggal, 'refresh');
	}


	/**
	 * Tambah antrian manual (admin)
	 */
	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_antrian_create'), 'admin/antrian/create');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('id_layanan', 'lang:antrian_layanan', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('nik', 'lang:antrian_nik', 'trim|regex_match[/^(\d{16})?$/]');
		$this->form_validation->set_rules('keterangan', 'lang:antrian_keterangan', 'trim|max_length[1000]');

		if ($this->form_validation->run() == TRUE)
		{
			$id_layanan = (int) $this->input->post('id_layanan');
			$nik        = $this->input->post('nik');
			$keterangan = $this->input->post('keterangan');
			$tiket = $this->Antrian_model->generate_nomor_baru($id_layanan, $nik, $keterangan);

			if ($tiket)
			{
				$this->session->set_flashdata('message', '<div class="alert alert-success">'.sprintf(lang('antrian_created_success'), $tiket['nomor_antrian']).'</div>');
			}
			else
			{
				$this->session->set_flashdata('message', '<div class="alert alert-danger">'.lang('antrian_created_error').'</div>');
			}

			redirect('admin/antrian', 'refresh');
		}
		else
		{
			$this->data['message'] = (validation_errors()
				? '<div class="alert alert-danger">'.validation_errors().'</div>'
				: $this->session->flashdata('message'));

			/* Dropdown layanan */
			$layanan_list = $this->Layanan_model->get_all();
			$this->data['layanan_options'] = array('' => '-- Pilih Layanan --');
			foreach ($layanan_list as $l)
			{
				$this->data['layanan_options'][$l['id']] = $l['kode_huruf'].' - '.$l['nama_layanan'];
			}
			$this->data['selected_layanan']    = $this->form_validation->set_value('id_layanan');
			$this->data['selected_nik']        = $this->form_validation->set_value('nik');
			$this->data['selected_keterangan'] = $this->form_validation->set_value('keterangan');

			/* Load Template */
			$this->template->admin_render('admin/antrian/create', $this->data);
		}
	}


	/**
	 * Update status antrian: selesai / batal
	 */
	public function update_status($id = NULL, $status = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}

		$id     = (int) $id;
		$allowed = ['selesai', 'batal'];

		if ( ! $id OR ! in_array($status, $allowed))
		{
			redirect('admin/antrian', 'refresh');
		}

		$data_update = ['status' => $status];

		if ($status == 'selesai')
		{
			$data_update['waktu_selesai'] = date('Y-m-d H:i:s');
		}

		$this->db->where('id', $id)->update('antrian', $data_update);
		$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('antrian_status_updated').'</div>');

		/* Kembali ke halaman dengan tanggal yang sedang dilihat */
		$tanggal = $this->input->get('tanggal') ?: date('Y-m-d');
		redirect('admin/antrian?tanggal='.$tanggal, 'refresh');
	}


	/**
	 * Reset semua antrian hari ini (batal semua yang masih menunggu)
	 */
	public function reset()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_antrian_reset'), 'admin/antrian/reset');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate CSRF */
		$this->form_validation->set_rules('confirm', 'lang:antrian_confirm', 'required');

		if ($this->form_validation->run() === FALSE)
		{
			$tanggal = date('Y-m-d');
			$this->data['csrf']    = $this->_get_csrf_nonce();
			$this->data['tanggal'] = $tanggal;
			$this->data['count']   = $this->db
				->where('tanggal', $tanggal)
				->where_in('status', ['menunggu', 'dipanggil'])
				->count_all_results('antrian');

			$this->template->admin_render('admin/antrian/reset', $this->data);
		}
		else
		{
			if ($this->input->post('confirm') == 'yes')
			{
				if ($this->_valid_csrf_nonce() === FALSE)
				{
					show_error(lang('error_csrf'));
				}

				$tanggal = date('Y-m-d');
				$this->db
					->where('tanggal', $tanggal)
					->where_in('status', ['menunggu', 'dipanggil'])
					->update('antrian', ['status' => 'batal']);

				$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('antrian_reset_success').'</div>');
			}

			redirect('admin/antrian', 'refresh');
		}
	}


	/**
	 * Hapus 1 record antrian
	 */
	public function delete($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}

		$id = (int) $id;

		if ( ! $id)
		{
			redirect('admin/antrian', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_antrian_delete'), 'admin/antrian/delete/'.$id);
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		$antrian = $this->db
			->select('antrian.*, layanan.kode_huruf, layanan.nama_layanan')
			->from('antrian')
			->join('layanan', 'layanan.id = antrian.id_layanan', 'left')
			->where('antrian.id', $id)
			->get()->row_array();

		if ( ! $antrian)
		{
			$this->session->set_flashdata('message', '<div class="alert alert-warning">'.lang('antrian_not_found').'</div>');
			redirect('admin/antrian', 'refresh');
		}

		/* Validate form input */
		$this->form_validation->set_rules('confirm', 'lang:antrian_confirm', 'required');
		$this->form_validation->set_rules('id', 'ID', 'required|is_natural_no_zero');

		if ($this->form_validation->run() === FALSE)
		{
			$this->data['csrf']    = $this->_get_csrf_nonce();
			$this->data['id']      = $id;
			$this->data['antrian'] = $antrian;

			$this->template->admin_render('admin/antrian/delete', $this->data);
		}
		else
		{
			if ($this->input->post('confirm') == 'yes')
			{
				if ($this->_valid_csrf_nonce() === FALSE OR $id != $this->input->post('id'))
				{
					show_error(lang('error_csrf'));
				}

				$this->db->where('id', $id)->delete('antrian');
				$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('antrian_deleted_success').'</div>');
			}

			redirect('admin/antrian', 'refresh');
		}
	}


	public function _get_csrf_nonce()
	{
		$this->load->helper('string');
		$key   = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);

		return array($key => $value);
	}


	public function _valid_csrf_nonce()
	{
		if ($this->input->post($this->session->flashdata('csrfkey')) !== FALSE
			&& $this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue'))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
}
