-- 
-- SiLLA Supabase (PostgreSQL) Database Schema Initialization
-- 

-- 1. Table users (Menyimpan profil pengguna & password hashed secara lokal)
CREATE TABLE IF NOT EXISTS users (
    uid VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'officer',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (uid),
    CONSTRAINT unique_user_email UNIQUE (email)
);

-- 2. Table counters (Menyimpan data loket pelayanan)
CREATE TABLE IF NOT EXISTS counters (
    id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    current_queue_number VARCHAR(50) DEFAULT NULL,
    current_queue_id VARCHAR(255) DEFAULT NULL,
    assigned_officer_uid VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT unique_counter_name UNIQUE (name)
);

-- 3. Table queues (Menyimpan data nomor antrian pelayanan)
CREATE TABLE IF NOT EXISTS queues (
    id VARCHAR(255) NOT NULL,
    queue_number VARCHAR(50) NOT NULL,
    counter_name VARCHAR(255) DEFAULT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'waiting',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    called_at TIMESTAMP DEFAULT NULL,
    completed_at TIMESTAMP DEFAULT NULL,
    served_by VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id)
);

-- Indexing
CREATE INDEX IF NOT EXISTS idx_queue_created_at ON queues (created_at);
CREATE INDEX IF NOT EXISTS idx_queue_status ON queues (status);

-- 4. Seed Data Default (Admin Account & Loket Awal)
-- Akun Admin Default:
-- Email: admin@silla.com
-- Password: admin123
INSERT INTO users (uid, email, password, display_name, role, created_at) 
VALUES (
    'u_admin', 
    'admin@silla.com', 
    '$2y$10$g1kZq7qQYFj613Z.YpYj/exq1.wQ7aV.g4Xk78xO9bX9uDkIq7cK6', -- Hash untuk 'admin123'
    'Administrator', 
    'admin', 
    CURRENT_TIMESTAMP
) ON CONFLICT (uid) DO NOTHING;

-- Daftar Loket Awal
INSERT INTO counters (id, name, is_active, current_queue_number, current_queue_id, assigned_officer_uid) 
VALUES 
('c_loket1', 'Loket 1', TRUE, NULL, NULL, NULL),
('c_loket2', 'Loket 2', TRUE, NULL, NULL, NULL),
('c_loket3', 'Loket 3', TRUE, NULL, NULL, NULL)
ON CONFLICT (id) DO NOTHING;
