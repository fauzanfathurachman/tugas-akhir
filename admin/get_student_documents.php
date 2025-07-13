<?php
require_once '../config/database.php';
// Tidak perlu cek login, langsung izinkan akses (untuk demo/tugas kecil)

header('Content-Type: application/json');

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID siswa tidak valid']);
    exit;
}

$id = (int)$_POST['id'];
$db = Database::getInstance();

try {
    // Get student data
    $query = "SELECT cs.*, p.status_verifikasi, p.status_seleksi, p.nomor_pendaftaran, 
                     p.tanggal_daftar, p.catatan_verifikasi, p.nilai_tes
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
    
    // Define documents
    $documents = [
        'foto' => [
            'title' => 'Foto (3x4)',
            'icon' => 'fas fa-image',
            'file' => $student['foto'],
            'type' => 'image',
            'required' => true
        ],
        'kk' => [
            'title' => 'Kartu Keluarga',
            'icon' => 'fas fa-id-card',
            'file' => $student['kartu_keluarga'],
            'type' => 'document',
            'required' => true
        ],
        'akta' => [
            'title' => 'Akta Kelahiran',
            'icon' => 'fas fa-certificate',
            'file' => $student['akta'],
            'type' => 'document',
            'required' => true
        ],
        'ijazah' => [
            'title' => 'Ijazah/SKL',
            'icon' => 'fas fa-graduation-cap',
            'file' => $student['ijazah'],
            'type' => 'document',
            'required' => true
        ]
    ];
    
    // Generate HTML
    $html = '
    <div class="student-info-header">
        <h4>' . htmlspecialchars($student['nama']) . '</h4>
        <p>NISN: ' . htmlspecialchars($student['nisn']) . ' | No. Daftar: ' . htmlspecialchars($student['nomor_pendaftaran'] ?? '-') . '</p>
    </div>
    
    <div class="document-grid">';
    
    foreach ($documents as $key => $doc) {
        $file_exists = !empty($doc['file']) && file_exists('../uploads/' . $key . '/' . $doc['file']);
        $status_class = $file_exists ? 'document-available' : 'document-missing';
        $status_text = $file_exists ? 'Tersedia' : 'Tidak ada';
        
        $html .= '
        <div class="document-card ' . $status_class . '">
            <div class="document-icon">
                <i class="' . $doc['icon'] . '"></i>
            </div>
            <div class="document-title">' . $doc['title'] . '</div>
            <div class="document-status">' . $status_text . '</div>';
        
        if ($file_exists) {
            $file_url = '../uploads/' . $key . '/' . $doc['file'];
            $html .= '
            <div class="document-actions">
                <button type="button" class="btn btn-sm btn-outline-primary view-document" 
                        data-url="' . $file_url . '" data-title="' . $doc['title'] . '">
                    <i class="fas fa-eye"></i> Lihat
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary download-document" 
                        data-url="' . $file_url . '" data-name="' . $doc['title'] . '_' . $student['nama'] . '.' . pathinfo($doc['file'], PATHINFO_EXTENSION) . '">
                    <i class="fas fa-download"></i> Download
                </button>
            </div>';
        } else {
            $html .= '
            <div class="document-actions">
                <span class="text-muted">Dokumen tidak tersedia</span>
            </div>';
        }
        
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    // Add download all button if any documents exist
    $has_documents = false;
    foreach ($documents as $doc) {
        if (!empty($doc['file']) && file_exists('../uploads/' . $key . '/' . $doc['file'])) {
            $has_documents = true;
            break;
        }
    }
    
    if ($has_documents) {
        $html .= '
        <div class="document-actions-footer">
            <button type="button" class="btn btn-primary download-all-documents" data-student-id="' . $student['id'] . '">
                <i class="fas fa-download"></i> Download Semua Dokumen
            </button>
            <button type="button" class="btn btn-secondary download-zip-documents" data-student-id="' . $student['id'] . '">
                <i class="fas fa-file-archive"></i> Download ZIP
            </button>
        </div>';
    }
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 