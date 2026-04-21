<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Layanan extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		/* Load :: Common */
		$this->lang->load('admin/layanan');
		$this->load->model('Layanan_model');

		/* Title Page :: Common */
		$this->page_title->push(lang('menu_layanan'));
		$this->data['pagetitle'] = $this->page_title->show();

		/* Breadcrumbs :: Common */
		$this->breadcrumbs->unshift(1, lang('menu_layanan'), 'admin/layanan');
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

			/* Get all layanan */
			$this->data['layanan'] = $this->Layanan_model->get_all();

			/* Load Template */
			$this->template->admin_render('admin/layanan/index', $this->data);
		}
	}


	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_layanan_create'), 'admin/layanan/create');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('kode_huruf', 'lang:layanan_kode_huruf', 'required|max_length[5]|is_unique[layanan.kode_huruf]');
		$this->form_validation->set_rules('nama_layanan', 'lang:layanan_nama_layanan', 'required|max_length[100]');
		$this->form_validation->set_rules('keterangan', 'lang:layanan_keterangan', 'trim');

		if ($this->form_validation->run() == TRUE)
		{
			$data = array(
				'kode_huruf'   => strtoupper($this->input->post('kode_huruf')),
				'nama_layanan' => $this->input->post('nama_layanan'),
				'keterangan'   => $this->input->post('keterangan'),
			);

			if ($this->Layanan_model->insert($data))
			{
				$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('layanan_created_success').'</div>');
				redirect('admin/layanan', 'refresh');
			}
			else
			{
				$this->session->set_flashdata('message', '<div class="alert alert-danger">'.lang('layanan_created_error').'</div>');
				redirect('admin/layanan/create', 'refresh');
			}
		}
		else
		{
			$this->data['message'] = (validation_errors()
				? '<div class="alert alert-danger">'.validation_errors().'</div>'
				: $this->session->flashdata('message'));

			$this->data['kode_huruf'] = array(
				'name'      => 'kode_huruf',
				'id'        => 'kode_huruf',
				'type'      => 'text',
				'class'     => 'form-control',
				'maxlength' => '5',
				'value'     => $this->form_validation->set_value('kode_huruf'),
			);
			$this->data['nama_layanan'] = array(
				'name'      => 'nama_layanan',
				'id'        => 'nama_layanan',
				'type'      => 'text',
				'class'     => 'form-control',
				'maxlength' => '100',
				'value'     => $this->form_validation->set_value('nama_layanan'),
			);
			$this->data['keterangan'] = array(
				'name'  => 'keterangan',
				'id'    => 'keterangan',
				'class' => 'form-control',
				'rows'  => '3',
				'value' => $this->form_validation->set_value('keterangan'),
			);

			/* Load Template */
			$this->template->admin_render('admin/layanan/create', $this->data);
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
			redirect('admin/layanan', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_layanan_edit'), 'admin/layanan/edit/'.$id);
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Data */
		$layanan = $this->Layanan_model->get_by_id($id);

		if ( ! $layanan)
		{
			$this->session->set_flashdata('message', '<div class="alert alert-warning">'.lang('layanan_not_found').'</div>');
			redirect('admin/layanan', 'refresh');
		}

		/* Validate form input */
		$this->form_validation->set_rules('kode_huruf', 'lang:layanan_kode_huruf', 'required|max_length[5]');
		$this->form_validation->set_rules('nama_layanan', 'lang:layanan_nama_layanan', 'required|max_length[100]');
		$this->form_validation->set_rules('keterangan', 'lang:layanan_keterangan', 'trim');

		if ($this->form_validation->run() == TRUE)
		{
			$data = array(
				'kode_huruf'   => strtoupper($this->input->post('kode_huruf')),
				'nama_layanan' => $this->input->post('nama_layanan'),
				'keterangan'   => $this->input->post('keterangan'),
			);

			if ($this->Layanan_model->update($id, $data))
			{
				$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('layanan_updated_success').'</div>');
				redirect('admin/layanan', 'refresh');
			}
			else
			{
				$this->session->set_flashdata('message', '<div class="alert alert-danger">'.lang('layanan_updated_error').'</div>');
			}
		}

		$this->data['message'] = (validation_errors()
			? '<div class="alert alert-danger">'.validation_errors().'</div>'
			: $this->session->flashdata('message'));

		$this->data['layanan'] = $layanan;

		$this->data['kode_huruf'] = array(
			'name'      => 'kode_huruf',
			'id'        => 'kode_huruf',
			'type'      => 'text',
			'class'     => 'form-control',
			'maxlength' => '5',
			'value'     => $this->form_validation->set_value('kode_huruf', $layanan['kode_huruf']),
		);
		$this->data['nama_layanan'] = array(
			'name'      => 'nama_layanan',
			'id'        => 'nama_layanan',
			'type'      => 'text',
			'class'     => 'form-control',
			'maxlength' => '100',
			'value'     => $this->form_validation->set_value('nama_layanan', $layanan['nama_layanan']),
		);
		$this->data['keterangan'] = array(
			'name'  => 'keterangan',
			'id'    => 'keterangan',
			'class' => 'form-control',
			'rows'  => '3',
			'value' => $this->form_validation->set_value('keterangan', $layanan['keterangan']),
		);

		/* Load Template */
		$this->template->admin_render('admin/layanan/edit', $this->data);
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
			redirect('admin/layanan', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_layanan_delete'), 'admin/layanan/delete/'.$id);
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		$layanan = $this->Layanan_model->get_by_id($id);

		if ( ! $layanan)
		{
			$this->session->set_flashdata('message', '<div class="alert alert-warning">'.lang('layanan_not_found').'</div>');
			redirect('admin/layanan', 'refresh');
		}

		/* Validate form input */
		$this->form_validation->set_rules('confirm', 'lang:layanan_confirm', 'required');
		$this->form_validation->set_rules('id', 'ID', 'required|is_natural_no_zero');

		if ($this->form_validation->run() === FALSE)
		{
			$this->data['csrf']    = $this->_get_csrf_nonce();
			$this->data['id']      = $id;
			$this->data['layanan'] = $layanan;

			/* Load Template */
			$this->template->admin_render('admin/layanan/delete', $this->data);
		}
		else
		{
			if ($this->input->post('confirm') == 'yes')
			{
				if ($this->_valid_csrf_nonce() === FALSE OR $id != $this->input->post('id'))
				{
					show_error(lang('error_csrf'));
				}

				$this->Layanan_model->delete($id);
				$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('layanan_deleted_success').'</div>');
			}

			redirect('admin/layanan', 'refresh');
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
