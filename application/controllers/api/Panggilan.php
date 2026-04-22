<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

/**
 * REST API — Panggilan (Call Antrian + Broadcast Redis)
 *
 * Perbedaan dengan api/Antrian/call: endpoint ini mem-publish channel
 * realtime via Redis agar tampilan display/TV ikut update, sama seperti
 * admin/panggilan.
 *
 * Endpoint:
 *   GET  api/panggilan/loket              -> list loket yang sedang buka
 *   POST api/panggilan/call               -> panggil antrian berikutnya + broadcast
 *                                             Body: id_loket (required)
 *   POST api/panggilan/recall             -> ulangi panggilan (broadcast saja)
 *                                             Body: id_loket (required), nomor (required)
 *   POST api/panggilan/simpan             -> panggil nomor tertentu (manual) + broadcast
 *                                             Body: id_antrian (required), id_loket (required)
 */
class Panggilan extends RestController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(['Layanan_model', 'Loket_model', 'Antrian_model']);
	}


	/**
	 * GET api/panggilan/loket
	 * Daftar loket yang sedang buka (kandidat loket untuk panggilan).
	 */
	public function loket_get()
	{
		$this->response([
			'status' => TRUE,
			'data'   => $this->Loket_model->get_loket_buka(),
		], RestController::HTTP_OK);
	}


	/**
	 * POST api/panggilan/call
	 * Body: id_loket (required)
	 * Panggil antrian paling lama menunggu di loket tersebut + broadcast Redis.
	 */
	public function call_post()
	{
		$id_loket = (int) $this->post('id_loket');

		if ($id_loket <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Field id_loket wajib diisi',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$loket = $this->Loket_model->get_by_id($id_loket);
		if ( ! $loket)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Loket tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		if ($loket['status_buka'] !== 'buka')
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Loket sedang tidak buka',
			], RestController::HTTP_CONFLICT);
			return;
		}

		$nomor = $this->Antrian_model->call_next_antrian($id_loket);
		if ( ! $nomor)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Antrian habis / tidak ada yang menunggu',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$channel = $this->_channel($id_loket);
		$this->_publish($channel, $nomor);

		$this->response([
			'status'        => TRUE,
			'message'       => 'Antrian berhasil dipanggil',
			'data'          => [
				'id_loket'      => $id_loket,
				'nama_loket'    => $loket['nama_loket'],
				'nomor_antrian' => $nomor,
				'channel'       => $channel,
				'waktu_panggil' => date('Y-m-d H:i:s'),
			],
		], RestController::HTTP_OK);
	}


	/**
	 * POST api/panggilan/recall
	 * Body: id_loket (required), nomor (required)
	 * Tidak mengubah data antrian — hanya mem-broadcast ulang via Redis.
	 */
	public function recall_post()
	{
		$id_loket = (int) $this->post('id_loket');
		$nomor    = trim((string) $this->post('nomor'));

		if ($id_loket <= 0 OR $nomor === '')
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Field id_loket dan nomor wajib diisi',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		if ( ! preg_match('/^[A-Za-z0-9]+$/', $nomor))
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Format nomor antrian tidak valid',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$loket = $this->Loket_model->get_by_id($id_loket);
		if ( ! $loket)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Loket tidak ditemukan',
			], RestController::HTTP_NOT_FOUND);
			return;
		}

		$channel = $this->_channel($id_loket);
		$this->_publish($channel, $nomor);

		$this->response([
			'status'  => TRUE,
			'message' => 'Panggilan ulang berhasil disiarkan',
			'data'    => [
				'id_loket'      => $id_loket,
				'nama_loket'    => $loket['nama_loket'],
				'nomor_antrian' => $nomor,
				'channel'       => $channel,
				'waktu_panggil' => date('Y-m-d H:i:s'),
			],
		], RestController::HTTP_OK);
	}


	/**
	 * POST api/panggilan/simpan
	 * Body: id_antrian (required), id_loket (required)
	 * Panggil nomor tertentu (manual / panggil ulang tiket) + broadcast Redis.
	 */
	public function simpan_post()
	{
		$id_antrian = (int) $this->post('id_antrian');
		$id_loket   = (int) $this->post('id_loket');

		if ($id_antrian <= 0 OR $id_loket <= 0)
		{
			$this->response([
				'status'  => FALSE,
				'message' => 'Field id_antrian dan id_loket wajib diisi',
			], RestController::HTTP_BAD_REQUEST);
			return;
		}

		$result = $this->Antrian_model->simpan_panggilan($id_antrian, $id_loket);

		switch ($result['code'])
		{
			case 'antrian_not_found':
				$this->response([
					'status'  => FALSE,
					'message' => 'Antrian tidak ditemukan',
				], RestController::HTTP_NOT_FOUND);
				return;

			case 'loket_not_found':
				$this->response([
					'status'  => FALSE,
					'message' => 'Loket tidak ditemukan',
				], RestController::HTTP_NOT_FOUND);
				return;

			case 'layanan_mismatch':
				$this->response([
					'status'  => FALSE,
					'message' => 'Loket ini tidak melayani layanan antrian tersebut',
				], RestController::HTTP_CONFLICT);
				return;

			case 'status_final':
				$this->response([
					'status'  => FALSE,
					'message' => 'Antrian sudah selesai/batal dan tidak dapat dipanggil',
					'data'    => $result['data'],
				], RestController::HTTP_CONFLICT);
				return;
		}

		$data    = $result['data'];
		$channel = $this->_channel($id_loket);
		$this->_publish($channel, $data['nomor_antrian']);

		$this->response([
			'status'  => TRUE,
			'message' => $data['is_ulang']
				? 'Panggilan ulang berhasil disimpan & disiarkan'
				: 'Panggilan berhasil disimpan & disiarkan',
			'data'    => [
				'id_antrian'    => (int) $data['id'],
				'nomor_antrian' => $data['nomor_antrian'],
				'id_loket'      => (int) $data['id_loket'],
				'nama_loket'    => $data['nama_loket'],
				'waktu_panggil' => $data['waktu_panggil'],
				'is_ulang'      => (bool) $data['is_ulang'],
				'channel'       => $channel,
			],
		], RestController::HTTP_OK);
	}


	// ------------------------------------------------------------------
	// Internal helper
	// ------------------------------------------------------------------

	private function _channel($id_loket)
	{
		return 'loket'.str_pad((int) $id_loket, 2, '0', STR_PAD_LEFT);
	}


	private function _publish($channel, $nomor)
	{
		$this->redis->command('publish realtime '.$channel.'-'.$nomor);
	}
}
