-- ============================================
-- MIGRASI: Tambah kolom role (admin / pelanggan)
-- Jalankan sekali di database "cafe" kamu (phpMyAdmin -> tab SQL)
-- ============================================

-- 1. Tambah kolom role, default pelanggan supaya akun yang sudah ada tetap aman
ALTER TABLE users
  ADD COLUMN role ENUM('admin','pelanggan') NOT NULL DEFAULT 'pelanggan' AFTER username;

-- 2. Jadikan akun admin kamu sebagai admin.
--    Ganti 'admin' di bawah ini dengan username akun admin kamu yang sebenarnya.
--    Bisa dijalankan berkali-kali / untuk lebih dari satu akun.
UPDATE users SET role='admin' WHERE username='admin';

-- Cek hasilnya:
-- SELECT id, nama, username, role FROM users;
