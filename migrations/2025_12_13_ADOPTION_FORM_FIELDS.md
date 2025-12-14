# Dokumentasi Field Form Adoption

## Step-by-Step Field Requirements

### Step 1: Start
- **User Info** (dari session):
  - `full_name` → kolom `full_name` di `adoption_applications`
  - `email` → kolom `email` di `adoption_applications`
  - `applicant_user_id` → kolom `applicant_user_id` di `adoption_applications`

### Step 2: Address
- **Field yang diperlukan:**
  - `address` → kolom `address_line1` (VARCHAR(255), NOT NULL)
  - `postcode` → kolom `postcode` (VARCHAR(20), NOT NULL)
  - `telephone` → kolom `phone` (VARCHAR(30), NULL)
  - `city` → kolom `city` (VARCHAR(100), NULL) - **PERLU DITAMBAHKAN**

### Step 3: Home
- **Field yang diperlukan:**
  - `garden` (Yes/No) → kolom `has_garden` (TINYINT(1), NOT NULL, DEFAULT 0)
  - `living_situation` → kolom `living_situation` (VARCHAR(200), NULL)
  - `household_setting` → disimpan di `details_json` (TEXT, NULL)
  - `household_activity` → disimpan di `details_json` (TEXT, NULL)

### Step 4: Home Picture
- **Field yang diperlukan:**
  - `home_photos` (4 foto, min 2) → **PERLU TABEL BARU** `adoption_home_photos`
  - Setiap foto disimpan dengan:
    - `application_id` (INT, FK ke `adoption_applications.id`)
    - `photo_path` (VARCHAR(255), path ke file)
    - `photo_order` (TINYINT(1), urutan foto 1-4)
    - `uploaded_at` (DATETIME)

### Step 5: Roommate
- **Field yang diperlukan (semua disimpan di `details_json`):**
  - `adults` (INT, min 1)
  - `children` (INT, default 0)
  - `children_ages` (VARCHAR, pilihan: "no-children", "0-5", "6-12", "13-17")
  - `visiting_children` (VARCHAR, "Yes"/"No")
  - `visiting_ages` (VARCHAR, pilihan: "under5", "over5", "all")
  - `flatmates` (VARCHAR, "Yes"/"No")
  - `flatmates_consent` (VARCHAR, "Yes"/"No")

### Step 6: Other Animals
- **Field yang diperlukan:**
  - `allergies` → disimpan di `details_json`
  - `other_animals` (VARCHAR, "Yes"/"No"/"Not Applicable") → disimpan di `details_json`
  - `vaccinated` (VARCHAR, "Yes"/"No"/"Not Applicable") → disimpan di `details_json`
  - `experience` → kolom `story` (TEXT, NULL)

### Step 7: Confirm
- Review semua data dari step sebelumnya
- Checkbox konfirmasi (tidak disimpan di DB)

### Step 8: Thank You
- Halaman sukses (tidak ada data)

## Struktur Database yang Diperlukan

### Tabel: `adoption_applications`
Kolom yang sudah ada:
- ✅ `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
- ✅ `applicant_user_id` (INT, NOT NULL, FK ke `user.id_user`)
- ✅ `assigned_admin_user_id` (INT, NOT NULL, DEFAULT 1, FK ke `user.id_user`)
- ✅ `hewan_id` (INT, NULL, FK ke `hewan.id_hewan`)
- ✅ `full_name` (VARCHAR(120), NOT NULL)
- ✅ `email` (VARCHAR(120), NOT NULL)
- ✅ `phone` (VARCHAR(30), NULL)
- ✅ `address_line1` (VARCHAR(255), NOT NULL)
- ⚠️ `city` (VARCHAR(100), NULL) - **PERLU DITAMBAHKAN JIKA BELUM ADA**
- ✅ `postcode` (VARCHAR(20), NOT NULL)
- ✅ `has_garden` (TINYINT(1), NOT NULL, DEFAULT 0)
- ✅ `living_situation` (VARCHAR(200), NULL)
- ✅ `story` (TEXT, NULL)
- ✅ `details_json` (TEXT, NULL)
- ✅ `status` (ENUM('submitted','in_review','approved','rejected','withdrawn'), NOT NULL, DEFAULT 'submitted')
- ✅ `submitted_at` (DATETIME, NOT NULL, DEFAULT CURRENT_TIMESTAMP)
- ✅ `updated_at` (DATETIME, NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)

### Tabel: `adoption_home_photos` (BARU)
Kolom yang perlu dibuat:
- `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
- `application_id` (INT, NOT NULL, FK ke `adoption_applications.id`)
- `photo_path` (VARCHAR(255), NOT NULL)
- `photo_order` (TINYINT(1), NOT NULL, DEFAULT 1, COMMENT 'Urutan foto 1-4')
- `uploaded_at` (DATETIME, NOT NULL, DEFAULT CURRENT_TIMESTAMP)

## Field yang Disimpan di `details_json`

Format JSON yang disimpan di kolom `details_json`:
```json
{
  "telephone": "string",
  "household_setting": "string",
  "household_activity": "string",
  "adults": 1,
  "children": 0,
  "children_ages": "0-5",
  "visiting_children": "Yes",
  "visiting_ages": "under5",
  "flatmates": "Yes",
  "flatmates_consent": "Yes",
  "allergies": "string",
  "other_animals": "Yes",
  "vaccinated": "Yes",
  "experience": "string"
}
```

## Checklist Implementasi

- [ ] Cek apakah kolom `city` sudah ada di `adoption_applications`
- [ ] Tambahkan kolom `city` jika belum ada
- [ ] Buat tabel `adoption_home_photos`
- [ ] Update `submit_adoption.php` untuk handle upload home photos
- [ ] Update `script.js` untuk mengirim home photos ke backend
- [ ] Pastikan semua field dari form terkirim ke backend
- [ ] Test form submission dengan semua field terisi

