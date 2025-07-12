# PSB Online Database

Database untuk Sistem Penerimaan Siswa Baru Online (PSB Online).

## Struktur Database

### Tabel Utama

#### 1. `users` - Manajemen User/Admin
- **Kolom**: id, username, password, email, role, nama_lengkap, is_active, last_login, created_at, updated_at
- **Fungsi**: Menyimpan data user/admin sistem
- **Role**: admin, operator, viewer

#### 2. `calon_siswa` - Data Pendaftar
- **Kolom**: id, nomor_daftar, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, alamat, telepon, email, asal_sekolah, nisn, nama_ayah, pekerjaan_ayah, nama_ibu, pekerjaan_ibu, penghasilan_ortu, foto, ijazah, kartu_keluarga, status_verifikasi, status_seleksi, catatan, created_at, updated_at
- **Fungsi**: Menyimpan data lengkap calon siswa

#### 3. `pendaftaran` - Form Pendaftaran
- **Kolom**: id, calon_siswa_id, tahun_ajaran, jalur_pendaftaran, pilihan_jurusan, nilai_un_*, rata_rata_un, prestasi_*, status_pendaftaran, tanggal_submit, verified_by, approved_by, catatan_verifikasi, created_at, updated_at
- **Fungsi**: Menyimpan data pendaftaran dan nilai akademik

#### 4. `pengumuman` - Hasil Seleksi
- **Kolom**: id, judul, konten, jenis, tanggal_publish, tanggal_berakhir, status, created_by, updated_by, created_at, updated_at
- **Fungsi**: Menyimpan pengumuman dan hasil seleksi

#### 5. `pengaturan` - Konfigurasi Sistem
- **Kolom**: id, nama_setting, nilai, deskripsi, kategori, is_public, created_at, updated_at
- **Fungsi**: Menyimpan konfigurasi sistem

#### 6. `backup_log` - Log Backup Database
- **Kolom**: id, nama_file, ukuran, tanggal_backup, status, keterangan, created_by, created_at
- **Fungsi**: Mencatat log backup database

### Relasi Antar Tabel

```
calon_siswa (1) ────> pendaftaran (1)
users (1) ──────────> pendaftaran (many) [verified_by, approved_by]
users (1) ──────────> pengumuman (many) [created_by, updated_by]
users (1) ──────────> backup_log (many) [created_by]
```

### Indexes

- **Primary Keys**: Semua tabel memiliki primary key auto increment
- **Foreign Keys**: Index otomatis untuk foreign key constraints
- **Composite Indexes**: 
  - `idx_pendaftaran_tahun_jalur` - untuk query berdasarkan tahun dan jalur
  - `idx_pendaftaran_status_tanggal` - untuk query status dan tanggal
  - `idx_calon_siswa_status` - untuk query status verifikasi dan seleksi
  - `idx_pengumuman_status_tanggal` - untuk query pengumuman aktif

### Views

#### 1. `v_pendaftar_lengkap`
- **Fungsi**: Menampilkan data lengkap pendaftar dengan informasi pendaftaran
- **Query**: JOIN antara `calon_siswa` dan `pendaftaran`

#### 2. `v_statistik_pendaftaran`
- **Fungsi**: Menampilkan statistik pendaftaran berdasarkan tahun, jalur, dan jurusan
- **Query**: GROUP BY dengan agregasi COUNT

### Stored Procedures

#### 1. `GenerateNomorDaftar(tahun_ajaran, nomor_daftar)`
- **Fungsi**: Generate nomor daftar otomatis
- **Parameter**: tahun_ajaran (IN), nomor_daftar (OUT)

#### 2. `UpdateStatusSeleksi()`
- **Fungsi**: Update status seleksi berdasarkan nilai UN
- **Logika**: 
  - UN ≥ 85.00 → lulus
  - UN ≥ 70.00 → pending
  - UN < 70.00 → tidak_lulus

### Triggers

