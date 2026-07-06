-- 
-- SiLLA MySQL Database Schema Initialization (Puskesmas Salem Edition)
-- 

CREATE DATABASE IF NOT EXISTS `silla_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `silla_db`;

-- 1. Table users (Untuk Admin & Petugas Loket)
CREATE TABLE IF NOT EXISTS `users` (
    `uid` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `display_name` VARCHAR(255) NOT NULL,
    `role` VARCHAR(50) NOT NULL DEFAULT 'officer',
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`uid`),
    UNIQUE KEY `unique_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table polis (Data Poliklinik)
CREATE TABLE IF NOT EXISTS `polis` (
    `kd_poli` VARCHAR(10) NOT NULL,
    `nama_poli` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`kd_poli`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table dokters (Data Dokter)
CREATE TABLE IF NOT EXISTS `dokters` (
    `kd_dokter` VARCHAR(10) NOT NULL,
    `nama_dokter` VARCHAR(100) NOT NULL,
    `kd_poli` VARCHAR(10) NOT NULL,
    PRIMARY KEY (`kd_dokter`),
    FOREIGN KEY (`kd_poli`) REFERENCES `polis` (`kd_poli`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Table jadwal_praktiks (Jadwal Praktik Dokter)
CREATE TABLE IF NOT EXISTS `jadwal_praktiks` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `kd_dokter` VARCHAR(10) NOT NULL,
    `hari` VARCHAR(20) NOT NULL,
    `jam_mulai` TIME NOT NULL,
    `jam_selesai` TIME NOT NULL,
    `kuota` INT NOT NULL DEFAULT 20,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`kd_dokter`) REFERENCES `dokters` (`kd_dokter`) ON DELETE CASCADE,
    UNIQUE KEY `unique_jadwal` (`kd_dokter`, `hari`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 5. Table pasiens (Data Diri Pasien)
CREATE TABLE IF NOT EXISTS `pasiens` (
    `no_rm` VARCHAR(15) NOT NULL,
    `nik` VARCHAR(16) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `tgl_lahir` DATE NOT NULL,
    `jk` VARCHAR(20) NOT NULL,
    `no_telp` VARCHAR(20) NOT NULL,
    `no_bpjs` VARCHAR(20) DEFAULT NULL,
    `alamat` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`no_rm`),
    UNIQUE KEY `unique_nik` (`nik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Table counters (Menyimpan data loket pelayanan)
CREATE TABLE IF NOT EXISTS `counters` (
    `id` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `current_queue_number` VARCHAR(50) DEFAULT NULL,
    `current_queue_id` VARCHAR(255) DEFAULT NULL,
    `assigned_officer_uid` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_counter_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Table queues (Menyimpan data pendaftaran antrian)
CREATE TABLE IF NOT EXISTS `queues` (
    `id` VARCHAR(255) NOT NULL,
    `queue_number` VARCHAR(50) NOT NULL,
    `no_rm` VARCHAR(15) NOT NULL,
    `kd_poli` VARCHAR(10) NOT NULL,
    `kd_dokter` VARCHAR(10) NOT NULL,
    `tanggal` DATE NOT NULL,
    `jenis_pembayaran` VARCHAR(50) NOT NULL,
    `keluhan` TEXT DEFAULT NULL,
    `jam_ambil` TIME NOT NULL,
    `counter_name` VARCHAR(255) DEFAULT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'Menunggu', -- Menunggu / Dipanggil / Selesai / Lewat
    `called_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `served_by` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_queue_tanggal` (`tanggal`),
    KEY `idx_queue_status` (`status`),
    FOREIGN KEY (`no_rm`) REFERENCES `pasiens` (`no_rm`) ON DELETE CASCADE,
    FOREIGN KEY (`kd_poli`) REFERENCES `polis` (`kd_poli`) ON DELETE CASCADE,
    FOREIGN KEY (`kd_dokter`) REFERENCES `dokters` (`kd_dokter`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SEED DATA DEFAULT (Puskesmas Salem)
-- ============================================================

-- 1. Akun Admin Default: admin@silla.com / admin123
INSERT INTO `users` (`uid`, `email`, `password`, `display_name`, `role`, `created_at`) 
VALUES (
    'u_admin', 
    'admin@silla.com', 
    '$2y$10$g1kZq7qQYFj613Z.YpYj/exq1.wQ7aV.g4Xk78xO9bX9uDkIq7cK6', 
    'Administrator', 
    'admin', 
    NOW()
) ON DUPLICATE KEY UPDATE `uid`=`uid`;

-- 2. Daftar Loket Awal
INSERT INTO `counters` (`id`, `name`, `is_active`, `current_queue_number`, `current_queue_id`, `assigned_officer_uid`) 
VALUES 
('c_loket1', 'Loket 1', 1, NULL, NULL, NULL),
('c_loket2', 'Loket 2', 1, NULL, NULL, NULL),
('c_loket3', 'Loket 3', 1, NULL, NULL, NULL)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- 3. Data Poliklinik (Poli)
INSERT INTO `polis` (`kd_poli`, `nama_poli`) VALUES
('P001', 'Poli Umum'),
('P002', 'Poli KIA/KB'),
('P003', 'Poli Gigi'),
('P004', 'Poli Lansia')
ON DUPLICATE KEY UPDATE `kd_poli`=`kd_poli`;

-- 4. Data Dokter
INSERT INTO `dokters` (`kd_dokter`, `nama_dokter`, `kd_poli`) VALUES
('D001', 'dr. Andi Pratama', 'P001'),
('D002', 'dr. Budi Santoso', 'P002'),
('D003', 'drg. Siti Rahayu', 'P003'),
('D004', 'dr. Maya Dewi', 'P004')
ON DUPLICATE KEY UPDATE `kd_dokter`=`kd_dokter`;

-- 5. Jadwal Praktik Dokter
INSERT INTO `jadwal_praktiks` (`kd_dokter`, `hari`, `jam_mulai`, `jam_selesai`, `kuota`) VALUES
('D001', 'Senin', '08:00:00', '12:00:00', 20),
('D001', 'Rabu', '08:00:00', '12:00:00', 20),
('D001', 'Jumat', '08:00:00', '12:00:00', 20),
('D002', 'Senin', '09:00:00', '12:00:00', 18),
('D002', 'Kamis', '09:00:00', '12:00:00', 18),
('D003', 'Selasa', '08:00:00', '12:00:00', 15),
('D003', 'Kamis', '08:00:00', '12:00:00', 15),
('D004', 'Selasa', '08:00:00', '11:00:00', 20),
('D004', 'Sabtu', '08:00:00', '11:00:00', 20)
ON DUPLICATE KEY UPDATE `kd_dokter`=`kd_dokter`, `hari`=`hari`;
