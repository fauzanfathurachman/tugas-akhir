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
    role ENUM('admin', 'operator', 'viewer') NOT NULL DEFAULT 'operator',
    nama_lengkap VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
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

-- Insert sample users (admin)
INSERT INTO users (username, password, email, role, nama_lengkap) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@psbonline.com', 'admin', 'Administrator Sistem'),
('operator1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'operator1@psbonline.com', 'operator', 'Operator Pendaftaran'),
('viewer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'viewer1@psbonline.com', 'viewer', 'Viewer Data');

-- Insert sample calon_siswa
INSERT INTO calon_siswa (nomor_daftar, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, alamat, telepon, email, asal_sekolah, nisn, nama_ayah, pekerjaan_ayah, nama_ibu, pekerjaan_ibu, penghasilan_ortu) VALUES
('PSB-2024-001', 'Ahmad Fauzan', 'Jakarta', '2008-03-15', 'L', 'Islam', 'Jl. Sudirman No. 123, Jakarta Pusat', '081234567890', 'ahmad@email.com', 'SMP Negeri 1 Jakarta', '1234567890', 'Budi Santoso', 'Wiraswasta', 'Siti Aminah', 'Guru', 5000000.00),
('PSB-2024-002', 'Siti Nurhaliza', 'Bandung', '2008-07-22', 'P', 'Islam', 'Jl. Asia Afrika No. 45, Bandung', '081234567891', 'siti@email.com', 'SMP Negeri 2 Bandung', '1234567891', 'Ahmad Hidayat', 'PNS', 'Nurul Hidayah', 'Dokter', 8000000.00),
('PSB-2024-003', 'Budi Prasetyo', 'Surabaya', '2008-01-10', 'L', 'Islam', 'Jl. Pemuda No. 67, Surabaya', '081234567892', 'budi@email.com', 'SMP Negeri 3 Surabaya', '1234567892', 'Sukarno', 'Pedagang', 'Sukarni', 'Ibu Rumah Tangga', 3000000.00),
('PSB-2024-004', 'Dewi Sartika', 'Medan', '2008-11-05', 'P', 'Islam', 'Jl. Gatot Subroto No. 89, Medan', '081234567893', 'dewi@email.com', 'SMP Negeri 4 Medan', '1234567893', 'Raden Mas', 'Pengacara', 'Raden Ayu', 'Dosen', 12000000.00),
('PSB-2024-005', 'Rizki Pratama', 'Semarang', '2008-05-18', 'L', 'Islam', 'Jl. Pandanaran No. 12, Semarang', '081234567894', 'rizki@email.com', 'SMP Negeri 5 Semarang', '1234567894', 'Pratama Jaya', 'Arsitek', 'Sari Indah', 'Akuntan', 9000000.00);

-- Insert sample pendaftaran
INSERT INTO pendaftaran (calon_siswa_id, tahun_ajaran, jalur_pendaftaran, pilihan_jurusan, nilai_un_matematika, nilai_un_ipa, nilai_un_bindo, nilai_un_bing, rata_rata_un, prestasi_akademik, prestasi_non_akademik, status_pendaftaran, tanggal_submit) VALUES
(1, '2024/2025', 'reguler', 'IPA', 85.50, 88.00, 90.00, 87.50, 87.75, 'Juara 1 Olimpiade Matematika Tingkat Kota', 'Juara 2 Lomba Robotik', 'submitted', '2024-01-15 10:30:00'),
(2, '2024/2025', 'prestasi', 'IPS', 82.00, 85.50, 92.00, 89.00, 87.13, 'Juara 1 Olimpiade Sains Tingkat Provinsi', 'Juara 1 Lomba Debat', 'submitted', '2024-01-16 14:20:00'),
(3, '2024/2025', 'reguler', 'IPA', 78.50, 80.00, 85.00, 82.50, 81.50, 'Juara 3 Olimpiade Matematika Tingkat Sekolah', 'Anggota OSIS', 'submitted', '2024-01-17 09:15:00'),
(4, '2024/2025', 'afirmasi', 'IPS', 75.00, 78.50, 88.00, 85.00, 81.63, 'Peserta Olimpiade Sains', 'Anggota Pramuka', 'submitted', '2024-01-18 11:45:00'),
(5, '2024/2025', 'reguler', 'IPA', 88.00, 90.50, 92.00, 89.50, 90.00, 'Juara 2 Olimpiade Matematika Tingkat Kota', 'Juara 1 Lomba Cerdas Cermat', 'submitted', '2024-01-19 16:30:00');

-- Insert sample pengumuman
INSERT INTO pengumuman (judul, konten, jenis, tanggal_publish, status, created_by) VALUES
('Pembukaan Pendaftaran Siswa Baru Tahun Ajaran 2024/2025', 'Diumumkan kepada seluruh calon siswa dan orang tua bahwa pendaftaran siswa baru untuk tahun ajaran 2024/2025 telah dibuka. Pendaftaran dapat dilakukan secara online melalui website resmi sekolah.', 'umum', '2024-01-01 08:00:00', 'published', 1),
('Jadwal Tes Seleksi Penerimaan Siswa Baru', 'Tes seleksi akan dilaksanakan pada tanggal 15 Februari 2024. Peserta diharapkan hadir 30 menit sebelum jadwal tes yang telah ditentukan.', 'seleksi', '2024-02-01 10:00:00', 'published', 1),
('Pengumuman Hasil Seleksi Penerimaan Siswa Baru', 'Hasil seleksi penerimaan siswa baru tahun ajaran 2024/2025 akan diumumkan pada tanggal 1 Maret 2024 pukul 10:00 WIB melalui website sekolah dan papan pengumuman.', 'pengumuman', '2024-02-25 14:00:00', 'published', 1),
('Informasi Daftar Ulang Siswa yang Diterima', 'Bagi siswa yang dinyatakan lulus seleksi, daftar ulang dapat dilakukan pada tanggal 5-10 Maret 2024. Persyaratan dan jadwal lengkap dapat dilihat di website sekolah.', 'pengumuman', '2024-03-01 09:00:00', 'published', 1);

-- Insert sample pengaturan
INSERT INTO pengaturan (nama_setting, nilai, deskripsi, kategori) VALUES
('nama_sekolah', 'SMAN 1 Jakarta', 'Nama lengkap sekolah', 'sistem'),
('alamat_sekolah', 'Jl. Sudirman No. 1, Jakarta Pusat', 'Alamat lengkap sekolah', 'sistem'),
('telepon_sekolah', '021-1234567', 'Nomor telepon sekolah', 'sistem'),
('email_sekolah', 'info@sman1jakarta.sch.id', 'Email resmi sekolah', 'sistem'),
('website_sekolah', 'https://www.sman1jakarta.sch.id', 'Website resmi sekolah', 'sistem'),
('tahun_ajaran_aktif', '2024/2025', 'Tahun ajaran yang sedang aktif', 'pendaftaran'),
('tanggal_buka_pendaftaran', '2024-01-01', 'Tanggal pembukaan pendaftaran', 'pendaftaran'),
('tanggal_tutup_pendaftaran', '2024-01-31', 'Tanggal penutupan pendaftaran', 'pendaftaran'),
('kuota_reguler', '200', 'Kuota pendaftar jalur reguler', 'pendaftaran'),
('kuota_prestasi', '50', 'Kuota pendaftar jalur prestasi', 'pendaftaran'),
('kuota_afirmasi', '30', 'Kuota pendaftar jalur afirmasi', 'pendaftaran'),
('kuota_perpindahan', '20', 'Kuota pendaftar jalur perpindahan', 'pendaftaran'),
('minimal_nilai_un', '70.00', 'Nilai minimal UN untuk pendaftaran', 'seleksi'),
('bobot_nilai_un', '60', 'Bobot nilai UN dalam seleksi (persen)', 'seleksi'),
('bobot_prestasi', '40', 'Bobot prestasi dalam seleksi (persen)', 'seleksi'),
('smtp_host', 'smtp.gmail.com', 'SMTP host untuk pengiriman email', 'email'),
('smtp_port', '587', 'SMTP port untuk pengiriman email', 'email'),
('smtp_username', 'noreply@psbonline.com', 'SMTP username', 'email'),
('smtp_password', 'password123', 'SMTP password', 'email'),
('maintenance_mode', 'false', 'Mode maintenance sistem', 'sistem');

-- Insert sample backup_log
INSERT INTO backup_log (nama_file, ukuran, tanggal_backup, status, keterangan, created_by) VALUES
('backup_psb_online_2024_01_01.sql', 1048576, '2024-01-01 23:00:00', 'success', 'Backup otomatis harian', 1),
('backup_psb_online_2024_01_02.sql', 1052672, '2024-01-02 23:00:00', 'success', 'Backup otomatis harian', 1),
('backup_psb_online_2024_01_03.sql', 1060864, '2024-01-03 23:00:00', 'success', 'Backup otomatis harian', 1),
('backup_psb_online_manual_2024_01_15.sql', 1073152, '2024-01-15 14:30:00', 'success', 'Backup manual sebelum update sistem', 1);

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