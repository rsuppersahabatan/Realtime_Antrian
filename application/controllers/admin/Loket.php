<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loket extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		/* Load :: Common */
		$this->lang->load('admin/loket');
		$this->load->model('Loket_model');
		$this->load->model('Layanan_model');

		/* Title Page :: Common */
		$this->page_title->push(lang('menu_loket'));
		$this->data['pagetitle'] = $this->page_title->show();

		/* Breadcrumbs :: Common */
		$this->breadcrumbs->unshift(1, lang('menu_loket'), 'admin/loket');
	}


	public function index()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}
		else
		{
			/* Breadcrumbs */
			$this->data['breadcrumb'] = $this->breadcrumbs->show();

			/* Get all loket */
			$this->data['loket'] = $this->Loket_model->get_all();

			/* Load Template */
			$this->template->admin_render('admin/loket/index', $this->data);
		}
	}


	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_loket_create'), 'admin/loket/create');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('id_layanan',  'lang:loket_layanan',     'required|is_natural_no_zero');
		$this->form_validation->set_rules('nama_loket',  'lang:loket_nama_loket',  'required|max_length[50]');
		$this->form_validation->set_rules('status_buka', 'lang:loket_status_buka', 'required|in_list[buka,tutup]');

		if ($this->form_validation->run() == TRUE)
		{
			$data = array(
				'id_layanan'  => $this->input->post('id_layanan'),
				'nama_loket'  => $this->input->post('nama_loket'),
				'status_buka' => $this->input->post('status_buka'),
			);

			if ($this->Loket_model->insert($data))
			{
				$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('loket_created_success').'</div>');
				redirect('admin/loket', 'refresh');
			}
			else
			{
				$this->session->set_flashdata('message', '<div class="alert alert-danger">'.lang('loket_created_error').'</div>');
				redirect('admin/loket/create', 'refresh');
			}
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

			$this->data['selected_layanan'] = $this->form_validation->set_value('id_layanan');

			$this->data['nama_loket'] = array(
				'name'      => 'nama_loket',
				'id'        => 'nama_loket',
				'type'      => 'text',
				'class'     => 'form-control',
				'maxlength' => '50',
				'value'     => $this->form_validation->set_value('nama_loket'),
			);
			$this->data['selected_status'] = $this->form_validation->set_value('status_buka', 'buka');

			/* Load Template */
			$this->template->admin_render('admin/loket/create', $this->data);
		}
	}


	public function edit($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}

		$id = (int) $id;

		if ( ! $id)
		{
			redirect('admin/loket', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_loket_edit'), 'admin/loket/edit/'.$id);
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Data */
		$loket = $this->Loket_model->get_by_id($id);

		if ( ! $loket)
		{
			$this->session->set_flashdata('message', '<div class="alert alert-warning">'.lang('loket_not_found').'</div>');
			redirect('admin/loket', 'refresh');
		}

		/* Validate form input */
		$this->form_validation->set_rules('id_layanan',  'lang:loket_layanan',     'required|is_natural_no_zero');
		$this->form_validation->set_rules('nama_loket',  'lang:loket_nama_loket',  'required|max_length[50]');
		$this->form_validation->set_rules('status_buka', 'lang:loket_status_buka', 'required|in_list[buka,tutup]');

		if ($this->form_validation->run() == TRUE)
		{
			$data = array(
				'id_layanan'  => $this->input->post('id_layanan'),
				'nama_loket'  => $this->input->post('nama_loket'),
				'status_buka' => $this->input->post('status_buka'),
			);

			if ($this->db->where('id', $id)->update('loket', $data))
			{
				$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('loket_updated_success').'</div>');
				redirect('admin/loket', 'refresh');
			}
			else
			{
				$this->session->set_flashdata('message', '<div class="alert alert-danger">'.lang('loket_updated_error').'</div>');
			}
		}

		$this->data['message'] = (validation_errors()
			? '<div class="alert alert-danger">'.validation_errors().'</div>'
			: $this->session->flashdata('message'));

		$this->data['loket'] = $loket;

		/* Dropdown layanan */
		$layanan_list = $this->Layanan_model->get_all();
		$this->data['layanan_options'] = array('' => '-- Pilih Layanan --');
		foreach ($layanan_list as $l)
		{
			$this->data['layanan_options'][$l['id']] = $l['kode_huruf'].' - '.$l['nama_layanan'];
		}

		$this->data['selected_layanan'] = $this->form_validation->set_value('id_layanan', $loket['id_layanan']);

		$this->data['nama_loket'] = array(
			'name'      => 'nama_loket',
			'id'        => 'nama_loket',
			'type'      => 'text',
			'class'     => 'form-control',
			'maxlength' => '50',
			'value'     => $this->form_validation->set_value('nama_loket', $loket['nama_loket']),
		);
		$this->data['selected_status'] = $this->form_validation->set_value('status_buka', $loket['status_buka']);

		/* Load Template */
		$this->template->admin_render('admin/loket/edit', $this->data);
	}


	public function toggle_status($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}

		$id = (int) $id;

		if ( ! $id)
		{
			redirect('admin/loket', 'refresh');
		}

		$loket = $this->Loket_model->get_by_id($id);

		if ($loket)
		{
			$new_status = ($loket['status_buka'] == 'buka') ? 'tutup' : 'buka';
			$this->Loket_model->update_status($id, $new_status);
			$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('loket_status_updated').'</div>');
		}

		redirect('admin/loket', 'refresh');
	}


	public function delete($id = NULL)
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}

		$id = (int) $id;

		if ( ! $id)
		{
			redirect('admin/loket', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_loket_delete'), 'admin/loket/delete/'.$id);
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		$loket = $this->Loket_model->get_by_id($id);

		if ( ! $loket)
		{
			$this->session->set_flashdata('message', '<div class="alert alert-warning">'.lang('loket_not_found').'</div>');
			redirect('admin/loket', 'refresh');
		}

		/* Validate form input */
		$this->form_validation->set_rules('confirm', 'lang:loket_confirm', 'required');
		$this->form_validation->set_rules('id', 'ID', 'required|is_natural_no_zero');

		if ($this->form_validation->run() === FALSE)
		{
			$this->data['csrf']  = $this->_get_csrf_nonce();
			$this->data['id']    = $id;
			$this->data['loket'] = $loket;

			/* Load Template */
			$this->template->admin_render('admin/loket/delete', $this->data);
		}
		else
		{
			if ($this->input->post('confirm') == 'yes')
			{
				if ($this->_valid_csrf_nonce() === FALSE OR $id != $this->input->post('id'))
				{
					show_error(lang('error_csrf'));
				}

				$this->Loket_model->delete($id);
				$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('loket_deleted_success').'</div>');
			}

			redirect('admin/loket', 'refresh');
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
