-- ========================================================
-- SKEMA DATABASE: antrian_db
-- ========================================================

CREATE DATABASE IF NOT EXISTS antrian_db;
USE antrian_db;

-- --------------------------------------------------------
-- 1. Tabel Layanan (Kategori Antrian)
-- Digunakan jika antrian memiliki beberapa jenis awalan huruf.
-- Contoh: A = Pendaftaran, B = Kasir, C = Apotek
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `layanan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_huruf` VARCHAR(5) NOT NULL UNIQUE COMMENT 'Misal: A, B, CS',
  `nama_layanan` VARCHAR(100) NOT NULL COMMENT 'Misal: Poli Umum, Kasir',
  `keterangan` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Tabel Loket (Meja Petugas)
-- Menyimpan informasi meja/loket panggilan.
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `loket` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_layanan` INT NOT NULL COMMENT 'Loket ini melayani layanan apa?',
  `nama_loket` VARCHAR(50) NOT NULL COMMENT 'Misal: Loket 01, Kasir 01',
  `status_buka` ENUM('buka', 'tutup') DEFAULT 'tutup',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_layanan`) REFERENCES `layanan`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. Tabel Antrian (Transaksi Antrian Harian)
-- Tempat menyimpan semua nomor antrian yang diambil pengunjung.
-- Agar reset nomor tiap hari mudah, kita berikan kolom tanggal harian.
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `antrian` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `tanggal` DATE NOT NULL COMMENT 'Tanggal antrian terjadi (untuk reset harian)',
  `id_layanan` INT NOT NULL,
  `nomor_antrian` VARCHAR(20) NOT NULL COMMENT 'Nomor urut gabungan (Misal: A12)',
  `nomor_urut` INT NOT NULL COMMENT 'Angka murninya saja (Misal: 12)',
  `status` ENUM('menunggu', 'dipanggil', 'selesai', 'batal') DEFAULT 'menunggu',
  `id_loket` INT NULL COMMENT 'Loket mana yang memanggil antrian ini',
  `waktu_ambil` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `waktu_panggil` DATETIME NULL,
  `waktu_selesai` DATETIME NULL,
  FOREIGN KEY (`id_layanan`) REFERENCES `layanan`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_loket`) REFERENCES `loket`(`id`) ON DELETE SET NULL,
  INDEX `idx_tanggal` (`tanggal`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- DATA DUMMY AWAL (Seeder)
-- ========================================================

-- Insert Layanan
INSERT INTO `layanan` (`kode_huruf`, `nama_layanan`) VALUES 
('A', 'Loket Pendaftaran'),
('B', 'Kasir Rawat Jalan'),
('C', 'Pengambilan Obat');

-- Insert Loket
INSERT INTO `loket` (`id_layanan`, `nama_loket`, `status_buka`) VALUES 
(1, 'Loket 01', 'buka'),
(1, 'Loket 02', 'buka'),
(2, 'Kasir 01', 'buka');

-- Insert Contoh Antrian Sedang Berjalan (Hari Ini)
INSERT INTO `antrian` (`tanggal`, `id_layanan`, `nomor_antrian`, `nomor_urut`, `status`, `id_loket`, `waktu_ambil`, `waktu_panggil`) VALUES 
(CURDATE(), 1, 'A1', 1, 'selesai', 1, DATE_SUB(NOW(), INTERVAL 30 MINUTE), DATE_SUB(NOW(), INTERVAL 25 MINUTE)),
(CURDATE(), 1, 'A2', 2, 'dipanggil', 2, DATE_SUB(NOW(), INTERVAL 20 MINUTE), NOW()),
(CURDATE(), 1, 'A3', 3, 'menunggu', NULL, DATE_SUB(NOW(), INTERVAL 5 MINUTE), NULL),
(CURDATE(), 2, 'B1', 1, 'menunggu', NULL, DATE_SUB(NOW(), INTERVAL 2 MINUTE), NULL);
