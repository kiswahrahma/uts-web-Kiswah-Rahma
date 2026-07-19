-- ============================================
-- MIGRASI: Tambah kolom email dan OTP
-- Jalankan di database "cafe" kamu
-- ============================================

-- 1. Tambah kolom email terlebih dahulu (nullable agar aman untuk user yang sudah ada)
ALTER TABLE users ADD COLUMN email VARCHAR(100) NULL AFTER nama;

-- 2. Isi email user yang sudah ada dengan email default (username@example.com)
UPDATE users SET email = CONCAT(username, '@example.com') WHERE email IS NULL;

-- 3. Ubah email menjadi NOT NULL dan buat UNIQUE
ALTER TABLE users MODIFY COLUMN email VARCHAR(100) NOT NULL;
ALTER TABLE users ADD UNIQUE (email);

-- 4. Tambah kolom otp_code dan otp_expiry
ALTER TABLE users ADD COLUMN otp_code VARCHAR(6) NULL AFTER password;
ALTER TABLE users ADD COLUMN otp_expiry DATETIME NULL AFTER otp_code;
