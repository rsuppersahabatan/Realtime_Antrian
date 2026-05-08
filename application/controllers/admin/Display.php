<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Display extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->lang->load('admin/display');
		$this->load->model('Client_model');

		$this->page_title->push(lang('menu_display_setting'));
		$this->data['pagetitle'] = $this->page_title->show();
		$this->breadcrumbs->unshift(1, lang('menu_display_setting'), 'admin/display');
	}

	public function index()
	{
		$this->_guard_admin();
		$this->data['breadcrumb'] = $this->breadcrumbs->show();

		$this->db->select('cds.*, c.nama_client');
		$this->db->from('client_display_settings cds');
		$this->db->join('client c', 'c.id = cds.id_client', 'left');
		$this->db->order_by('c.nama_client', 'ASC');
		$this->data['display_settings'] = $this->db->get()->result_array();

		$this->template->admin_render('admin/display/index', $this->data);
	}

	public function create()
	{
		$this->_guard_admin();
		$this->breadcrumbs->unshift(2, lang('menu_display_create'), 'admin/display/create');
		$this->data['breadcrumb'] = $this->breadcrumbs->show();
		$this->data['clients'] = $this->Client_model->get_all();

		$this->_set_validation_rules();
		if ($this->form_validation->run() === TRUE)
		{
			$data = $this->_collect_form_data();
			$exists = $this->db->get_where('client_display_settings', array('id_client' => $data['id_client']))->row_array();
			if ($exists)
			{
				$this->session->set_flashdata('message', '<div class="alert alert-warning">'.lang('display_client_already_has_setting').'</div>');
				redirect('admin/display/create', 'refresh');
			}

			$this->db->insert('client_display_settings', $data);
			$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('display_created_success').'</div>');
			redirect('admin/display', 'refresh');
		}

		$this->data['message'] = (validation_errors()
			? '<div class="alert alert-danger">'.validation_errors().'</div>'
			: $this->session->flashdata('message'));

		$defaults = $this->_default_form_data();
		$this->data['selected_client_id'] = (int) $this->form_validation->set_value('id_client', $defaults['id_client']);
		$this->data['color_scheme'] = $this->form_validation->set_value('color_scheme', $defaults['color_scheme']);
		$this->data['video_source'] = $this->form_validation->set_value('video_source', $defaults['video_source']);
		$this->data['video_link'] = $this->form_validation->set_value('video_link', $defaults['video_link']);
		$this->data['footer_text'] = $this->form_validation->set_value('footer_text', $defaults['footer_text']);
		$this->data['footer_mode'] = $this->form_validation->set_value('footer_mode', $defaults['footer_mode']);
		$this->data['font_family'] = $this->form_validation->set_value('font_family', $defaults['font_family']);
		$this->data['font_size'] = (int) $this->form_validation->set_value('font_size', $defaults['font_size']);

		$this->template->admin_render('admin/display/create', $this->data);
	}

	public function edit($id = NULL)
	{
		$this->_guard_admin();
		$id = (int) $id;
		if ( ! $id)
		{
			redirect('admin/display', 'refresh');
		}

		$this->breadcrumbs->unshift(2, lang('menu_display_edit'), 'admin/display/edit/'.$id);
		$this->data['breadcrumb'] = $this->breadcrumbs->show();
		$this->data['clients'] = $this->Client_model->get_all();

		$setting = $this->db->get_where('client_display_settings', array('id' => $id))->row_array();
		if ( ! $setting)
		{
			$this->session->set_flashdata('message', '<div class="alert alert-warning">'.lang('display_not_found').'</div>');
			redirect('admin/display', 'refresh');
		}

		$this->_set_validation_rules();
		if ($this->form_validation->run() === TRUE)
		{
			$data = $this->_collect_form_data();

			$this->db->where('id_client', $data['id_client']);
			$this->db->where('id !=', $id);
			$exists = $this->db->get('client_display_settings')->row_array();
			if ($exists)
			{
				$this->session->set_flashdata('message', '<div class="alert alert-warning">'.lang('display_client_already_has_setting').'</div>');
				redirect('admin/display/edit/'.$id, 'refresh');
			}

			$this->db->where('id', $id)->update('client_display_settings', $data);
			$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('display_updated_success').'</div>');
			redirect('admin/display', 'refresh');
		}

		$this->data['message'] = (validation_errors()
			? '<div class="alert alert-danger">'.validation_errors().'</div>'
			: $this->session->flashdata('message'));

		$this->data['setting'] = $setting;
		$this->data['selected_client_id'] = (int) $this->form_validation->set_value('id_client', $setting['id_client']);
		$this->data['color_scheme'] = $this->form_validation->set_value('color_scheme', $setting['color_scheme']);
		$this->data['video_source'] = $this->form_validation->set_value('video_source', $setting['video_source']);
		$this->data['video_link'] = $this->form_validation->set_value('video_link', $setting['video_link']);
		$this->data['footer_text'] = $this->form_validation->set_value('footer_text', $setting['footer_text']);
		$this->data['footer_mode'] = $this->form_validation->set_value('footer_mode', $setting['footer_mode']);
		$this->data['font_family'] = $this->form_validation->set_value('font_family', $setting['font_family']);
		$this->data['font_size'] = (int) $this->form_validation->set_value('font_size', $setting['font_size']);

		$this->template->admin_render('admin/display/edit', $this->data);
	}

	public function delete($id = NULL)
	{
		$this->_guard_admin();
		$id = (int) $id;
		if ( ! $id)
		{
			redirect('admin/display', 'refresh');
		}

		$this->breadcrumbs->unshift(2, lang('menu_display_delete'), 'admin/display/delete/'.$id);
		$this->data['breadcrumb'] = $this->breadcrumbs->show();
		$this->db->select('cds.*, c.nama_client');
		$this->db->from('client_display_settings cds');
		$this->db->join('client c', 'c.id = cds.id_client', 'left');
		$this->db->where('cds.id', $id);
		$setting = $this->db->get()->row_array();

		if ( ! $setting)
		{
			$this->session->set_flashdata('message', '<div class="alert alert-warning">'.lang('display_not_found').'</div>');
			redirect('admin/display', 'refresh');
		}

		$this->form_validation->set_rules('confirm', 'lang:display_confirm', 'required');
		$this->form_validation->set_rules('id', 'ID', 'required|is_natural_no_zero');

		if ($this->form_validation->run() === FALSE)
		{
			$this->data['csrf'] = $this->_get_csrf_nonce();
			$this->data['id'] = $id;
			$this->data['setting'] = $setting;
			$this->template->admin_render('admin/display/delete', $this->data);
		}
		else
		{
			if ($this->input->post('confirm') == 'yes')
			{
				if ($this->_valid_csrf_nonce() === FALSE OR $id != $this->input->post('id'))
				{
					show_error(lang('error_csrf'));
				}
				$this->db->where('id', $id)->delete('client_display_settings');
				$this->session->set_flashdata('message', '<div class="alert alert-success">'.lang('display_deleted_success').'</div>');
			}
			redirect('admin/display', 'refresh');
		}
	}

	private function _guard_admin()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}
	}

	private function _set_validation_rules()
	{
		$this->form_validation->set_rules('id_client', 'lang:display_id_client', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('color_scheme', 'lang:display_color_scheme', 'required|regex_match[/^#[0-9a-fA-F]{6}$/]');
		$this->form_validation->set_rules('video_source', 'lang:display_video_source', 'required|in_list[youtube,local]');
		$this->form_validation->set_rules('video_link', 'lang:display_video_link', 'trim|max_length[255]');
		$this->form_validation->set_rules('footer_text', 'lang:display_footer_text', 'trim');
		$this->form_validation->set_rules('footer_mode', 'lang:display_footer_mode', 'required|in_list[statis,running]');
		$this->form_validation->set_rules('font_family', 'lang:display_font_family', 'required|max_length[50]');
		$this->form_validation->set_rules('font_size', 'lang:display_font_size', 'required|integer|greater_than_equal_to[60]|less_than_equal_to[160]');
	}

	private function _collect_form_data()
	{
		return array(
			'id_client'    => (int) $this->input->post('id_client'),
			'color_scheme' => $this->input->post('color_scheme', TRUE),
			'video_source' => $this->input->post('video_source', TRUE),
			'video_link'   => $this->input->post('video_link', TRUE),
			'footer_text'  => $this->input->post('footer_text', TRUE),
			'footer_mode'  => $this->input->post('footer_mode', TRUE),
			'font_family'  => $this->input->post('font_family', TRUE),
			'font_size'    => (int) $this->input->post('font_size'),
		);
	}

	private function _default_form_data()
	{
		return array(
			'id_client'    => 0,
			'color_scheme' => '#4e73df',
			'video_source' => 'youtube',
			'video_link'   => '',
			'footer_text'  => '',
			'footer_mode'  => 'statis',
			'font_family'  => 'Poppins',
			'font_size'    => 100,
		);
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

		return FALSE;
	}
}
