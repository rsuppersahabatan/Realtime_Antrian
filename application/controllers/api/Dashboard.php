<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

/**
 * REST API — Dashboard (Statistik Ringkas)
 *
 * Endpoint:
 *   GET  api/dashboard                   -> ringkasan lengkap (count, disk, memory, rekap antrian)
 *                                           ?tanggal=YYYY-MM-DD (default hari ini)
 *   GET  api/dashboard/summary           -> alias ringkas untuk counts (users, groups, loket)
 *   GET  api/dashboard/system            -> pemakaian disk & memory server
 *   GET  api/dashboard/antrian_status    -> rekap antrian per status pada tanggal tertentu
 *   GET  api/dashboard/antrian_loket     -> rekap antrian per loket pada tanggal tertentu
 *   GET  api/dashboard/loket_status      -> jumlah loket per status buka/tutup
 */
class Dashboard extends RestController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin/dashboard_model');
	}


	/**
	 * GET api/dashboard
	 * Query: ?tanggal=YYYY-MM-DD (default hari ini)
	 */
	public function index_get()
	{
		$tanggal = $this->_resolve_tanggal($this->get('tanggal'));

		$this->response([
			'status'  => TRUE,
			'tanggal' => $tanggal,
			'data'    => [
				'counts' => [
					'users'  => (int) $this->dashboard_model->get_count_record('users'),
					'groups' => (int) $this->dashboard_model->get_count_record('groups'),
					'loket'  => (int) $this->dashboard_model->get_count_record('loket'),
				],
				'disk' => [
					'total'       => (float) $this->dashboard_model->disk_totalspace(FCPATH),
					'free'        => (float) $this->dashboard_model->disk_freespace(FCPATH),
					'used'        => (float) $this->dashboard_model->disk_usespace(FCPATH),
					'use_percent' => (float) $this->dashboard_model->disk_usepercent(FCPATH, FALSE),
				],
				'memory' => [
					'usage'       => (int) $this->dashboard_model->memory_usage(),
					'peak'        => (int) $this->dashboard_model->memory_peak_usage(TRUE),
					'use_percent' => (float) $this->dashboard_model->memory_usepercent(TRUE, FALSE),
				],
				'antrian_by_status' => $this->dashboard_model->get_antrian_by_status($tanggal),
				'antrian_by_loket'  => $this->dashboard_model->get_antrian_by_loket($tanggal),
				'loket_by_status'   => $this->dashboard_model->get_loket_by_status(),
			],
		], RestController::HTTP_OK);
	}


	/**
	 * GET api/dashboard/summary
	 */
	public function summary_get()
	{
		$this->response([
			'status' => TRUE,
			'data'   => [
				'users'  => (int) $this->dashboard_model->get_count_record('users'),
				'groups' => (int) $this->dashboard_model->get_count_record('groups'),
				'loket'  => (int) $this->dashboard_model->get_count_record('loket'),
			],
		], RestController::HTTP_OK);
	}


	/**
	 * GET api/dashboard/system
	 */
	public function system_get()
	{
		$this->response([
			'status' => TRUE,
			'data'   => [
				'disk' => [
					'total'       => (float) $this->dashboard_model->disk_totalspace(FCPATH),
					'free'        => (float) $this->dashboard_model->disk_freespace(FCPATH),
					'used'        => (float) $this->dashboard_model->disk_usespace(FCPATH),
					'use_percent' => (float) $this->dashboard_model->disk_usepercent(FCPATH, FALSE),
				],
				'memory' => [
					'usage'       => (int) $this->dashboard_model->memory_usage(),
					'peak'        => (int) $this->dashboard_model->memory_peak_usage(TRUE),
					'use_percent' => (float) $this->dashboard_model->memory_usepercent(TRUE, FALSE),
				],
			],
		], RestController::HTTP_OK);
	}


	/**
	 * GET api/dashboard/antrian_status
	 * Query: ?tanggal=YYYY-MM-DD
	 */
	public function antrian_status_get()
	{
		$tanggal = $this->_resolve_tanggal($this->get('tanggal'));
		$this->response([
			'status'  => TRUE,
			'tanggal' => $tanggal,
			'data'    => $this->dashboard_model->get_antrian_by_status($tanggal),
		], RestController::HTTP_OK);
	}


	/**
	 * GET api/dashboard/antrian_loket
	 * Query: ?tanggal=YYYY-MM-DD
	 */
	public function antrian_loket_get()
	{
		$tanggal = $this->_resolve_tanggal($this->get('tanggal'));
		$this->response([
			'status'  => TRUE,
			'tanggal' => $tanggal,
			'data'    => $this->dashboard_model->get_antrian_by_loket($tanggal),
		], RestController::HTTP_OK);
	}


	/**
	 * GET api/dashboard/loket_status
	 */
	public function loket_status_get()
	{
		$this->response([
			'status' => TRUE,
			'data'   => $this->dashboard_model->get_loket_by_status(),
		], RestController::HTTP_OK);
	}


	// ------------------------------------------------------------------
	// Internal helper
	// ------------------------------------------------------------------

	private function _resolve_tanggal($tanggal)
	{
		if ( ! $tanggal OR ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal))
		{
			return date('Y-m-d');
		}
		return $tanggal;
	}
}
