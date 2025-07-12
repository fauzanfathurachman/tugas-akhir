# Sistem Verifikasi Berkas - PSB Online

## Overview
Sistem verifikasi berkas yang komprehensif untuk panel administrasi PSB MTs Ulul Albab dengan fitur preview dokumen, checklist verifikasi, approval/rejection, dan notifikasi email otomatis.

## 🚀 Fitur Utama

### 1. **Preview Dokumen**
- ✅ Image viewer modal untuk preview foto
- ✅ PDF viewer untuk dokumen (KK, Akta, Ijazah)
- ✅ Zoom in/out dan rotate image
- ✅ Download dokumen individual

### 2. **Checklist Verifikasi**
- ✅ Foto (3x4) - Validasi ukuran dan format
- ✅ Kartu Keluarga - Validasi keabsahan
- ✅ Akta Kelahiran - Validasi keabsahan
- ✅ Ijazah/SKL - Validasi keabsahan
- ✅ SKHUN - Validasi keabsahan
- ✅ NISN Valid - Validasi nomor NISN

### 3. **Status Approval/Rejection**
- ✅ Radio button untuk keputusan
- ✅ Textarea untuk komentar detail
- ✅ History verifikasi untuk tracking
- ✅ Timestamp dan verifier info

### 4. **Notifikasi Email Otomatis**
- ✅ Email HTML template yang menarik
- ✅ Status diterima/ditolak
- ✅ Detail verifikasi lengkap
- ✅ Informasi kontak panitia

### 5. **Download Berkas**
- ✅ Download individual dokumen
- ✅ Download ZIP archive
- ✅ Naming convention yang rapi
- ✅ Security check untuk akses file

### 6. **Filter dan Pencarian**
- ✅ Filter: Menunggu, Diverifikasi, Ditolak, Semua
- ✅ Search: Nama, NISN, Nomor Pendaftaran
- ✅ Real-time filtering dengan AJAX

### 7. **Bulk Operations**
- ✅ Verifikasi massal multiple siswa
- ✅ Select all/deselect all
- ✅ Batch email notifications

## 📁 File Structure

```
admin/
├── verifikasi.php                    # Main verification page
├── get_verification_students.php     # AJAX: Load students list
├── get_student_documents.php         # AJAX: Load student documents
├── get_student_verification_data.php # AJAX: Load verification modal data
├── save_verification.php             # AJAX: Save verification result
├── bulk_verify.php                   # AJAX: Bulk verification
├── download_documents.php            # Download handler
├── includes/
│   └── email_helper.php              # Email utility class
└── VERIFIKASI_README.md             # This file
```

## 🛠️ Setup dan Instalasi

### 1. **Database Requirements**
Pastikan tabel berikut sudah ada dan memiliki struktur yang benar:

