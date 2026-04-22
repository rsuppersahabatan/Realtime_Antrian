<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }


    public function get_count_record($table)
    {
        $query = $this->db->count_all($table);

        return $query;
    }


    public function disk_totalspace($dir = FCPATH)
    {
        return @disk_total_space($dir) ?: 0;
    }


    public function disk_freespace($dir = FCPATH)
    {
        return @disk_free_space($dir) ?: 0;
    }


    public function disk_usespace($dir = FCPATH)
    {
        return $this->disk_totalspace($dir) - $this->disk_freespace($dir);
    }


    public function disk_freepercent($dir = FCPATH, $display_unit = FALSE)
    {
        $unit  = ($display_unit === FALSE) ? NULL : ' %';
        $total = $this->disk_totalspace($dir);
        if ($total <= 0)
        {
            return '0'.$unit;
        }

        return round(($this->disk_freespace($dir) * 100) / $total, 0).$unit;
    }


    public function disk_usepercent($dir = FCPATH, $display_unit = FALSE)
    {
        $unit  = ($display_unit === FALSE) ? NULL : ' %';
        $total = $this->disk_totalspace($dir);
        if ($total <= 0)
        {
            return '0'.$unit;
        }

        return round(($this->disk_usespace($dir) * 100) / $total, 0).$unit;
    }


    public function memory_usage()
    {
        return memory_get_usage();
    }


    public function memory_peak_usage($real = TRUE)
    {
        if ($real)
        {
            return memory_get_peak_usage(TRUE);
        }
        else
        {
            return memory_get_peak_usage(FALSE);
        }
    }


    public function memory_usepercent($real = TRUE, $display_unit = FALSE)
    {
        if ($display_unit === FALSE)
        {
            $unit = NULL;
        }
        else
        {
            $unit = ' %';
        }

        return round(($this->memory_usage() * 100) / $this->memory_peak_usage($real), 0).$unit;
    }


    /**
     * Rekap jumlah antrian per status untuk tanggal tertentu (default: hari ini)
     * Selalu mengembalikan semua status walau count = 0
     */
    public function get_antrian_by_status($tanggal = NULL)
    {
        if ($tanggal === NULL)
        {
            $tanggal = date('Y-m-d');
        }

        $statuses = ['menunggu', 'dipanggil', 'selesai', 'batal'];
        $result   = array_fill_keys($statuses, 0);

        $this->db->select('status, COUNT(*) AS total', FALSE);
        $this->db->from('antrian');
        $this->db->where('tanggal', $tanggal);
        $this->db->group_by('status');
        $rows = $this->db->get()->result_array();

        foreach ($rows as $r)
        {
            if (isset($result[$r['status']]))
            {
                $result[$r['status']] = (int) $r['total'];
            }
        }

        return $result;
    }


    /**
     * Rekap jumlah antrian per loket untuk tanggal tertentu (default: hari ini)
     * Mengembalikan seluruh loket walau jumlah antriannya 0
     */
    public function get_antrian_by_loket($tanggal = NULL)
    {
        if ($tanggal === NULL)
        {
            $tanggal = date('Y-m-d');
        }

        $this->db->select('loket.id, loket.nama_loket, loket.status_buka, layanan.kode_huruf');
        $this->db->select('COUNT(antrian.id) AS total_antrian', FALSE);
        $this->db->select('SUM(CASE WHEN antrian.status = "selesai" THEN 1 ELSE 0 END) AS total_selesai', FALSE);
        $this->db->from('loket');
        $this->db->join('layanan', 'layanan.id = loket.id_layanan', 'left');
        $this->db->join(
            'antrian',
            'antrian.id_loket = loket.id AND antrian.tanggal = '.$this->db->escape($tanggal),
            'left'
        );
        $this->db->group_by('loket.id');
        $this->db->order_by('loket.id', 'ASC');

        return $this->db->get()->result_array();
    }


    /**
     * Jumlah loket berdasarkan status buka/tutup
     */
    public function get_loket_by_status()
    {
        $this->db->select('status_buka, COUNT(*) AS total', FALSE);
        $this->db->from('loket');
        $this->db->group_by('status_buka');
        $rows = $this->db->get()->result_array();

        $result = ['buka' => 0, 'tutup' => 0];
        foreach ($rows as $r)
        {
            if (isset($result[$r['status_buka']]))
            {
                $result[$r['status_buka']] = (int) $r['total'];
            }
        }

        return $result;
    }
}
