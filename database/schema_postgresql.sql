-- 
-- SiLLA Supabase (PostgreSQL) Database Schema Initialization (Puskesmas Salem Edition)
-- 

-- 1. Table users (Untuk Admin & Petugas Loket)
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

-- 2. Table polis (Data Poliklinik)
CREATE TABLE IF NOT EXISTS polis (
    kd_poli VARCHAR(10) NOT NULL,
    nama_poli VARCHAR(100) NOT NULL,
    PRIMARY KEY (kd_poli)
);

-- 3. Table dokters (Data Dokter)
CREATE TABLE IF NOT EXISTS dokters (
    kd_dokter VARCHAR(10) NOT NULL,
    nama_dokter VARCHAR(100) NOT NULL,
    kd_poli VARCHAR(10) NOT NULL,
    PRIMARY KEY (kd_dokter),
    FOREIGN KEY (kd_poli) REFERENCES polis (kd_poli) ON DELETE CASCADE
);

-- 4. Table jadwal_praktiks (Jadwal Praktik Dokter)
CREATE TABLE IF NOT EXISTS jadwal_praktiks (
    id SERIAL PRIMARY KEY,
    kd_dokter VARCHAR(10) NOT NULL,
    hari VARCHAR(20) NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    kuota INT NOT NULL DEFAULT 20,
    FOREIGN KEY (kd_dokter) REFERENCES dokters (kd_dokter) ON DELETE CASCADE,
    CONSTRAINT unique_jadwal UNIQUE (kd_dokter, hari)
);


-- 5. Table pasiens (Data Diri Pasien)
CREATE TABLE IF NOT EXISTS pasiens (
    no_rm VARCHAR(15) NOT NULL,
    nik VARCHAR(16) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    tgl_lahir DATE NOT NULL,
    jk VARCHAR(20) NOT NULL,
    no_telp VARCHAR(20) NOT NULL,
    no_bpjs VARCHAR(20) DEFAULT NULL,
    alamat TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (no_rm),
    CONSTRAINT unique_nik UNIQUE (nik)
);

-- 6. Table counters (Menyimpan data loket pelayanan)
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

-- 7. Table queues (Menyimpan data pendaftaran antrian)
CREATE TABLE IF NOT EXISTS queues (
    id VARCHAR(255) NOT NULL,
    queue_number VARCHAR(50) NOT NULL,
    no_rm VARCHAR(15) NOT NULL,
    kd_poli VARCHAR(10) NOT NULL,
    kd_dokter VARCHAR(10) NOT NULL,
    tanggal DATE NOT NULL,
    jenis_pembayaran VARCHAR(50) NOT NULL,
    keluhan TEXT DEFAULT NULL,
    jam_ambil TIME NOT NULL,
    counter_name VARCHAR(255) DEFAULT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Menunggu', -- Menunggu / Dipanggil / Selesai / Lewat
    called_at TIMESTAMP DEFAULT NULL,
    completed_at TIMESTAMP DEFAULT NULL,
    served_by VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (no_rm) REFERENCES pasiens (no_rm) ON DELETE CASCADE,
    FOREIGN KEY (kd_poli) REFERENCES polis (kd_poli) ON DELETE CASCADE,
    FOREIGN KEY (kd_dokter) REFERENCES dokters (kd_dokter) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_queue_tanggal ON queues (tanggal);
CREATE INDEX IF NOT EXISTS idx_queue_status ON queues (status);


-- ============================================================
-- SEED DATA DEFAULT (Puskesmas Salem)
-- ============================================================

-- 1. Akun Admin Default: admin@silla.com / admin123
INSERT INTO users (uid, email, password, display_name, role, created_at) 
VALUES (
    'u_admin', 
    'admin@silla.com', 
    '$2y$10$g1kZq7qQYFj613Z.YpYj/exq1.wQ7aV.g4Xk78xO9bX9uDkIq7cK6', 
    'Administrator', 
    'admin', 
    CURRENT_TIMESTAMP
) ON CONFLICT (uid) DO NOTHING;

-- 2. Daftar Loket Awal
INSERT INTO counters (id, name, is_active, current_queue_number, current_queue_id, assigned_officer_uid) 
VALUES 
('c_loket1', 'Loket 1', TRUE, NULL, NULL, NULL),
('c_loket2', 'Loket 2', TRUE, NULL, NULL, NULL),
('c_loket3', 'Loket 3', TRUE, NULL, NULL, NULL)
ON CONFLICT (id) DO NOTHING;

-- 3. Data Poliklinik (Poli)
INSERT INTO polis (kd_poli, nama_poli) VALUES
('P001', 'Poli Umum'),
('P002', 'Poli KIA/KB'),
('P003', 'Poli Gigi'),
('P004', 'Poli Lansia')
ON CONFLICT (kd_poli) DO NOTHING;

-- 4. Data Dokter
INSERT INTO dokters (kd_dokter, nama_dokter, kd_poli) VALUES
('D001', 'dr. Andi Pratama', 'P001'),
('D002', 'dr. Budi Santoso', 'P002'),
('D003', 'drg. Siti Rahayu', 'P003'),
('D004', 'dr. Maya Dewi', 'P004')
ON CONFLICT (kd_dokter) DO NOTHING;

-- 5. Jadwal Praktik Dokter
INSERT INTO jadwal_praktiks (kd_dokter, hari, jam_mulai, jam_selesai, kuota) VALUES
('D001', 'Senin', '08:00:00', '12:00:00', 20),
('D001', 'Rabu', '08:00:00', '12:00:00', 20),
('D001', 'Jumat', '08:00:00', '12:00:00', 20),
('D002', 'Senin', '09:00:00', '12:00:00', 18),
('D002', 'Kamis', '09:00:00', '12:00:00', 18),
('D003', 'Selasa', '08:00:00', '12:00:00', 15),
('D003', 'Kamis', '08:00:00', '12:00:00', 15),
('D004', 'Selasa', '08:00:00', '11:00:00', 20),
('D004', 'Sabtu', '08:00:00', '11:00:00', 20)
ON CONFLICT (kd_dokter, hari) DO NOTHING;