```sql
-- Tabel calon_siswa
ALTER TABLE calon_siswa ADD COLUMN status_verifikasi ENUM('pending', 'verified', 'rejected') DEFAULT 'pending';

-- Tabel pendaftaran
ALTER TABLE pendaftaran ADD COLUMN verified_by INT NULL;
ALTER TABLE pendaftaran ADD COLUMN verified_at DATETIME NULL;
ALTER TABLE pendaftaran ADD COLUMN catatan_verifikasi TEXT NULL;

-- Tabel activity_log (jika belum ada)
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

### 2. **Upload Directories**
Buat direktori upload jika belum ada:

```bash
mkdir -p uploads/foto
mkdir -p uploads/kk
mkdir -p uploads/akta
mkdir -p uploads/ijazah
mkdir -p uploads/temp
chmod 755 uploads/
chmod 755 uploads/*/
```

### 3. **Email Configuration**
Update file `config/config.php` dengan SMTP settings:

```php
// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_FROM_NAME', 'PSB Online System');
define('SMTP_FROM_EMAIL', 'noreply@psbonline.com');
```

### 4. **File Permissions**
Pastikan file permissions yang benar:

```bash
chmod 644 admin/verifikasi.php
chmod 644 admin/*.php
chmod 755 uploads/
```

## 🎯 Cara Penggunaan

### 1. **Akses Halaman Verifikasi**
```
http://your-domain/admin/verifikasi.php
```

### 2. **Verifikasi Individual**
1. Pilih siswa dari daftar di sebelah kiri
2. Preview dokumen di panel kanan
3. Klik tombol "Verifikasi" pada siswa
4. Isi checklist verifikasi
5. Pilih keputusan (Diterima/Ditolak)
6. Tambahkan komentar jika perlu
7. Klik "Simpan Verifikasi"

### 3. **Verifikasi Massal**
1. Centang siswa yang akan diverifikasi
2. Klik "Verifikasi Massal"
3. Konfirmasi aksi
4. Sistem akan memverifikasi semua siswa terpilih

### 4. **Download Dokumen**
1. Pilih siswa
2. Klik "Lihat" untuk preview
3. Klik "Download" untuk download individual
4. Klik "Download ZIP" untuk download semua dokumen

## 📧 Email Templates

### Template Verifikasi Individual
- Header dengan logo sekolah
- Informasi siswa lengkap
- Status verifikasi (DITERIMA/DITOLAK)
- Detail checklist verifikasi
- Informasi verifier dan timestamp
- Footer dengan kontak panitia

### Template Verifikasi Massal
- Header yang sama
- Informasi verifikasi massal
- Checklist lengkap semua dokumen
- Metode verifikasi massal
- Informasi kontak

## 🔒 Security Features

### 1. **Authentication**
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ CSRF protection

### 2. **File Security**
- ✅ Path traversal protection
- ✅ File type validation
- ✅ Access control untuk uploads/

### 3. **Database Security**
- ✅ Prepared statements
- ✅ Input validation
- ✅ SQL injection protection

### 4. **Email Security**
- ✅ HTML email sanitization
- ✅ SMTP authentication
- ✅ Error logging

## 📊 Monitoring dan Logging

### 1. **Activity Logging**
```sql
-- View verification activities
SELECT * FROM activity_log WHERE action LIKE 'verification_%' ORDER BY created_at DESC;
```

### 2. **Email Logging**
```bash
# Check email logs
tail -f /var/log/mail.log
```

### 3. **Error Logging**
```bash
# Check PHP error logs
tail -f /var/log/php_errors.log
```

## 🧪 Testing

### 1. **Test Email Sending**
```php
// Test email configuration
$email_helper = new EmailHelper();
$result = $email_helper->sendVerificationEmail($student_data, 'verified', 'Test notes', 'Test Verifier');
var_dump($result);
```

### 2. **Test Document Download**
```
http://your-domain/admin/download_documents.php?student_id=1&type=individual&document=foto
```

### 3. **Test Bulk Verification**
```javascript
// Test bulk verification via browser console
$.ajax({
    url: 'bulk_verify.php',
    method: 'POST',
    data: { student_ids: [1, 2, 3] },
    success: function(response) {
        console.log(response);
    }
});
```

## 🔧 Troubleshooting

### 1. **Email Tidak Terkirim**
- Periksa SMTP settings di `config.php`
- Pastikan SMTP server aktif
- Cek error logs untuk detail error

### 2. **Dokumen Tidak Muncul**
- Periksa path uploads/ directory
- Pastikan file permissions benar
- Cek apakah file benar-benar ada di server

### 3. **Verifikasi Tidak Tersimpan**
- Periksa database connection
- Pastikan tabel memiliki struktur yang benar
- Cek error logs untuk detail error

### 4. **Modal Tidak Muncul**
- Periksa JavaScript console untuk errors
- Pastikan jQuery dan SweetAlert2 loaded
- Cek network tab untuk AJAX errors

## 📈 Performance Optimization

### 1. **Database Optimization**
```sql
-- Add indexes for better performance
CREATE INDEX idx_calon_siswa_status_verifikasi ON calon_siswa(status_verifikasi);
CREATE INDEX idx_pendaftaran_verified_at ON pendaftaran(verified_at);
```

### 2. **File Optimization**
- Compress images before upload
- Use CDN for static assets
- Enable browser caching

### 3. **Email Optimization**
- Use queue system for bulk emails
- Implement email templates caching
- Use async email sending

## 🔄 Updates dan Maintenance

### 1. **Regular Updates**
- Update email templates sesuai kebutuhan
- Monitor email delivery rates
- Backup verification data regularly

### 2. **Data Cleanup**
```sql
-- Clean old activity logs
DELETE FROM activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Clean temp files
rm -rf uploads/temp/*
```

### 3. **Performance Monitoring**
- Monitor database query performance
- Check email delivery success rates
- Monitor file storage usage

## 📞 Support

Untuk bantuan teknis atau pertanyaan tentang sistem verifikasi:

- **Email:** admin@mtululalbab.sch.id
- **Phone:** +62-xxx-xxx-xxxx
- **Documentation:** Lihat file ini dan komentar dalam kode

---

**Version:** 1.0.0  
**Last Updated:** December 2024  
**Author:** MTs Ulul Albab Development Team 