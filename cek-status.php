<?php
// Include database configuration
define('SECURE_ACCESS', true);
require_once 'config/config.php';

$status = null;
$error = null;
$student = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['input'] ?? '');
    $input_type = $_POST['input_type'] ?? 'nomor_daftar';
    
    if (empty($input)) {
        $error = 'Mohon masukkan nomor pendaftaran atau NISN';
    } else {
        try {
            $db = Database::getInstance();
            
            // Build query based on input type
            if ($input_type === 'nisn') {
                $query = "SELECT cs.*, p.status_pendaftaran, p.status_verifikasi, p.status_seleksi, 
                         p.tanggal_submit, p.jadwal_tes, p.hasil_tes, p.catatan, p.instruksi_selanjutnya,
                         p.verifikasi_kk, p.verifikasi_akta, p.verifikasi_ijazah, p.verifikasi_skhun,
                         p.verifikasi_foto, p.verifikasi_surat_sehat, p.verifikasi_surat_baik
                         FROM calon_siswa cs 
                         LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id 
                         WHERE cs.nisn = ?";
            } else {
                $query = "SELECT cs.*, p.status_pendaftaran, p.status_verifikasi, p.status_seleksi, 
                         p.tanggal_submit, p.jadwal_tes, p.hasil_tes, p.catatan, p.instruksi_selanjutnya,
                         p.verifikasi_kk, p.verifikasi_akta, p.verifikasi_ijazah, p.verifikasi_skhun,
                         p.verifikasi_foto, p.verifikasi_surat_sehat, p.verifikasi_surat_baik
                         FROM calon_siswa cs 
                         LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id 
                         WHERE cs.nomor_daftar = ?";
            }
            
            $student = $db->fetchOne($query, [$input]);
            
            if (!$student) {
                $error = $input_type === 'nisn' ? 'NISN tidak ditemukan' : 'Nomor pendaftaran tidak ditemukan';
            }
            
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem';
        }
    }
}

