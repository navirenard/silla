-- 
-- SiLLA MySQL Database Schema Initialization
-- 

CREATE DATABASE IF NOT EXISTS `silla_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `silla_db`;

-- 1. Table users (Menyimpan profil pengguna & password hashed secara lokal)
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

-- 2. Table counters (Menyimpan data loket pelayanan)
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

-- 3. Table queues (Menyimpan data nomor antrian pelayanan)
CREATE TABLE IF NOT EXISTS `queues` (
    `id` VARCHAR(255) NOT NULL,
    `queue_number` VARCHAR(50) NOT NULL,
    `counter_name` VARCHAR(255) DEFAULT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'waiting',
    `created_at` DATETIME NOT NULL,
    `called_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `served_by` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_queue_created_at` (`created_at`),
    KEY `idx_queue_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Seed Data Default (Admin Account & Loket Awal)
-- Akun Admin Default:
-- Email: admin@silla.com
-- Password: admin123
INSERT INTO `users` (`uid`, `email`, `password`, `display_name`, `role`, `created_at`) 
VALUES (
    'u_admin', 
    'admin@silla.com', 
    '$2y$10$g1kZq7qQYFj613Z.YpYj/exq1.wQ7aV.g4Xk78xO9bX9uDkIq7cK6', -- Hash untuk 'admin123'
    'Administrator', 
    'admin', 
    NOW()
) ON DUPLICATE KEY UPDATE `uid`=`uid`;

-- Daftar Loket Awal
INSERT INTO `counters` (`id`, `name`, `is_active`, `current_queue_number`, `current_queue_id`, `assigned_officer_uid`) 
VALUES 
('c_loket1', 'Loket 1', 1, NULL, NULL, NULL),
('c_loket2', 'Loket 2', 1, NULL, NULL, NULL),
('c_loket3', 'Loket 3', 1, NULL, NULL, NULL)
ON DUPLICATE KEY UPDATE `id`=`id`;

