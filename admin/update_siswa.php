<?php
require_once '../config/database.php';
// Tidak perlu cek login, langsung izinkan akses (untuk demo/tugas kecil)
require_once '../config/validation_rules.php';

header('Content-Type: application/json');

if ($_POST['action'] !== 'update') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

$db = Database::getInstance();

try {
    $db->beginTransaction();
    
    // Validate input data
    $validation_errors = [];
    
    // Required fields
    $required_fields = ['id', 'nama', 'nisn'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $validation_errors[] = "Field $field wajib diisi";
        }
    }
    
    // Validate NISN format
    if (!empty($_POST['nisn']) && !preg_match('/^\d{10}$/', $_POST['nisn'])) {
        $validation_errors[] = "NISN harus berupa 10 digit angka";
    }
    
    // Validate email format
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $validation_errors[] = "Format email tidak valid";
    }
    
    // Validate phone number
    if (!empty($_POST['telepon']) && !preg_match('/^[0-9+\-\s()]+$/', $_POST['telepon'])) {
        $validation_errors[] = "Format nomor telepon tidak valid";
    }
    
    // Check if NISN already exists (excluding current record)
    if (!empty($_POST['nisn'])) {
        $check_stmt = $db->prepare("SELECT id FROM calon_siswa WHERE nisn = ? AND id != ?");
        $check_stmt->execute([$_POST['nisn'], $_POST['id']]);
        if ($check_stmt->fetch()) {
            $validation_errors[] = "NISN sudah terdaftar";
        }
    }
    
    if (!empty($validation_errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $validation_errors)]);
        exit;
    }
    
    // Update calon_siswa table
    $update_siswa = $db->prepare("
        UPDATE calon_siswa SET 
            nama = ?, nisn = ?, tempat_lahir = ?, tanggal_lahir = ?, 
            jenis_kelamin = ?, agama = ?, alamat = ?, telepon = ?, 
            email = ?, asal_sekolah = ?, nama_ayah = ?, pekerjaan_ayah = ?, 
            pendapatan_ayah = ?, nama_ibu = ?, pekerjaan_ibu = ?, 
            pendapatan_ibu = ?, updated_at = NOW()
        WHERE id = ?
    ");
    
    $update_siswa->execute([
        $_POST['nama'],
        $_POST['nisn'],
        $_POST['tempat_lahir'] ?: null,
        $_POST['tanggal_lahir'] ?: null,
        $_POST['jenis_kelamin'] ?: null,
        $_POST['agama'] ?: null,
        $_POST['alamat'] ?: null,
        $_POST['telepon'] ?: null,
        $_POST['email'] ?: null,
        $_POST['asal_sekolah'] ?: null,
        $_POST['nama_ayah'] ?: null,
        $_POST['pekerjaan_ayah'] ?: null,
        $_POST['pendapatan_ayah'] ?: null,
        $_POST['nama_ibu'] ?: null,
        $_POST['pekerjaan_ibu'] ?: null,
        $_POST['pendapatan_ibu'] ?: null,
        $_POST['id']
    ]);
    
    // Update pendaftaran table if status fields are provided
    if (isset($_POST['status_verifikasi']) || isset($_POST['status_seleksi'])) {
        // Check if pendaftaran record exists
        $check_pendaftaran = $db->prepare("SELECT id FROM pendaftaran WHERE calon_siswa_id = ?");
        $check_pendaftaran->execute([$_POST['id']]);
        $pendaftaran_exists = $check_pendaftaran->fetch();
        
        if ($pendaftaran_exists) {
            // Update existing record
            $update_pendaftaran = $db->prepare("
                UPDATE pendaftaran SET 
                    status_verifikasi = ?, 
                    status_seleksi = ?,
                    updated_at = NOW()
                WHERE calon_siswa_id = ?
            ");
            
            $update_pendaftaran->execute([
                $_POST['status_verifikasi'] ?: 'Menunggu',
                $_POST['status_seleksi'] ?: null,
                $_POST['id']
            ]);
        } else {
            // Create new record
            $insert_pendaftaran = $db->prepare("
                INSERT INTO pendaftaran (calon_siswa_id, status_verifikasi, status_seleksi, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            
            $insert_pendaftaran->execute([
                $_POST['id'],
                $_POST['status_verifikasi'] ?: 'Menunggu',
                $_POST['status_seleksi'] ?: null
            ]);
        }
    }
    
    $db->commit();
    
    // Log activity
    logActivity('UPDATE', 'calon_siswa', $_POST['id'], 'Updated student data: ' . $_POST['nama']);
    
    echo json_encode(['success' => true, 'message' => 'Data siswa berhasil diperbarui']);
    
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function logActivity($action, $table, $record_id, $description) {
    global $db;
    $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, table_name, record_id, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'] ?? 1,
        $action,
        $table,
        $record_id,
        $description,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}
?> 