// Calculate progress percentage
function calculateProgress($student) {
    if (!$student) return 0;
    
    $steps = 0;
    $completed = 0;
    
    // Step 1: Registration submitted
    $steps++;
    if ($student['tanggal_submit']) $completed++;
    
    // Step 2: Documents verified
    $steps++;
    if ($student['status_verifikasi'] === 'verified') $completed++;
    
    // Step 3: Test scheduled
    $steps++;
    if ($student['jadwal_tes']) $completed++;
    
    // Step 4: Selection completed
    $steps++;
    if ($student['status_seleksi'] && $student['status_seleksi'] !== 'pending') $completed++;
    
    return round(($completed / $steps) * 100);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pendaftaran - MTs Ulul Albab</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-container {
            max-width: 800px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .status-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .status-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }
        
        .status-header h2 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .status-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .status-form {
            padding: 2rem;
            background: #f8fafc;
        }
        
        .form-tabs {
            display: flex;
            margin-bottom: 1.5rem;
            background: #fff;
            border-radius: 12px;
            padding: 0.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .form-tab {
            flex: 1;
            padding: 0.75rem 1rem;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .form-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        
        .form-tab:not(.active) {
            color: #6b7280;
        }
        
        .form-tab:not(.active):hover {
            background: #f3f4f6;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #fff;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            font-size: 1rem;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102,126,234,0.3);
        }
        
        .status-result {
            padding: 2rem;
        }
        
        .student-card {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid #e2e8f0;
        }
        
        .student-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .student-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 2rem;
            font-weight: bold;
            margin-right: 1.5rem;
        }
        
        .student-info h3 {
            margin: 0 0 0.5rem 0;
            color: #1f2937;
            font-size: 1.5rem;
        }
        
        .student-info p {
            margin: 0;
            color: #6b7280;
        }
        
        .progress-section {
            margin-bottom: 2rem;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .progress-title {
            font-weight: 600;
            color: #374151;
        }
        
        .progress-percentage {
            font-weight: 600;
            color: #667eea;
        }
        
        .progress-bar {
            width: 100%;
            height: 12px;
            background: #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 6px;
            transition: width 0.5s ease;
        }
        
        .timeline {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        
        .timeline-step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        
        .timeline-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e5e7eb;
            z-index: 1;
        }
        
        .timeline-step.completed:not(:last-child)::after {
            background: #667eea;
        }
        
        .timeline-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            position: relative;
            z-index: 2;
            transition: all 0.3s;
        }
        
        .timeline-step.completed .timeline-icon {
            background: #667eea;
            color: #fff;
        }
        
        .timeline-step.active .timeline-icon {
            background: #667eea;
            color: #fff;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.2);
        }
        
        .timeline-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
        }
        
        .timeline-step.completed .timeline-label,
        .timeline-step.active .timeline-label {
            color: #374151;
        }
        
        .status-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .status-card h4 {
            margin: 0 0 1rem 0;
            color: #374151;
            display: flex;
            align-items: center;
        }
        
        .status-card h4 i {
            margin-right: 0.5rem;
            color: #667eea;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-verified {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-approved {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .status-lulus {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-tidak-lulus {
            background: #fef2f2;
            color: #dc2626;
        }
        
        .document-checklist {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .document-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-radius: 8px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        
        .document-item.verified {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }
        
        .document-item.rejected {
            background: #fef2f2;
            border-color: #fecaca;
        }
        
        .document-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            font-size: 0.75rem;
        }
        
        .document-item.verified .document-icon {
            background: #22c55e;
            color: #fff;
        }
        
        .document-item.rejected .document-icon {
            background: #ef4444;
            color: #fff;
        }
        
        .document-item.pending .document-icon {
            background: #f59e0b;
            color: #fff;
        }
        
        .document-name {
            flex: 1;
            font-weight: 500;
            color: #374151;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .info-item {
            padding: 1rem;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .info-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-weight: 600;
            color: #374151;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: #fff;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-1px);
        }
        
        .btn-secondary i {
            margin-right: 0.5rem;
        }
        
        .btn-success {
            background: #22c55e;
            color: #fff;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-success:hover {
            background: #16a34a;
            transform: translateY(-1px);
        }
        
        .btn-success i {
            margin-right: 0.5rem;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: #5a67d8;
            text-decoration: underline;
        }
        
        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .status-container {
                margin: 1rem;
                border-radius: 16px;
            }
            
            .status-header {
                padding: 2rem 1rem;
            }
            
            .status-header h2 {
                font-size: 2rem;
            }
            
            .student-header {
                flex-direction: column;
                text-align: center;
            }
            
            .student-avatar {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .timeline {
                flex-direction: column;
                gap: 1rem;
            }
            
            .timeline-step:not(:last-child)::after {
                display: none;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-flex">
            <div class="logo">
                <img src="assets/images/logo.png" alt="Logo MTs Ulul Albab" />
                <span>MTs Ulul Albab</span>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="pendaftaran.php">Pendaftaran</a></li>
                    <li><a href="pengumuman.php">Pengumuman</a></li>
                    <li><a href="cek-status.php" class="active">Cek Status</a></li>
                    <li><a href="admin/login.php" class="btn-login">Login Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="status-container">
        <div class="status-header">
            <h2><i class="fas fa-search"></i> Cek Status Pendaftaran</h2>
            <p>Masukkan nomor pendaftaran atau NISN untuk melihat status lengkap pendaftaran Anda</p>
        </div>

        <div class="status-form">
            <form method="POST">
                <div class="form-tabs">
                    <div class="form-tab active" data-type="nomor_daftar">
                        <i class="fas fa-id-card"></i> Nomor Pendaftaran
                    </div>
                    <div class="form-tab" data-type="nisn">
                        <i class="fas fa-user-graduate"></i> NISN
                    </div>
                </div>
                
                <input type="hidden" name="input_type" id="input_type" value="nomor_daftar">
                
                <div class="form-group">
                    <label for="input" id="input_label">Nomor Pendaftaran</label>
                    <input type="text" id="input" name="input" 
                           placeholder="Contoh: PSB-2024-001" 
                           value="<?php echo htmlspecialchars($_POST['input'] ?? ''); ?>" required>
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-search"></i> Cek Status
                </button>
            </form>
        </div>

        <?php if ($error): ?>
            <div class="status-result">
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($student): ?>
            <div class="status-result">
                <!-- Student Information Card -->
                <div class="student-card">
                    <div class="student-header">
                        <div class="student-avatar">
                            <?php echo strtoupper(substr($student['nama_lengkap'], 0, 1)); ?>
                        </div>
                        <div class="student-info">
                            <h3><?php echo htmlspecialchars($student['nama_lengkap']); ?></h3>
                            <p><strong>Nomor Pendaftaran:</strong> <?php echo htmlspecialchars($student['nomor_daftar']); ?></p>
                            <p><strong>NISN:</strong> <?php echo htmlspecialchars($student['nisn']); ?></p>
                        </div>
                    </div>
                    
                    <!-- Progress Section -->
                    <div class="progress-section">
                        <div class="progress-header">
                            <span class="progress-title">Progress Pendaftaran</span>
                            <span class="progress-percentage"><?php echo calculateProgress($student); ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo calculateProgress($student); ?>%"></div>
                        </div>
                        
                        <!-- Timeline -->
                        <div class="timeline">
                            <div class="timeline-step <?php echo $student['tanggal_submit'] ? 'completed' : 'active'; ?>">
                                <div class="timeline-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div class="timeline-label">Pendaftaran</div>
                            </div>
                            <div class="timeline-step <?php echo $student['status_verifikasi'] === 'verified' ? 'completed' : ($student['status_verifikasi'] === 'rejected' ? 'active' : ''); ?>">
                                <div class="timeline-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="timeline-label">Verifikasi</div>
                            </div>
                            <div class="timeline-step <?php echo $student['jadwal_tes'] ? 'completed' : ($student['status_verifikasi'] === 'verified' ? 'active' : ''); ?>">
                                <div class="timeline-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="timeline-label">Tes</div>
                            </div>
                            <div class="timeline-step <?php echo $student['status_seleksi'] && $student['status_seleksi'] !== 'pending' ? 'completed' : ($student['jadwal_tes'] ? 'active' : ''); ?>">
                                <div class="timeline-icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div class="timeline-label">Hasil</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Verification Status -->
                <div class="status-card">
                    <h4><i class="fas fa-file-alt"></i> Status Verifikasi Berkas</h4>
                    
                    <?php
                    $documents = [
                        'verifikasi_kk' => 'Kartu Keluarga',
                        'verifikasi_akta' => 'Akta Kelahiran',
                        'verifikasi_ijazah' => 'Ijazah SD/MI',
                        'verifikasi_skhun' => 'SKHUN',
                        'verifikasi_foto' => 'Foto 3x4',
                        'verifikasi_surat_sehat' => 'Surat Sehat',
                        'verifikasi_surat_baik' => 'Surat Kelakuan Baik'
                    ];
                    
                    $verified_count = 0;
                    $total_documents = count($documents);
                    ?>
                    
                    <div class="document-checklist">
                        <?php foreach ($documents as $field => $name): ?>
                            <?php 
                            $status = $student[$field] ?? 'pending';
                            if ($status === 'verified') $verified_count++;
                            ?>
                            <div class="document-item <?php echo $status; ?>">
                                <div class="document-icon">
                                    <?php if ($status === 'verified'): ?>
                                        <i class="fas fa-check"></i>
                                    <?php elseif ($status === 'rejected'): ?>
                                        <i class="fas fa-times"></i>
                                    <?php else: ?>
                                        <i class="fas fa-clock"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="document-name"><?php echo $name; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="margin-top: 1rem; padding: 0.75rem; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #0ea5e9;">
                        <strong>Progress Verifikasi:</strong> <?php echo $verified_count; ?> dari <?php echo $total_documents; ?> berkas terverifikasi
                    </div>
                </div>

                <!-- Registration Status -->
                <div class="status-card">
                    <h4><i class="fas fa-info-circle"></i> Status Pendaftaran</h4>
                    
                    <?php
                    $statusClass = 'status-pending';
                    $statusText = 'Menunggu Verifikasi';
                    $statusIcon = 'fas fa-clock';
                    
                    if ($student['status_verifikasi'] === 'verified') {
                        $statusClass = 'status-verified';
                        $statusText = 'Berkas Terverifikasi';
                        $statusIcon = 'fas fa-check-circle';
                    } elseif ($student['status_verifikasi'] === 'rejected') {
                        $statusClass = 'status-rejected';
                        $statusText = 'Berkas Ditolak';
                        $statusIcon = 'fas fa-times-circle';
                    }
                    
                    if ($student['status_pendaftaran'] === 'approved') {
                        $statusClass = 'status-approved';
                        $statusText = 'Pendaftaran Diterima';
                        $statusIcon = 'fas fa-thumbs-up';
                    }
                    ?>
                    
                    <div class="status-badge <?php echo $statusClass; ?>">
                        <i class="<?php echo $statusIcon; ?>"></i> <?php echo $statusText; ?>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Tanggal Daftar</div>
                            <div class="info-value">
                                <?php echo $student['tanggal_submit'] ? date('d/m/Y H:i', strtotime($student['tanggal_submit'])) : 'Belum disubmit'; ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Status Verifikasi</div>
                            <div class="info-value">
                                <?php 
                                switch($student['status_verifikasi']) {
                                    case 'pending': echo 'Menunggu Verifikasi'; break;
                                    case 'verified': echo 'Terverifikasi'; break;
                                    case 'rejected': echo 'Ditolak'; break;
                                    default: echo 'Tidak diketahui';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Status Seleksi</div>
                            <div class="info-value">
                                <?php 
                                switch($student['status_seleksi']) {
                                    case 'pending': echo 'Menunggu Seleksi'; break;
                                    case 'lulus': echo 'Lulus'; break;
                                    case 'tidak_lulus': echo 'Tidak Lulus'; break;
                                    default: echo 'Tidak diketahui';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Hasil Tes</div>
                            <div class="info-value">
                                <?php echo $student['hasil_tes'] ? htmlspecialchars($student['hasil_tes']) : 'Belum ada'; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Schedule -->
                <?php if ($student['jadwal_tes']): ?>
                <div class="status-card">
                    <h4><i class="fas fa-calendar-alt"></i> Jadwal Tes</h4>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Tanggal Tes</div>
                            <div class="info-value">
                                <?php echo date('d/m/Y', strtotime($student['jadwal_tes'])); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Waktu Tes</div>
                            <div class="info-value">
                                <?php echo date('H:i', strtotime($student['jadwal_tes'])); ?> WIB
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Lokasi</div>
                            <div class="info-value">
                                MTs Ulul Albab
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Yang Dibawa</div>
                            <div class="info-value">
                                Kartu Peserta, Alat Tulis
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($student['status_verifikasi'] === 'verified'): ?>
                    <div class="action-buttons">
                        <a href="#" class="btn-success" onclick="downloadKartuPeserta()">
                            <i class="fas fa-download"></i> Download Kartu Peserta
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Selection Results -->
                <?php if ($student['status_seleksi'] && $student['status_seleksi'] !== 'pending'): ?>
                <div class="status-card">
                    <h4><i class="fas fa-trophy"></i> Hasil Seleksi</h4>
                    
                    <?php
                    $resultClass = $student['status_seleksi'] === 'lulus' ? 'status-lulus' : 'status-tidak-lulus';
                    $resultText = $student['status_seleksi'] === 'lulus' ? 'LULUS SELEKSI' : 'TIDAK LULUS';
                    $resultIcon = $student['status_seleksi'] === 'lulus' ? 'fas fa-trophy' : 'fas fa-times-circle';
                    ?>
                    
                    <div class="status-badge <?php echo $resultClass; ?>">
                        <i class="<?php echo $resultIcon; ?>"></i> <?php echo $resultText; ?>
                    </div>
                    
                    <?php if ($student['hasil_tes']): ?>
                    <div class="info-item" style="margin-top: 1rem;">
                        <div class="info-label">Nilai Tes</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['hasil_tes']); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($student['instruksi_selanjutnya']): ?>
                    <div style="margin-top: 1rem; padding: 1rem; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #22c55e;">
                        <strong>Instruksi Selanjutnya:</strong><br>
                        <?php echo nl2br(htmlspecialchars($student['instruksi_selanjutnya'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Notes (if rejected) -->
                <?php if ($student['status_verifikasi'] === 'rejected' && !empty($student['catatan'])): ?>
                <div class="status-card">
                    <h4><i class="fas fa-exclamation-triangle"></i> Catatan</h4>
                    <div style="padding: 1rem; background: #fef2f2; border-radius: 8px; border-left: 4px solid #ef4444;">
                        <?php echo nl2br(htmlspecialchars($student['catatan'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="action-buttons">
                    <a href="index.php" class="btn-secondary">
                        <i class="fas fa-home"></i> Kembali ke Beranda
                    </a>
                    <a href="pendaftaran.php" class="btn-secondary">
                        <i class="fas fa-edit"></i> Edit Pendaftaran
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Tab switching
        document.querySelectorAll('.form-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.form-tab').forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Update hidden input and label
                const type = this.dataset.type;
                document.getElementById('input_type').value = type;
                
                const input = document.getElementById('input');
                const label = document.getElementById('input_label');
                
                if (type === 'nisn') {
                    label.textContent = 'NISN';
                    input.placeholder = 'Contoh: 12345678';
                } else {
                    label.textContent = 'Nomor Pendaftaran';
                    input.placeholder = 'Contoh: PSB-2024-001';
                }
            });
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const input = document.getElementById('input').value.trim();
            const inputType = document.getElementById('input_type').value;
            
            if (!input) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Input Kosong',
                    text: 'Mohon masukkan nomor pendaftaran atau NISN Anda'
                });
                return;
            }
            
            // Validate format based on input type
            if (inputType === 'nomor_daftar') {
                if (!/^PSB-\d{4}-\d{3}$/.test(input)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Format Nomor Pendaftaran Salah',
                        text: 'Format yang benar: PSB-YYYY-XXX (contoh: PSB-2024-001)'
                    });
                    return;
                }
            } else if (inputType === 'nisn') {
                if (!/^\d{8,10}$/.test(input)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Format NISN Salah',
                        text: 'NISN harus berupa angka dengan 8-10 digit'
                    });
                    return;
                }
            }
        });

        // Download kartu peserta function
        function downloadKartuPeserta() {
            Swal.fire({
                icon: 'info',
                title: 'Download Kartu Peserta',
                text: 'Fitur download kartu peserta akan segera tersedia',
                confirmButtonText: 'OK'
            });
        }

        // Animate progress bar on load
        document.addEventListener('DOMContentLoaded', function() {
            const progressFill = document.querySelector('.progress-fill');
            if (progressFill) {
                const width = progressFill.style.width;
                progressFill.style.width = '0%';
                setTimeout(() => {
                    progressFill.style.width = width;
                }, 500);
            }
        });
    </script>
</body>
</html> 