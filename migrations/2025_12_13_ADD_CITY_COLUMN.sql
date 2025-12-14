-- =========================================================
-- SQL UNTUK MENAMBAHKAN KOLOM CITY
-- Jalankan SQL ini di phpMyAdmin atau MySQL client
-- =========================================================

-- Cek dulu apakah kolom city sudah ada
-- Jalankan query ini: SHOW COLUMNS FROM adoption_applications LIKE 'city';
-- Jika tidak ada hasil, berarti kolom belum ada, lanjutkan ke query di bawah

-- Tambahkan kolom city setelah address_line1
ALTER TABLE `adoption_applications` 
ADD COLUMN `city` VARCHAR(100) DEFAULT NULL AFTER `address_line1`;

-- Verifikasi kolom sudah ditambahkan
-- Jalankan query ini: SHOW COLUMNS FROM adoption_applications;

-- =========================================================
-- CATATAN:
-- - Kolom city bersifat optional (boleh NULL)
-- - Home photos tidak perlu tabel terpisah, sudah disimpan di details_json sebagai array
-- - Format: { "home_photos": ["path1", "path2", ...] }
-- =========================================================

