-- ============================================
-- MIGRASI: Tambah kolom metode_pembayaran
-- Jalankan sekali di database "cafe" kamu (phpMyAdmin -> tab SQL)
-- ATAU sudah dijalankan otomatis oleh skrip PHP
-- ============================================

-- Tambah kolom metode_pembayaran ke tabel pesanan
-- DEFAULT 'Tunai' supaya data pesanan lama tetap valid
ALTER TABLE pesanan
  ADD COLUMN metode_pembayaran VARCHAR(50) NOT NULL DEFAULT 'Tunai' AFTER catatan;

-- Nilai yang valid: 'Tunai', 'Transfer Bank', 'QRIS', 'Dompet Digital'

-- Cek hasilnya:
-- DESCRIBE pesanan;
