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
    // Get student data with verification info
    $query = "SELECT cs.*, p.status_verifikasi, p.status_seleksi, p.nomor_pendaftaran, 
                     p.tanggal_daftar, p.catatan_verifikasi, p.nilai_tes, p.verified_by, p.verified_at
              FROM calon_siswa cs 
              LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id 
              WHERE cs.id = ?";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Data siswa tidak ditemukan']);
        exit;
    }
    
    // Get verifier info if exists
    $verifier_name = 'Belum diverifikasi';
    if ($student['verified_by']) {
        $verifier_query = "SELECT nama_lengkap FROM users WHERE id = ?";
        $verifier_stmt = $db->prepare($verifier_query);
        $verifier_stmt->execute([$student['verified_by']]);
        $verifier = $verifier_stmt->fetch(PDO::FETCH_ASSOC);
        if ($verifier) {
            $verifier_name = $verifier['nama_lengkap'];
        }
    }
    
    // Generate HTML
    $html = '
    <div class="student-verification-info">
        <div class="student-basic-info">
            <div class="info-row">
                <div class="info-label">Nama Lengkap:</div>
                <div class="info-value">' . htmlspecialchars($student['nama']) . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">NISN:</div>
                <div class="info-value">' . htmlspecialchars($student['nisn']) . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Nomor Pendaftaran:</div>
                <div class="info-value">' . htmlspecialchars($student['nomor_pendaftaran'] ?? '-') . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Asal Sekolah:</div>
                <div class="info-value">' . htmlspecialchars($student['asal_sekolah']) . '</div>
            </div>
        </div>
        
        <div class="verification-status">
            <div class="status-item">
                <span class="status-label">Status Verifikasi:</span>
                <span class="status-badge ' . getStatusClass($student['status_verifikasi']) . '">' . getStatusText($student['status_verifikasi']) . '</span>
            </div>
            <div class="status-item">
                <span class="status-label">Diverifikasi oleh:</span>
                <span class="status-value">' . htmlspecialchars($verifier_name) . '</span>
            </div>';
    
    if ($student['verified_at']) {
        $html .= '
            <div class="status-item">
                <span class="status-label">Tanggal Verifikasi:</span>
                <span class="status-value">' . date('d/m/Y H:i', strtotime($student['verified_at'])) . '</span>
            </div>';
    }
    
    $html .= '</div>';
    
    if ($student['catatan_verifikasi']) {
        $html .= '
        <div class="previous-verification">
            <h6>Catatan Verifikasi Sebelumnya:</h6>
            <div class="verification-notes">' . htmlspecialchars($student['catatan_verifikasi']) . '</div>
        </div>';
    }
    
    $html .= '</div>';
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getStatusClass($status) {
    switch ($status) {
        case 'pending':
            return 'status-pending';
        case 'verified':
            return 'status-verified';
        case 'rejected':
            return 'status-rejected';
        default:
            return 'status-pending';
    }
}

function getStatusText($status) {
    switch ($status) {
        case 'pending':
            return 'Menunggu Verifikasi';
        case 'verified':
            return 'Diverifikasi';
        case 'rejected':
            return 'Ditolak';
        default:
            return 'Menunggu Verifikasi';
    }
}
?> 