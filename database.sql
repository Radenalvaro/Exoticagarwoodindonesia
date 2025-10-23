-- Membuat tabel loginmaintainance dengan sistem hash yang aman
USE agarwood_db;

-- Drop tabel lama jika ada
DROP TABLE IF EXISTS loginmaintainance;

-- Buat tabel loginmaintainance dengan sistem hash
CREATE TABLE loginmaintainance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of username',
    password_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of password',
    namaayah_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of nama ayah',
    namaibu_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of nama ibu',
    tanggallahir_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of tanggal lahir',
    seedphrase_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of 12 kata unik',
    combined_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of all data combined',
    hash_timestamp INT NOT NULL COMMENT 'Timestamp for hash generation (30 second intervals)',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert data default dengan hash (akan diupdate oleh script PHP)
-- Placeholder data - akan diupdate oleh auto-hash system
INSERT INTO loginmaintainance (
    username_hash, 
    password_hash, 
    namaayah_hash, 
    namaibu_hash, 
    tanggallahir_hash, 
    seedphrase_hash,
    combined_hash,
    hash_timestamp,
    is_active
) VALUES (
    'placeholder_username_hash',
    'placeholder_password_hash', 
    'placeholder_namaayah_hash',
    'placeholder_namaibu_hash',
    'placeholder_tanggallahir_hash',
    'placeholder_seedphrase_hash',
    'placeholder_combined_hash',
    0,
    1
);

-- Verifikasi struktur tabel
DESCRIBE loginmaintainance;

-- Cek data
SELECT * FROM loginmaintainance;
