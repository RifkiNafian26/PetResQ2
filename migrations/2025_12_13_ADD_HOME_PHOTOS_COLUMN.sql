-- =========================================================
-- SQL UNTUK MENAMBAHKAN KOLOM HOME_PHOTOS_JSON
-- Jalankan SQL ini di phpMyAdmin atau MySQL client
-- =========================================================

-- Tambahkan kolom home_photos_json untuk menyimpan array path foto rumah
ALTER TABLE `adoption_applications` 
ADD COLUMN `home_photos_json` TEXT DEFAULT NULL AFTER `details_json`;

-- Verifikasi kolom sudah ditambahkan
-- Jalankan query ini: SHOW COLUMNS FROM adoption_applications;

-- =========================================================
-- CATATAN:
-- - Kolom home_photos_json menyimpan array JSON path foto
-- - Format: ["uploads/adoption/home_photos/photo1.jpg", "uploads/adoption/home_photos/photo2.jpg", ...]
-- - Maksimal 4 foto (sesuai form)
-- - Foto disimpan di folder: uploads/adoption/home_photos/
-- =========================================================

