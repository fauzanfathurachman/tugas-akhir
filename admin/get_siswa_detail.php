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
    
    // Format the HTML for modal
    $html = '
    <div class="student-detail-grid">
        <div class="detail-section">
            <h6><i class="fas fa-user"></i> Data Pribadi</h6>
            <div class="detail-item">
                <span class="detail-label">Nama Lengkap:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['nama']) . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">NISN:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['nisn']) . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Tempat Lahir:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['tempat_lahir'] ?: '-') . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Tanggal Lahir:</span>
                <span class="detail-value">' . ($siswa['tanggal_lahir'] ? date('d/m/Y', strtotime($siswa['tanggal_lahir'])) : '-') . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Jenis Kelamin:</span>
                <span class="detail-value">' . ($siswa['jenis_kelamin'] === 'L' ? 'Laki-laki' : ($siswa['jenis_kelamin'] === 'P' ? 'Perempuan' : '-')) . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Agama:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['agama'] ?: '-') . '</span>
            </div>
        </div>
        
        <div class="detail-section">
            <h6><i class="fas fa-map-marker-alt"></i> Data Kontak</h6>
            <div class="detail-item">
                <span class="detail-label">Alamat:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['alamat'] ?: '-') . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">No. Telepon:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['telepon'] ?: '-') . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Email:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['email'] ?: '-') . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Asal Sekolah:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['asal_sekolah'] ?: '-') . '</span>
            </div>
        </div>
        
        <div class="detail-section">
            <h6><i class="fas fa-users"></i> Data Orang Tua</h6>
            <div class="detail-item">
                <span class="detail-label">Nama Ayah:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['nama_ayah'] ?: '-') . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Pekerjaan Ayah:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['pekerjaan_ayah'] ?: '-') . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Pendapatan Ayah:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['pendapatan_ayah'] ?: '-') . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Nama Ibu:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['nama_ibu'] ?: '-') . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Pekerjaan Ibu:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['pekerjaan_ibu'] ?: '-') . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Pendapatan Ibu:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['pendapatan_ibu'] ?: '-') . '</span>
            </div>
        </div>
        
        <div class="detail-section">
            <h6><i class="fas fa-clipboard-check"></i> Status Pendaftaran</h6>
            <div class="detail-item">
                <span class="detail-label">Nomor Pendaftaran:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['nomor_pendaftaran'] ?: '-') . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Tanggal Daftar:</span>
                <span class="detail-value">' . ($siswa['tanggal_daftar'] ? date('d/m/Y H:i', strtotime($siswa['tanggal_daftar'])) : '-') . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Status Verifikasi:</span>
                <span class="detail-value">' . getStatusBadge($siswa['status_verifikasi']) . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Status Seleksi:</span>
                <span class="detail-value">' . getStatusBadge($siswa['status_seleksi']) . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Nilai Tes:</span>
                <span class="detail-value">' . ($siswa['nilai_tes'] ?: '-') . '</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Catatan Verifikasi:</span>
                <span class="detail-value">' . htmlspecialchars($siswa['catatan_verifikasi'] ?: '-') . '</span>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="detail-section">
                <h6><i class="fas fa-file-alt"></i> Dokumen</h6>
                <div class="detail-item">
                    <span class="detail-label">Foto:</span>
                    <span class="detail-value">
                        <a href="../uploads/foto/' . ($siswa['foto'] ?: 'default.jpg') . '" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> Lihat Foto
                        </a>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">KK:</span>
                    <span class="detail-value">
                        <a href="../uploads/kk/' . ($siswa['kk'] ?: 'default.pdf') . '" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-file-pdf"></i> Lihat KK
                        </a>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Akta:</span>
                    <span class="detail-value">
                        <a href="../uploads/akta/' . ($siswa['akta'] ?: 'default.pdf') . '" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-file-pdf"></i> Lihat Akta
                        </a>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Ijazah:</span>
                    <span class="detail-value">
                        <a href="../uploads/ijazah/' . ($siswa['ijazah'] ?: 'default.pdf') . '" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-file-pdf"></i> Lihat Ijazah
                        </a>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="detail-section">
                <h6><i class="fas fa-chart-line"></i> Statistik</h6>
                <div class="detail-item">
                    <span class="detail-label">Tanggal Dibuat:</span>
                    <span class="detail-value">' . date('d/m/Y H:i', strtotime($siswa['created_at'])) . '</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Terakhir Diupdate:</span>
                    <span class="detail-value">' . date('d/m/Y H:i', strtotime($siswa['updated_at'])) . '</span>
                </div>
            </div>
        </div>
    </div>';
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getStatusBadge($status) {
    $badges = [
        'Menunggu' => '<span class="badge badge-warning">Menunggu</span>',
        'Diverifikasi' => '<span class="badge badge-success">Diverifikasi</span>',
        'Ditolak' => '<span class="badge badge-danger">Ditolak</span>',
        'Diterima' => '<span class="badge badge-success">Diterima</span>',
        'Cadangan' => '<span class="badge badge-info">Cadangan</span>',
        'Tidak Diterima' => '<span class="badge badge-danger">Tidak Diterima</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">-</span>';
}
?> 