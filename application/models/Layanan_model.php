<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Layanan_model extends CI_Model {
    protected $table = 'layanan';

    // Mengambil semua master data layanan (kode poli/bagian)
    public function get_all() {
        return $this->db->get($this->table)->result_array();
    }
    
    // Mengambil layanan berdasarkan ID
    public function get_by_id($id) {
        return $this->db->get_where($this->table, ['id' => $id])->row_array();
    }

    // Menambah layanan baru
    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    // Mengupdate layanan
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    // Menghapus layanan
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
}
