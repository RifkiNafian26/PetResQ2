-- =========================================================
-- UPDATE ADOPTION APPLICATIONS SCHEMA
-- Menambahkan kolom yang kurang untuk form adoption
-- =========================================================

-- =========================================================
-- STEP 1: CEK KOLOM CITY
-- =========================================================
-- Jalankan query ini dulu untuk cek apakah kolom 'city' sudah ada:
-- SHOW COLUMNS FROM adoption_applications LIKE 'city';

-- Jika kolom belum ada (tidak ada hasil), jalankan query di bawah ini:
-- Jika kolom sudah ada, skip query ini untuk menghindari error.

-- ALTER TABLE `adoption_applications` 
-- ADD COLUMN `city` VARCHAR(100) DEFAULT NULL AFTER `address_line1`;

-- Atau gunakan stored procedure di bawah untuk menambahkan dengan aman:
DROP PROCEDURE IF EXISTS `add_city_column_if_not_exists`;

DELIMITER //
CREATE PROCEDURE `add_city_column_if_not_exists`()
BEGIN
    DECLARE column_exists INT DEFAULT 0;
    
    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'adoption_applications'
    AND COLUMN_NAME = 'city';
    
    IF column_exists = 0 THEN
        ALTER TABLE `adoption_applications` 
        ADD COLUMN `city` VARCHAR(100) DEFAULT NULL AFTER `address_line1`;
        SELECT 'Kolom city berhasil ditambahkan' AS result;
    ELSE
        SELECT 'Kolom city sudah ada' AS result;
    END IF;
END //
DELIMITER ;

-- Jalankan stored procedure
CALL `add_city_column_if_not_exists`();

-- Hapus stored procedure setelah digunakan (optional)
DROP PROCEDURE IF EXISTS `add_city_column_if_not_exists`;

-- 2. Home photos disimpan sebagai array di details_json
-- Tidak perlu tabel terpisah, home_photos akan disimpan sebagai array di dalam details_json
-- Format: { "home_photos": ["path1", "path2", "path3", "path4"] }

-- 3. Pastikan semua kolom yang diperlukan ada di adoption_applications
-- (Kolom-kolom ini seharusnya sudah ada, tapi kita pastikan)

-- Cek dan tambahkan kolom jika belum ada (menggunakan stored procedure untuk menghindari error jika kolom sudah ada)
-- Note: MySQL tidak support IF NOT EXISTS untuk ALTER TABLE ADD COLUMN
-- Jadi kita perlu cek manual atau gunakan syntax yang aman

-- Untuk kolom city (sudah ditambahkan di atas)
-- Untuk kolom lainnya, pastikan sesuai dengan migration awal

-- 4. Update status enum jika perlu (cek apakah sudah sesuai)
-- Status enum: 'submitted','in_review','approved','rejected','withdrawn'
-- Jika perlu menambahkan status baru, gunakan:
-- ALTER TABLE `adoption_applications` 
-- MODIFY COLUMN `status` ENUM('submitted','in_review','approved','rejected','withdrawn') NOT NULL DEFAULT 'submitted';

-- 5. Pastikan kolom details_json bisa menyimpan semua data tambahan
-- details_json sudah TEXT, jadi cukup untuk menyimpan JSON data

-- =========================================================
-- VERIFIKASI STRUKTUR TABEL
-- =========================================================
-- Jalankan query berikut untuk memverifikasi struktur tabel:

-- DESCRIBE adoption_applications;
-- DESCRIBE adoption_home_photos;

-- =========================================================
-- CATATAN FIELD YANG DIPERLUKAN
-- =========================================================
-- Dari form adoption, field yang diperlukan:
-- 
-- Step 2 (Address):
-- - address_line1 (VARCHAR(255)) ✓
-- - city (VARCHAR(100)) ✓ (ditambahkan)
-- - postcode (VARCHAR(20)) ✓
-- - phone (VARCHAR(30)) ✓
-- 
-- Step 3 (Home):
-- - has_garden (TINYINT(1)) ✓
-- - living_situation (VARCHAR(200)) ✓
-- - household_setting → disimpan di details_json ✓
-- - household_activity → disimpan di details_json ✓
-- 
-- Step 4 (Home Picture):
-- - home_photos → disimpan di tabel adoption_home_photos ✓ (tabel baru)
-- 
-- Step 5 (Roommate):
-- - adults → disimpan di details_json ✓
-- - children → disimpan di details_json ✓
-- - children_ages → disimpan di details_json ✓
-- - visiting_children → disimpan di details_json ✓
-- - visiting_ages → disimpan di details_json ✓
-- - flatmates → disimpan di details_json ✓
-- - flatmates_consent → disimpan di details_json ✓
-- 
-- Step 6 (Other Animals):
-- - allergies → disimpan di details_json ✓
-- - other_animals → disimpan di details_json ✓
-- - vaccinated → disimpan di details_json ✓
-- - experience → disimpan di story (TEXT) ✓
-- 
-- Field yang sudah ada di tabel:
-- - id ✓
-- - applicant_user_id ✓
-- - assigned_admin_user_id ✓
-- - hewan_id ✓
-- - full_name ✓
-- - email ✓
-- - phone ✓
-- - address_line1 ✓
-- - city ✓ (ditambahkan)
-- - postcode ✓
-- - has_garden ✓
-- - living_situation ✓
-- - story ✓
-- - details_json ✓
-- - status ✓
-- - submitted_at ✓
-- - updated_at ✓

