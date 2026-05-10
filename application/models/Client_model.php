<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_model extends CI_Model {
    protected $table = 'client';

    // Ambil daftar semua client + daftar loket yang ter-assign
    public function get_all() {
        $this->db->select('*');
        $this->db->from($this->table);
        $rows = $this->db->get()->result_array();

        if ( ! empty($rows))
        {
            $client_ids = array_column($rows, 'id');
            $this->db->select('cl.id_client, l.id, l.nama_loket');
            $this->db->from('client_loket cl');
            $this->db->join('loket l', 'l.id = cl.id_loket', 'inner');
            $this->db->where_in('cl.id_client', $client_ids);
            $this->db->order_by('l.nama_loket', 'ASC');
            $lokets = $this->db->get()->result_array();

            $grouped = array();
            foreach ($lokets as $l)
            {
                $grouped[$l['id_client']][] = $l;
            }
            foreach ($rows as &$row)
            {
                $row['lokets'] = isset($grouped[$row['id']]) ? $grouped[$row['id']] : array();
            }
            unset($row);
        }

        return $rows;
    }
    
    // Ambil detail satu client spesifik (+ daftar loket + setting display)
    public function get_by_id($id) {
        $this->db->where('id', $id);
        $row = $this->db->get($this->table)->row_array();

        if ($row)
        {
            // Daftar loket (1 baris per loket)
            $this->db->select('l.id, l.nama_loket');
            $this->db->from('client_loket cl');
            $this->db->join('loket l', 'l.id = cl.id_loket', 'inner');
            $this->db->where('cl.id_client', (int) $id);
            $this->db->order_by('l.nama_loket', 'ASC');
            $row['lokets'] = $this->db->get()->result_array();

            // Setting display (0 atau 1 baris) — query terpisah agar tidak men-cross-join lokets
            $row['display_settings'] = $this->db->get_where('client_display_settings', array('id_client' => (int) $id))->row_array();
        }

        return $row;
    }

    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    public function get_loket_ids($id_client) {
        $this->db->select('id_loket');
        $this->db->from('client_loket');
        $this->db->where('id_client', $id_client);
        $rows = $this->db->get()->result_array();
        return array_map('intval', array_column($rows, 'id_loket'));
    }

    public function sync_loket($id_client, array $loket_ids) {
        $loket_ids = array_unique(array_filter(array_map('intval', $loket_ids)));

        $this->db->trans_start();
        $this->db->where('id_client', (int) $id_client)->delete('client_loket');

        if ( ! empty($loket_ids))
        {
            $batch = array();
            foreach ($loket_ids as $lid)
            {
                $batch[] = array(
                    'id_client' => (int) $id_client,
                    'id_loket'  => $lid,
                );
            }
            $this->db->insert_batch('client_loket', $batch);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
    
    public function update_status($id, $status) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, ['is_active' => $status]);
    }
}
