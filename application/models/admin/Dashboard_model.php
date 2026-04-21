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
}