#### 1. `update_rata_rata_un`
- **Event**: BEFORE UPDATE ON pendaftaran
- **Fungsi**: Update rata-rata UN otomatis saat nilai UN berubah

#### 2. `log_status_pendaftaran`
- **Event**: AFTER UPDATE ON pendaftaran
- **Fungsi**: Log perubahan status pendaftaran ke backup_log

## Sample Data

### Users
- **admin**: Administrator sistem (password: password)
- **operator1**: Operator pendaftaran (password: password)
- **viewer1**: Viewer data (password: password)

### Calon Siswa
- 5 sample data dengan informasi lengkap
- Variasi jalur pendaftaran: reguler, prestasi, afirmasi
- Data prestasi akademik dan non-akademik

### Pengaturan Sistem
- 20 pengaturan default meliputi:
  - Informasi sekolah
  - Konfigurasi pendaftaran
  - Pengaturan seleksi
  - Konfigurasi email
  - Mode maintenance

## Cara Penggunaan

### 1. Import Database
```bash
mysql -u username -p < database/psb_online.sql
```

### 2. Koneksi Database
```php
// PHP Example
$host = 'localhost';
$dbname = 'psb_online';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
```

### 3. Query Examples

#### Get Pendaftar Lengkap
```sql
SELECT * FROM v_pendaftar_lengkap WHERE tahun_ajaran = '2024/2025';
```

#### Get Statistik Pendaftaran
```sql
SELECT * FROM v_statistik_pendaftaran WHERE tahun_ajaran = '2024/2025';
```

#### Get Pengumuman Aktif
```sql
SELECT * FROM pengumuman 
WHERE status = 'published' 
AND tanggal_publish <= NOW() 
AND (tanggal_berakhir IS NULL OR tanggal_berakhir >= NOW())
ORDER BY tanggal_publish DESC;
```

#### Get Pengaturan Sistem
```sql
SELECT nama_setting, nilai FROM pengaturan WHERE kategori = 'sistem';
```

### 4. Generate Nomor Daftar
```sql
CALL GenerateNomorDaftar('2024/2025', @nomor_daftar);
SELECT @nomor_daftar;
```

### 5. Update Status Seleksi
```sql
CALL UpdateStatusSeleksi();
```

## Keamanan

### Password Hashing
- Password disimpan menggunakan bcrypt (hash: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`)
- Sample password: "password"

### Role-based Access
- **admin**: Akses penuh ke semua fitur
- **operator**: Akses terbatas untuk operasi pendaftaran
- **viewer**: Akses read-only

### Data Validation
- Foreign key constraints untuk integritas data
- ENUM untuk membatasi nilai yang valid
- NOT NULL constraints untuk data wajib

## Backup dan Maintenance

### Backup Otomatis
- Log backup tersimpan di tabel `backup_log`
- Informasi: nama file, ukuran, tanggal, status, keterangan

### Maintenance Mode
- Pengaturan `maintenance_mode` untuk mode maintenance
- Dapat diaktifkan/nonaktifkan melalui tabel `pengaturan`

## Pengembangan

### Menambah Tabel Baru
1. Definisikan struktur tabel
2. Tambahkan foreign key jika diperlukan
3. Buat index untuk performa
4. Tambahkan sample data

### Menambah Stored Procedure
1. Definisikan parameter IN/OUT
2. Implementasi logika bisnis
3. Test dengan sample data

### Menambah Trigger
1. Tentukan event (BEFORE/AFTER INSERT/UPDATE/DELETE)
2. Implementasi logika trigger
3. Test dengan operasi database

## Troubleshooting

### Error Common
1. **Foreign Key Constraint**: Pastikan data referensi ada
2. **Unique Constraint**: Cek duplikasi data
3. **ENUM Value**: Pastikan nilai sesuai dengan definisi ENUM

### Performance
1. Gunakan index untuk query yang sering digunakan
2. Monitor query slow dengan EXPLAIN
3. Optimasi query dengan JOIN yang tepat

## Support

Untuk pertanyaan atau masalah terkait database, silakan hubungi tim development. 