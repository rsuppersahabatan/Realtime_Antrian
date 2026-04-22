<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Antrian_model extends CI_Model {
    protected $table = 'antrian';

    // Ambil daftar seluruh rekap antrian hari ini
    public function get_antrian_hari_ini($tanggal = null) {
        if ($tanggal == null) {
            $tanggal = date('Y-m-d');
        }
        
        $this->db->select('antrian.*, layanan.kode_huruf, layanan.nama_layanan, loket.nama_loket');
        $this->db->from($this->table);
        $this->db->join('layanan', 'layanan.id = antrian.id_layanan', 'left');
        $this->db->join('loket', 'loket.id = antrian.id_loket', 'left');
        $this->db->where('antrian.tanggal', $tanggal);
        $this->db->order_by('antrian.waktu_ambil', 'ASC');
        
        return $this->db->get()->result_array();
    }
    
    /**
     * GENERATE NOMOR ANTRIAN BARU UNTUK PENGUNJUNG
     * Otomatis memberikan nomor urut lanjutan (misal: A10 -> A11)
     *
     * @param int    $id_layanan
     * @param string $nik  NIK pengunjung (opsional — kolom nullable di DB).
     */
    public function generate_nomor_baru($id_layanan, $nik = null) {
        $tanggal = date('Y-m-d');

        // 1. Ambil data layanan (untuk dapat prefix hurufnya, ex: "A")
        $this->db->where('id', $id_layanan);
        $layanan = $this->db->get('layanan')->row_array();

        if(!$layanan) return null; // Jika layanan tidak ada

        // 2. Cari nomor_urut TERTINGGI khusus pada hari ini & layanan ini
        $this->db->where('id_layanan', $id_layanan);
        $this->db->where('tanggal', $tanggal);
        $this->db->order_by('nomor_urut', 'DESC');
        $this->db->limit(1);
        $antrian_terakhir = $this->db->get($this->table)->row_array();

        // 3. Tentukan nomor baru (jika balum ada antrian, mulai dari 1)
        $nomor_urut_baru = ($antrian_terakhir) ? (int)$antrian_terakhir['nomor_urut'] + 1 : 1;
        $nomor_antrian_gabungan = $layanan['kode_huruf'] . $nomor_urut_baru; // Jadinya: "A1"

        // 4. Masukkan tiket baru ke Database
        $data_insert = [
            'tanggal'       => $tanggal,
            'id_layanan'    => $id_layanan,
            'nik'           => ($nik !== null && $nik !== '') ? $nik : null,
            'nomor_antrian' => $nomor_antrian_gabungan,
            'nomor_urut'    => $nomor_urut_baru,
            'status'        => 'menunggu',
            'waktu_ambil'   => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->table, $data_insert);
        $data_insert['id'] = $this->db->insert_id();

        // Mengembalikan objek tiket antrian yang sukses dicetak
        return $data_insert;
    }
    
    /**
     * PANGGIL ANTRIAN (Sisi Petugas/Loket)
     * Mengubah tiket yang 'menunggu' menjadi 'dipanggil'
     */
    public function call_next_antrian($id_loket) {
        // Ambil info loket (untuk tahu dia memanggil antrian kategori layanan apa)
        $loket = $this->db->get_where('loket', ['id' => $id_loket])->row_array();
        if(!$loket) return false;
        
        $tanggal = date('Y-m-d');
        
        // Cari 1 antrian yang paling lama 'menunggu' untuk id_layanan tersebut hari ini
        $this->db->where('id_layanan', $loket['id_layanan']);
        $this->db->where('tanggal', $tanggal);
        $this->db->where('status', 'menunggu');
        $this->db->order_by('nomor_urut', 'ASC'); // Yang urutannya terkecil dipanggil duluan
        $this->db->limit(1);
        $tiket_menunggu = $this->db->get($this->table)->row_array();
        
        if ($tiket_menunggu) {
            // Update tiket tersebut
            $this->db->where('id', $tiket_menunggu['id']);
            $this->db->update($this->table, [
                'status' => 'dipanggil',
                'id_loket' => $id_loket,
                'waktu_panggil' => date('Y-m-d H:i:s')
            ]);
            
            // Kembalikan nomor tiketnya (misal "A12") agar bisa dipublish via Socket/Redis
            return $tiket_menunggu['nomor_antrian'];
        }
        
        // Return null jika antrian sudah habis / tidak ada yang menunggu
        return null; 
    }
    
    /**
     * SIMPAN PANGGILAN (Panggil manual / panggil ulang)
     * Mengubah tiket menjadi 'dipanggil' pada loket tertentu.
     *
     * @param int $id_antrian
     * @param int $id_loket
     * @return array ['code'=>string, 'data'=>array|null]
     *               code: 'ok' | 'antrian_not_found' | 'loket_not_found'
     *                   | 'layanan_mismatch' | 'status_final'
     */
    public function simpan_panggilan($id_antrian, $id_loket) {
        $antrian = $this->db->get_where($this->table, ['id' => $id_antrian])->row_array();
        if ( ! $antrian) {
            return ['code' => 'antrian_not_found', 'data' => null];
        }

        $loket = $this->db->get_where('loket', ['id' => $id_loket])->row_array();
        if ( ! $loket) {
            return ['code' => 'loket_not_found', 'data' => null];
        }

        if ((int) $antrian['id_layanan'] !== (int) $loket['id_layanan']) {
            return ['code' => 'layanan_mismatch', 'data' => null];
        }

        if (in_array($antrian['status'], ['selesai', 'batal'], true)) {
            return ['code' => 'status_final', 'data' => $antrian];
        }

        $waktu_panggil = date('Y-m-d H:i:s');
        $update = [
            'status'        => 'dipanggil',
            'id_loket'      => $id_loket,
            'waktu_panggil' => $waktu_panggil,
        ];

        $this->db->where('id', $id_antrian)->update($this->table, $update);

        return [
            'code' => 'ok',
            'data' => array_merge($antrian, $update, [
                'nama_loket'   => $loket['nama_loket'],
                'is_ulang'     => $antrian['status'] === 'dipanggil',
            ]),
        ];
    }

    // Selesaikan tiket antrian
    public function selesaikan_antrian($id_antrian) {
        $this->db->where('id', $id_antrian);
        return $this->db->update($this->table, [
            'status' => 'selesai',
            'waktu_selesai' => date('Y-m-d H:i:s')
        ]);
    }
}
