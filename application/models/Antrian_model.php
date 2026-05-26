<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Antrian_model extends CI_Model {
    protected $table = 'antrian';

    /**
     * Ambil antrian berstatus 'menunggu' hari ini untuk daftar layanan tertentu,
     * di-group per id_layanan. Dipakai oleh display untuk menampilkan kolom
     * antrian yang sedang menunggu di setiap loket/layanan.
     */
    public function get_waiting_by_layanan_ids(array $layanan_ids, $tanggal = null) {
        $layanan_ids = array_values(array_unique(array_filter(array_map('intval', $layanan_ids))));
        if (empty($layanan_ids)) {
            return array();
        }
        if ($tanggal === null) {
            $tanggal = date('Y-m-d');
        }

        $this->db->select('id, id_layanan, nomor_antrian, nomor_urut, keterangan');
        $this->db->from($this->table);
        $this->db->where('tanggal', $tanggal);
        $this->db->where('status', 'menunggu');
        $this->db->where_in('id_layanan', $layanan_ids);
        $this->db->order_by('id_layanan', 'ASC');
        $this->db->order_by('nomor_urut', 'ASC');
        $rows = $this->db->get()->result_array();

        $grouped = array();
        foreach ($rows as $row) {
            $grouped[(int) $row['id_layanan']][] = $row;
        }
        return $grouped;
    }

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
     * Otomatis memberikan nomor urut lanjutan (misal: A10 -> A11),
     * atau memakai nomor tiket manual bila $nomor_antrian_manual diisi.
     *
     * @param int    $id_layanan
     * @param string $nik                  NIK pengunjung (opsional — kolom nullable di DB).
     * @param string $keterangan           Keterangan tambahan (opsional — kolom nullable di DB).
     * @param string $nomor_antrian_manual Tiket manual (opsional). Jika diisi, dipakai apa adanya;
     *                                     digit di dalamnya dipakai sebagai nomor_urut.
     */
    public function generate_nomor_baru($id_layanan, $nik = null, $keterangan = null, $nomor_antrian_manual = null) {
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

        // 3b. Jika tiket manual diberikan, override nomor antrian.
        //     Ambil digit di dalamnya sebagai nomor_urut agar urutan panggilan tetap konsisten.
        if ($nomor_antrian_manual !== null && $nomor_antrian_manual !== '') {
            $nomor_antrian_gabungan = (string) $nomor_antrian_manual;
            if (preg_match('/(\d+)/', $nomor_antrian_gabungan, $m)) {
                $nomor_urut_baru = (int) $m[1];
            }
        }

        // 4. Masukkan tiket baru ke Database
        $data_insert = [
            'tanggal'       => $tanggal,
            'id_layanan'    => $id_layanan,
            'nik'           => ($nik !== null && $nik !== '') ? $nik : null,
            'keterangan'    => ($keterangan !== null && $keterangan !== '') ? $keterangan : null,
            'nomor_antrian' => $nomor_antrian_gabungan,
            'nomor_urut'    => $nomor_urut_baru,
            'status'        => 'menunggu',
            'waktu_ambil'   => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->table, $data_insert);
        $data_insert['id'] = $this->db->insert_id();

        // Broadcast realtime antrian baru
        $CI =& get_instance();
        if (isset($CI->redis)) {
            $payload = 'antrian-baru-' . $id_layanan . '-' . $nomor_antrian_gabungan;
            if ($data_insert['keterangan'] !== null && $data_insert['keterangan'] !== '') {
                $ket = str_replace(['|', "\r", "\n"], ' ', $data_insert['keterangan']);
                $ket = preg_replace('/\s+/', ' ', $ket);
                $payload .= '|' . trim($ket);
            }
            try {
                $CI->redis->publish('realtime', $payload);
            } catch (Exception $e) {
                log_message('error', 'Redis publish failed: ' . $e->getMessage());
            }
        }

        // Mengembalikan objek tiket antrian yang sukses dicetak
        return $data_insert;
    }
    
    /**
     * PANGGIL ANTRIAN (Sisi Petugas/Loket)
     * Mengubah tiket yang 'menunggu' menjadi 'dipanggil'.
     *
     * @return array|null  Tiket lengkap (termasuk nomor_antrian & keterangan) atau null bila kosong/loket invalid.
     */
    public function call_next_antrian($id_loket) {
        // Ambil info loket (untuk tahu dia memanggil antrian kategori layanan apa)
        $loket = $this->db->get_where('loket', ['id' => $id_loket])->row_array();
        if(!$loket) return null;

        $tanggal = date('Y-m-d');

        // Cari 1 antrian yang paling lama 'menunggu' untuk id_layanan tersebut hari ini
        $this->db->where('id_layanan', $loket['id_layanan']);
        $this->db->where('tanggal', $tanggal);
        $this->db->where('status', 'menunggu');
        $this->db->order_by('nomor_urut', 'ASC'); // Yang urutannya terkecil dipanggil duluan
        $this->db->limit(1);
        $tiket_menunggu = $this->db->get($this->table)->row_array();

        if ($tiket_menunggu) {
            $waktu_panggil = date('Y-m-d H:i:s');
            $this->db->where('id', $tiket_menunggu['id']);
            $this->db->update($this->table, [
                'status' => 'dipanggil',
                'id_loket' => $id_loket,
                'waktu_panggil' => $waktu_panggil
            ]);

            // Kembalikan tiket lengkap supaya controller bisa pakai keterangan, dsb.
            return array_merge($tiket_menunggu, [
                'status'        => 'dipanggil',
                'id_loket'      => (int) $id_loket,
                'waktu_panggil' => $waktu_panggil,
            ]);
        }

        // Return null jika antrian sudah habis / tidak ada yang menunggu
        return null;
    }

    /**
     * Ambil tiket berdasarkan nomor_antrian + tanggal hari ini (untuk panggil ulang).
     * Optional: filter id_loket untuk memastikan tiket memang milik loket tsb.
     */
    public function get_by_nomor_today($nomor_antrian, $id_loket = null) {
        $this->db->where('nomor_antrian', $nomor_antrian);
        $this->db->where('tanggal', date('Y-m-d'));
        if ($id_loket !== null) {
            $this->db->where('id_loket', (int) $id_loket);
        }
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        return $this->db->get($this->table)->row_array();
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
