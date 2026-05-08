<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		/* Load :: Common */
		$this->lang->load('admin/client');
		$this->load->model('Client_model');
		$this->load->model('Loket_model');

		/* Title Page :: Common */
		$this->page_title->push(lang('menu_client'));
		$this->data['pagetitle'] = $this->page_title->show();

		/* Breadcrumbs :: Common */
		$this->breadcrumbs->unshift(1, lang('menu_client'), 'admin/client');
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

			/* Get all client */
			$this->data['client'] = $this->Client_model->get_all();

			/* Load Template */
			$this->template->admin_render('admin/client/index', $this->data);
		}
	}


	public function create()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_client_create'), 'admin/client/create');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Validate form input */
		$this->form_validation->set_rules('nama_client',  'lang:client_nama_client',  'required|max_length[50]');
		$this->form_validation->set_rules('is_active', 'lang:client_is_active', 'required|in_list[ya,tidak]');

		if ($this->form_validation->run() == TRUE)
		{
			$data = array(
				'nama_client' => $this->input->post('nama_client'),
				'is_active'   => $this->input->post('is_active'),
			);

			if ($this->Client_model->insert($data))
			{
				$new_id = $this->db->insert_id();
				$selected_lokets = (array) $this->input->post('id_lokets');
				$this->Client_model->sync_loket($new_id, $selected_lokets);

				$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('client_created_success').'</div>');
				redirect('admin/client', 'refresh');
			}
			else
			{
				$this->session->set_flashdata('message', '<div class="alert alert-danger">'.lang('client_created_error').'</div>');
				redirect('admin/client/create', 'refresh');
			}
		}
		else
		{
			$this->data['message'] = (validation_errors()
				? '<div class="alert alert-danger">'.validation_errors().'</div>'
				: $this->session->flashdata('message'));

			$this->data['nama_client'] = array(
				'name'      => 'nama_client',
				'id'        => 'nama_client',
				'type'      => 'text',
				'class'     => 'form-control',
				'maxlength' => '50',
				'value'     => $this->form_validation->set_value('nama_client'),
			);
			$this->data['selected_status'] = $this->form_validation->set_value('is_active', 'tidak');

			/* Loket list + pre-selected pada re-submit gagal */
			$this->data['loket_list'] = $this->Loket_model->get_all();
			$posted_lokets = (array) $this->input->post('id_lokets');
			$this->data['selected_lokets'] = array_map('intval', $posted_lokets);

			/* Load Template */
			$this->template->admin_render('admin/client/create', $this->data);
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
			redirect('admin/client', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_client_edit'), 'admin/client/edit/'.$id);
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		/* Data */
		$client = $this->Client_model->get_by_id($id);

		if ( ! $client)
		{
			$this->session->set_flashdata('message', '<div class="alert alert-warning">'.lang('client_not_found').'</div>');
			redirect('admin/client', 'refresh');
		}

		/* Validate form input */
		$this->form_validation->set_rules('nama_client',  'lang:client_nama_client',  'required|max_length[50]');
		$this->form_validation->set_rules('is_active', 'lang:client_is_active', 'required|in_list[ya,tidak]');

		if ($this->form_validation->run() == TRUE)
		{
			$data = array(
				'nama_client' => $this->input->post('nama_client'),
				'is_active'   => $this->input->post('is_active'),
			);

			$this->db->where('id', $id)->update('client', $data);

			$selected_lokets = (array) $this->input->post('id_lokets');
			$this->Client_model->sync_loket($id, $selected_lokets);

			$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('client_updated_success').'</div>');
			redirect('admin/client', 'refresh');
		}

		$this->data['message'] = (validation_errors()
			? '<div class="alert alert-danger">'.validation_errors().'</div>'
			: $this->session->flashdata('message'));

		$this->data['client'] = $client;

		$this->data['nama_client'] = array(
			'name'      => 'nama_client',
			'id'        => 'nama_client',
			'type'      => 'text',
			'class'     => 'form-control',
			'maxlength' => '50',
			'value'     => $this->form_validation->set_value('nama_client', $client['nama_client']),
		);
		$this->data['selected_status'] = $this->form_validation->set_value('is_active', $client['is_active']);

		/* Loket list + user yang sudah ter-assign */
		$this->data['loket_list'] = $this->Loket_model->get_all();
		if ($this->input->post('id_lokets') !== NULL)
		{
			$this->data['selected_lokets'] = array_map('intval', (array) $this->input->post('id_lokets'));
		}
		else
		{
			$this->data['selected_lokets'] = $this->Client_model->get_loket_ids($id);
		}

		/* Load Template */
		$this->template->admin_render('admin/client/edit', $this->data);
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
			redirect('admin/client', 'refresh');
		}

		$client = $this->Client_model->get_by_id($id);

		if ($client)
		{
			$new_status = ($client['is_active'] == 'ya') ? 'tidak' : 'ya';
			$this->Client_model->update_status($id, $new_status);
			$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('client_status_updated').'</div>');
		}

		redirect('admin/client', 'refresh');
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
			redirect('admin/client', 'refresh');
		}

		/* Breadcrumbs */
		$this->breadcrumbs->unshift(2, lang('menu_client_delete'), 'admin/client/delete/'.$id);
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		$client = $this->Client_model->get_by_id($id);

		if ( ! $client)
		{
			$this->session->set_flashdata('message', '<div class="alert alert-warning">'.lang('client_not_found').'</div>');
			redirect('admin/client', 'refresh');
		}

		/* Validate form input */
		$this->form_validation->set_rules('confirm', 'lang:client_confirm', 'required');
		$this->form_validation->set_rules('id', 'ID', 'required|is_natural_no_zero');

		if ($this->form_validation->run() === FALSE)
		{
			$this->data['csrf']  = $this->_get_csrf_nonce();
			$this->data['id']    = $id;
			$this->data['client'] = $client;

			/* Load Template */
			$this->template->admin_render('admin/client/delete', $this->data);
		}
		else
		{
			if ($this->input->post('confirm') == 'yes')
			{
				if ($this->_valid_csrf_nonce() === FALSE OR $id != $this->input->post('id'))
				{
					show_error(lang('error_csrf'));
				}

				$this->Client_model->delete($id);
				$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('client_deleted_success').'</div>');
			}

			redirect('admin/client', 'refresh');
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
