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
  `nik` VARCHAR(16) NULL COMMENT 'NIK pengunjung pengambil antrian (opsional untuk legacy)',
  `keterangan` TEXT NULL,
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
  INDEX `idx_status` (`status`),
  INDEX `idx_nik` (`nik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Migrasi untuk DB yang sudah terpasang tanpa kolom nik (idempotent via IF NOT EXISTS
-- membutuhkan MariaDB 10.0.2+ / MySQL 8+. Untuk versi lama, jalankan manual).
-- ALTER TABLE `antrian` ADD COLUMN `nik` VARCHAR(16) NULL AFTER `id_layanan`,
--                      ADD INDEX `idx_nik` (`nik`);

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

-- ========================================================
-- TABEL AUTH (Ion Auth) + ADMINLTE
-- Diambil dari CI-AdminLTE (install/sql/ci_adminlte.sql)
-- ========================================================

-- Preferensi panel admin AdminLTE
CREATE TABLE IF NOT EXISTS `admin_preferences` (
  `id` tinyint(1) NOT NULL AUTO_INCREMENT,
  `user_panel` tinyint(1) NOT NULL DEFAULT '0',
  `sidebar_form` tinyint(1) NOT NULL DEFAULT '0',
  `messages_menu` tinyint(1) NOT NULL DEFAULT '0',
  `notifications_menu` tinyint(1) NOT NULL DEFAULT '0',
  `tasks_menu` tinyint(1) NOT NULL DEFAULT '0',
  `user_menu` tinyint(1) NOT NULL DEFAULT '1',
  `ctrl_sidebar` tinyint(1) NOT NULL DEFAULT '0',
  `transition_page` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admin_preferences` (`id`, `user_panel`, `sidebar_form`, `messages_menu`, `notifications_menu`, `tasks_menu`, `user_menu`, `ctrl_sidebar`, `transition_page`) VALUES
(1, 0, 0, 0, 0, 0, 1, 0, 0);

-- Groups (Ion Auth + kolom bgcolor untuk AdminLTE)
CREATE TABLE IF NOT EXISTS `groups` (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL,
  `bgcolor` char(7) NOT NULL DEFAULT '#607D8B',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `groups` (`id`, `name`, `description`, `bgcolor`) VALUES
(1, 'admin', 'Administrator', '#F44336'),
(2, 'members', 'General User', '#2196F3');

-- Ion Auth login attempts
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(15) NOT NULL,
  `login` varchar(100) NOT NULL,
  `time` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ion Auth users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(15) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `activation_code` varchar(40) DEFAULT NULL,
  `forgotten_password_code` varchar(40) DEFAULT NULL,
  `forgotten_password_time` int(11) UNSIGNED DEFAULT NULL,
  `remember_code` varchar(40) DEFAULT NULL,
  `created_on` int(11) UNSIGNED NOT NULL,
  `last_login` int(11) UNSIGNED DEFAULT NULL,
  `active` tinyint(1) UNSIGNED DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin (email: admin@admin.com / password: password)
INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `salt`, `email`, `activation_code`, `forgotten_password_code`, `forgotten_password_time`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES
(1, '127.0.0.1', 'administrator', '$2a$07$SeBknntpZror9uyftVopmu61qg0ms8Qv1yV6FG.kQOSM.9QhmTo36', '', 'admin@admin.com', '', NULL, NULL, NULL, 1268889823, NULL, 1, 'Admin', 'istrator', 'ADMIN', '0');

-- Ion Auth pivot users/groups
CREATE TABLE IF NOT EXISTS `users_groups` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_users_groups` (`user_id`,`group_id`),
  KEY `fk_users_groups_users1_idx` (`user_id`),
  KEY `fk_users_groups_groups1_idx` (`group_id`),
  CONSTRAINT `fk_users_groups_groups1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_groups_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES
(1, 1, 1);

-- --------------------------------------------------------
-- 4. Tabel Loket User (Pengaturan Loket User)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `loket_user` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_loket` INT NOT NULL,
  `id_user` INT(11) UNSIGNED NOT NULL,
  UNIQUE KEY `unique_loket_user` (`id_loket`, `id_user`), -- Mencegah duplikasi user di loket yang sama
  KEY `idx_loket_user_user` (`id_user`),
  CONSTRAINT `fk_loket_user_loket` FOREIGN KEY (`id_loket`) REFERENCES `loket`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loket_user_user` FOREIGN KEY (`id_user`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
