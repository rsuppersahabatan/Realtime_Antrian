<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loket_model extends CI_Model {
    protected $table = 'loket';

    // Ambil daftar semua loket beserta info nama/kode layanannya + daftar user yang ter-assign
    public function get_all() {
        $this->db->select('loket.*, layanan.nama_layanan, layanan.kode_huruf');
        $this->db->from($this->table);
        $this->db->join('layanan', 'layanan.id = loket.id_layanan', 'left');
        $rows = $this->db->get()->result_array();

        if ( ! empty($rows))
        {
            $loket_ids = array_column($rows, 'id');
            $this->db->select('lu.id_loket, u.id, u.username, u.first_name, u.last_name');
            $this->db->from('loket_user lu');
            $this->db->join('users u', 'u.id = lu.id_user', 'inner');
            $this->db->where_in('lu.id_loket', $loket_ids);
            $this->db->order_by('u.username', 'ASC');
            $users = $this->db->get()->result_array();

            $grouped = array();
            foreach ($users as $u)
            {
                $grouped[$u['id_loket']][] = $u;
            }
            foreach ($rows as &$row)
            {
                $row['users'] = isset($grouped[$row['id']]) ? $grouped[$row['id']] : array();
            }
            unset($row);
        }

        return $rows;
    }
    
    // Ambil detail satu loket spesifik (+ daftar user yang ter-assign)
    public function get_by_id($id) {
        $this->db->select('loket.*, layanan.nama_layanan, layanan.kode_huruf');
        $this->db->from($this->table);
        $this->db->join('layanan', 'layanan.id = loket.id_layanan', 'left');
        $this->db->where('loket.id', $id);
        $row = $this->db->get()->row_array();

        if ($row)
        {
            $this->db->select('u.id, u.username, u.first_name, u.last_name, u.email');
            $this->db->from('loket_user lu');
            $this->db->join('users u', 'u.id = lu.id_user', 'inner');
            $this->db->where('lu.id_loket', (int) $id);
            $this->db->order_by('u.username', 'ASC');
            $row['users'] = $this->db->get()->result_array();
        }

        return $row;
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

        $subquery_nomor = '(SELECT a.nomor_antrian FROM antrian a '
            . 'WHERE a.id_loket = loket.id '
            . 'AND a.tanggal = ' . $this->db->escape($tanggal) . ' '
            . 'AND a.waktu_panggil IS NOT NULL '
            . 'ORDER BY a.waktu_panggil DESC LIMIT 1) AS nomor_terakhir';

        $subquery_ket = '(SELECT a.keterangan FROM antrian a '
            . 'WHERE a.id_loket = loket.id '
            . 'AND a.tanggal = ' . $this->db->escape($tanggal) . ' '
            . 'AND a.waktu_panggil IS NOT NULL '
            . 'ORDER BY a.waktu_panggil DESC LIMIT 1) AS keterangan_terakhir';

        $this->db->select('loket.*, layanan.nama_layanan, layanan.kode_huruf, ' . $subquery_nomor . ', ' . $subquery_ket, FALSE);
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

    // Ambil daftar ID user yang di-assign ke loket tertentu
    public function get_user_ids($id_loket) {
        $this->db->select('id_user');
        $this->db->from('loket_user');
        $this->db->where('id_loket', $id_loket);
        $rows = $this->db->get()->result_array();
        return array_map('intval', array_column($rows, 'id_user'));
    }

    // Sinkronkan assignment user untuk sebuah loket (replace all)
    public function sync_users($id_loket, array $user_ids) {
        $user_ids = array_unique(array_filter(array_map('intval', $user_ids)));

        $this->db->trans_start();

        $this->db->where('id_loket', (int) $id_loket)->delete('loket_user');

        if ( ! empty($user_ids))
        {
            $batch = array();
            foreach ($user_ids as $uid)
            {
                $batch[] = array(
                    'id_loket' => (int) $id_loket,
                    'id_user'  => $uid,
                );
            }
            $this->db->insert_batch('loket_user', $batch);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
