-- =====================================================
-- PSB ONLINE DATABASE
-- Sistem Penerimaan Siswa Baru Online
-- =====================================================

-- Drop database if exists (for development)
DROP DATABASE IF EXISTS psb_online;

-- Create database
CREATE DATABASE psb_online;
USE psb_online;

-- =====================================================
-- TABLE: users (Admin/User Management)
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'super_admin', 'operator', 'viewer') NOT NULL DEFAULT 'operator',
    nama_lengkap VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    login_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    remember_token VARCHAR(64) NULL,
    remember_expires DATETIME NULL,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_remember_token (remember_token)
);

-- =====================================================
-- TABLE: calon_siswa (Data Pendaftar)
-- =====================================================
CREATE TABLE calon_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_daftar VARCHAR(20) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    tempat_lahir VARCHAR(50) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    agama VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL,
    telepon VARCHAR(15) NOT NULL,
    email VARCHAR(100) NULL,
    asal_sekolah VARCHAR(100) NOT NULL,
    nisn VARCHAR(20) NOT NULL,
    nama_ayah VARCHAR(100) NOT NULL,
    pekerjaan_ayah VARCHAR(50) NOT NULL,
    nama_ibu VARCHAR(100) NOT NULL,
    pekerjaan_ibu VARCHAR(50) NOT NULL,
    penghasilan_ortu DECIMAL(12,2) NOT NULL,
    foto VARCHAR(255) NULL,
    ijazah VARCHAR(255) NULL,
    kartu_keluarga VARCHAR(255) NULL,
    status_verifikasi ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    status_seleksi ENUM('pending', 'lulus', 'tidak_lulus') DEFAULT 'pending',
    catatan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_nomor_daftar (nomor_daftar),
    INDEX idx_nama (nama_lengkap),
    INDEX idx_nisn (nisn),
    INDEX idx_status_verifikasi (status_verifikasi),
    INDEX idx_status_seleksi (status_seleksi),
    INDEX idx_tanggal_lahir (tanggal_lahir)
);

-- =====================================================
-- TABLE: pendaftaran (Form Pendaftaran)
-- =====================================================
CREATE TABLE pendaftaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    calon_siswa_id INT NOT NULL,
    tahun_ajaran VARCHAR(10) NOT NULL,
    jalur_pendaftaran ENUM('reguler', 'prestasi', 'afirmasi', 'perpindahan') NOT NULL,
    pilihan_jurusan VARCHAR(50) NOT NULL,
    nilai_un_matematika DECIMAL(5,2) NULL,
    nilai_un_ipa DECIMAL(5,2) NULL,
    nilai_un_bindo DECIMAL(5,2) NULL,
    nilai_un_bing DECIMAL(5,2) NULL,
    rata_rata_un DECIMAL(5,2) NULL,
    prestasi_akademik TEXT NULL,
    prestasi_non_akademik TEXT NULL,
    dokumen_pendukung TEXT NULL,
    status_pendaftaran ENUM('draft', 'submitted', 'verified', 'approved', 'rejected') DEFAULT 'draft',
    tanggal_submit DATETIME NULL,
    verified_by INT NULL,
    verified_at DATETIME NULL,
    approved_by INT NULL,
    approved_at DATETIME NULL,
    catatan_verifikasi TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (calon_siswa_id) REFERENCES calon_siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_calon_siswa (calon_siswa_id),
    INDEX idx_tahun_ajaran (tahun_ajaran),
    INDEX idx_jalur_pendaftaran (jalur_pendaftaran),
    INDEX idx_status_pendaftaran (status_pendaftaran),
    INDEX idx_tanggal_submit (tanggal_submit)
);

-- =====================================================
-- TABLE: pengumuman (Hasil Seleksi)
-- =====================================================
CREATE TABLE pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    konten TEXT NOT NULL,
    jenis ENUM('umum', 'seleksi', 'pengumuman') DEFAULT 'umum',
    tanggal_publish DATETIME NOT NULL,
    tanggal_berakhir DATETIME NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_by INT NOT NULL,
    updated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_judul (judul),
    INDEX idx_jenis (jenis),
    INDEX idx_status (status),
    INDEX idx_tanggal_publish (tanggal_publish)
);

