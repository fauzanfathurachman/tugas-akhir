<?php
require_once '../config/database.php';
require_once 'auth_check.php';

header('Content-Type: application/json');

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID siswa tidak valid']);
    exit;
}

$id = (int)$_POST['id'];
$db = Database::getInstance();

try {
    // Get student data with registration info
    $query = "SELECT cs.*, p.status_verifikasi, p.status_seleksi, p.nomor_pendaftaran, 
                     p.tanggal_daftar, p.catatan_verifikasi, p.nilai_tes
              FROM calon_siswa cs 
              LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id 
              WHERE cs.id = ?";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$siswa) {
        echo json_encode(['success' => false, 'message' => 'Data siswa tidak ditemukan']);
        exit;
    }
    
    // Return the data as JSON
    echo json_encode(['success' => true, 'data' => $siswa]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 