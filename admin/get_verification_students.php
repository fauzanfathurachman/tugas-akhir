<?php
require_once '../config/database.php';
// Tidak perlu cek login, langsung izinkan akses (untuk demo/tugas kecil)

header('Content-Type: application/json');

$db = Database::getInstance();

try {
    $status = $_POST['status'] ?? 'pending';
    $search = trim($_POST['search'] ?? '');
    
    // Build query
    $where_conditions = [];
    $params = [];
    
    if ($status !== 'all') {
        $where_conditions[] = "cs.status_verifikasi = ?";
        $params[] = $status;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(cs.nama LIKE ? OR cs.nisn LIKE ? OR cs.nomor_daftar LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    $query = "SELECT cs.*, p.status_verifikasi, p.status_seleksi, p.nomor_pendaftaran, 
                     p.tanggal_daftar, p.catatan_verifikasi, p.nilai_tes
              FROM calon_siswa cs 
              LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id 
              $where_clause
              ORDER BY cs.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($students)) {
        echo json_encode([
            'success' => true,
            'html' => '<div class="no-selection">
                <i class="fas fa-search"></i>
                <h4>Tidak ada data</h4>
                <p>Tidak ada pendaftar yang ditemukan dengan kriteria yang dipilih</p>
            </div>'
        ]);
        exit;
    }
    
    // Generate HTML
    $html = '';
    foreach ($students as $student) {
        $status_class = '';
        $status_text = '';
        
        switch ($student['status_verifikasi']) {
            case 'pending':
                $status_class = 'status-pending';
                $status_text = 'Menunggu';
                break;
            case 'verified':
                $status_class = 'status-verified';
                $status_text = 'Diverifikasi';
                break;
            case 'rejected':
                $status_class = 'status-rejected';
                $status_text = 'Ditolak';
                break;
        }
        
        $html .= '
        <div class="student-item" data-id="' . $student['id'] . '">
            <input type="checkbox" class="student-checkbox">
            <div class="student-info">
                <div class="student-name">' . htmlspecialchars($student['nama']) . '</div>
                <div class="student-details">
                    NISN: ' . htmlspecialchars($student['nisn']) . ' | 
                    No. Daftar: ' . htmlspecialchars($student['nomor_daftar'] ?? '-') . '
                </div>
            </div>
            <div class="student-status ' . $status_class . '">' . $status_text . '</div>
            <button type="button" class="btn btn-sm btn-primary verify-btn">
                <i class="fas fa-check-circle"></i> Verifikasi
            </button>
        </div>';
    }
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 