-- =====================================================
-- TABLE: pengaturan (Konfigurasi Sistem)
-- =====================================================
CREATE TABLE pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_setting VARCHAR(100) NOT NULL UNIQUE,
    nilai TEXT NOT NULL,
    deskripsi TEXT NULL,
    kategori ENUM('sistem', 'pendaftaran', 'seleksi', 'pengumuman', 'email') DEFAULT 'sistem',
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_nama_setting (nama_setting),
    INDEX idx_kategori (kategori)
);

-- =====================================================
-- TABLE: activity_log (Log Aktivitas User)
-- =====================================================
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- =====================================================
-- TABLE: backup_log (Log Backup Database)
-- =====================================================
CREATE TABLE backup_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_file VARCHAR(255) NOT NULL,
    ukuran BIGINT NOT NULL,
    tanggal_backup DATETIME NOT NULL,
    status ENUM('success', 'failed', 'in_progress') DEFAULT 'success',
    keterangan TEXT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_nama_file (nama_file),
    INDEX idx_tanggal_backup (tanggal_backup),
    INDEX idx_status (status)
);

-- =====================================================
-- SAMPLE DATA
-- =====================================================













-- =====================================================
-- ADDITIONAL INDEXES FOR PERFORMANCE
-- =====================================================

-- Composite indexes for better query performance
CREATE INDEX idx_pendaftaran_tahun_jalur ON pendaftaran(tahun_ajaran, jalur_pendaftaran);
CREATE INDEX idx_pendaftaran_status_tanggal ON pendaftaran(status_pendaftaran, tanggal_submit);
CREATE INDEX idx_calon_siswa_status ON calon_siswa(status_verifikasi, status_seleksi);
CREATE INDEX idx_pengumuman_status_tanggal ON pengumuman(status, tanggal_publish);

-- =====================================================
-- VIEWS FOR COMMON QUERIES
-- =====================================================

-- View untuk data lengkap pendaftar
CREATE VIEW v_pendaftar_lengkap AS
SELECT 
    cs.id,
    cs.nomor_daftar,
    cs.nama_lengkap,
    cs.tempat_lahir,
    cs.tanggal_lahir,
    cs.jenis_kelamin,
    cs.agama,
    cs.alamat,
    cs.telepon,
    cs.email,
    cs.asal_sekolah,
    cs.nisn,
    cs.nama_ayah,
    cs.pekerjaan_ayah,
    cs.nama_ibu,
    cs.pekerjaan_ibu,
    cs.penghasilan_ortu,
    cs.status_verifikasi,
    cs.status_seleksi,
    p.tahun_ajaran,
    p.jalur_pendaftaran,
    p.pilihan_jurusan,
    p.nilai_un_matematika,
    p.nilai_un_ipa,
    p.nilai_un_bindo,
    p.nilai_un_bing,
    p.rata_rata_un,
    p.prestasi_akademik,
    p.prestasi_non_akademik,
    p.status_pendaftaran,
    p.tanggal_submit
FROM calon_siswa cs
LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id;

-- View untuk statistik pendaftaran
CREATE VIEW v_statistik_pendaftaran AS
SELECT 
    tahun_ajaran,
    jalur_pendaftaran,
    pilihan_jurusan,
    COUNT(*) as total_pendaftar,
    SUM(CASE WHEN status_pendaftaran = 'submitted' THEN 1 ELSE 0 END) as menunggu_verifikasi,
    SUM(CASE WHEN status_pendaftaran = 'verified' THEN 1 ELSE 0 END) as sudah_diverifikasi,
    SUM(CASE WHEN status_pendaftaran = 'approved' THEN 1 ELSE 0 END) as sudah_disetujui,
    SUM(CASE WHEN status_pendaftaran = 'rejected' THEN 1 ELSE 0 END) as ditolak
FROM pendaftaran
GROUP BY tahun_ajaran, jalur_pendaftaran, pilihan_jurusan;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

DELIMITER //

-- Procedure untuk generate nomor daftar otomatis
CREATE PROCEDURE GenerateNomorDaftar(IN tahun_ajaran VARCHAR(10), OUT nomor_daftar VARCHAR(20))
BEGIN
    DECLARE counter INT DEFAULT 1;
    DECLARE nomor_temp VARCHAR(20);
    
    REPEAT
        SET nomor_temp = CONCAT('PSB-', SUBSTRING(tahun_ajaran, 1, 4), '-', LPAD(counter, 3, '0'));
        SET counter = counter + 1;
    UNTIL NOT EXISTS (SELECT 1 FROM calon_siswa WHERE nomor_daftar = nomor_temp) END REPEAT;
    
    SET nomor_daftar = nomor_temp;
