<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loket_model extends CI_Model {
    protected $table = 'loket';

    // Ambil daftar semua loket beserta info nama/kode layanannya
    public function get_all() {
        $this->db->select('loket.*, layanan.nama_layanan, layanan.kode_huruf');
        $this->db->from($this->table);
        $this->db->join('layanan', 'layanan.id = loket.id_layanan', 'left');
        return $this->db->get()->result_array();
    }
    
    // Ambil detail satu loket spesifik
    public function get_by_id($id) {
        $this->db->select('loket.*, layanan.nama_layanan, layanan.kode_huruf');
        $this->db->from($this->table);
        $this->db->join('layanan', 'layanan.id = loket.id_layanan', 'left');
        $this->db->where('loket.id', $id);
        return $this->db->get()->row_array();
    }

    // Ambil semua loket yang buka saja
    public function get_loket_buka() {
        $this->db->select('loket.*, layanan.nama_layanan, layanan.kode_huruf');
        $this->db->from($this->table);
        $this->db->join('layanan', 'layanan.id = loket.id_layanan', 'left');
        $this->db->where('loket.status_buka', 'buka');
        return $this->db->get()->result_array();
    }

    // Ambil loket buka + nomor antrian terakhir yang dipanggil hari ini per loket
    public function get_loket_buka_with_last_nomor($tanggal = null) {
        if ($tanggal === null) {
            $tanggal = date('Y-m-d');
        }

        $subquery = '(SELECT a.nomor_antrian FROM antrian a '
            . 'WHERE a.id_loket = loket.id '
            . 'AND a.tanggal = ' . $this->db->escape($tanggal) . ' '
            . 'AND a.waktu_panggil IS NOT NULL '
            . 'ORDER BY a.waktu_panggil DESC LIMIT 1) AS nomor_terakhir';

        $this->db->select('loket.*, layanan.nama_layanan, layanan.kode_huruf, ' . $subquery, FALSE);
        $this->db->from($this->table);
        $this->db->join('layanan', 'layanan.id = loket.id_layanan', 'left');
        $this->db->where('loket.status_buka', 'buka');
        $this->db->order_by('loket.id', 'ASC');
        return $this->db->get()->result_array();
    }
    
    // Mengubah status buka/tutup loket
    public function update_status($id, $status) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, ['status_buka' => $status]);
    }

    // Insert loket baru
    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    // Menghapus loket
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
}