END //

-- Procedure untuk update status seleksi berdasarkan nilai
CREATE PROCEDURE UpdateStatusSeleksi()
BEGIN
    UPDATE calon_siswa cs
    JOIN pendaftaran p ON cs.id = p.calon_siswa_id
    SET cs.status_seleksi = 
        CASE 
            WHEN p.rata_rata_un >= 85.00 THEN 'lulus'
            WHEN p.rata_rata_un >= 70.00 THEN 'pending'
            ELSE 'tidak_lulus'
        END
    WHERE p.status_pendaftaran = 'approved';
END //

DELIMITER ;

-- =====================================================
-- TRIGGERS
-- =====================================================

DELIMITER //

-- Trigger untuk update rata_rata_un otomatis
CREATE TRIGGER update_rata_rata_un
BEFORE UPDATE ON pendaftaran
FOR EACH ROW
BEGIN
    IF NEW.nilai_un_matematika IS NOT NULL AND NEW.nilai_un_ipa IS NOT NULL 
       AND NEW.nilai_un_bindo IS NOT NULL AND NEW.nilai_un_bing IS NOT NULL THEN
        SET NEW.rata_rata_un = (NEW.nilai_un_matematika + NEW.nilai_un_ipa + NEW.nilai_un_bindo + NEW.nilai_un_bing) / 4;
    END IF;
END //

-- Trigger untuk log perubahan status pendaftaran
CREATE TRIGGER log_status_pendaftaran
AFTER UPDATE ON pendaftaran
FOR EACH ROW
BEGIN
    IF OLD.status_pendaftaran != NEW.status_pendaftaran THEN
        INSERT INTO backup_log (nama_file, ukuran, tanggal_backup, status, keterangan)
        VALUES (
            CONCAT('status_change_', NEW.id, '_', NOW()),
            0,
            NOW(),
            'success',
            CONCAT('Status pendaftaran berubah dari ', OLD.status_pendaftaran, ' ke ', NEW.status_pendaftaran)
        );
    END IF;
END //

DELIMITER ;

-- =====================================================
-- COMMENTS AND DOCUMENTATION
-- =====================================================

/*
PSB ONLINE DATABASE SCHEMA
==========================

TABLES:
1. users - Manajemen user/admin sistem
2. calon_siswa - Data lengkap calon siswa
3. pendaftaran - Form pendaftaran dan nilai
4. pengumuman - Pengumuman dan hasil seleksi
5. pengaturan - Konfigurasi sistem
6. backup_log - Log backup database

RELATIONSHIPS:
- calon_siswa (1) -> pendaftaran (1)
- users (1) -> pendaftaran (many) [verified_by, approved_by]
- users (1) -> pengumuman (many) [created_by, updated_by]
- users (1) -> backup_log (many) [created_by]

INDEXES:
- Primary keys on all tables
- Foreign key indexes
- Composite indexes for common queries
- Text search indexes

VIEWS:
- v_pendaftar_lengkap - Data lengkap pendaftar
- v_statistik_pendaftaran - Statistik pendaftaran

PROCEDURES:
- GenerateNomorDaftar - Generate nomor daftar otomatis
- UpdateStatusSeleksi - Update status seleksi berdasarkan nilai

TRIGGERS:
- update_rata_rata_un - Update rata-rata UN otomatis
- log_status_pendaftaran - Log perubahan status pendaftaran

SAMPLE DATA:
- 3 users (admin, operator, viewer)
- 5 calon siswa dengan data lengkap
- 5 pendaftaran dengan nilai dan prestasi
- 4 pengumuman
- 20 pengaturan sistem
- 4 log backup

USAGE:
1. Import file ini ke MySQL/MariaDB
2. Database akan dibuat dengan nama 'psb_online'
3. Semua tabel, data sample, dan relasi akan tersedia
4. Sistem siap untuk development dan testing
*/

-- =====================================================
-- END OF PSB ONLINE DATABASE
-- ===================================================== 