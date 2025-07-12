<?php
// Include database configuration
define('SECURE_ACCESS', true);
require_once 'config/config.php';

$status = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_daftar = trim($_POST['nomor_daftar'] ?? '');
    
    if (empty($nomor_daftar)) {
        $error = 'Nomor pendaftaran harus diisi';
    } else {
        try {
            $db = Database::getInstance();
            
            // Get registration status
            $status = $db->fetchOne(
                "SELECT cs.*, p.status_pendaftaran, p.tanggal_submit 
                 FROM calon_siswa cs 
                 LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id 
                 WHERE cs.nomor_daftar = ?",
                [$nomor_daftar]
            );
            
            if (!$status) {
                $error = 'Nomor pendaftaran tidak ditemukan';
            }
            
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem';
        }
    }
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
    <style>
        .status-container {
            max-width: 600px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .status-header {
            background: linear-gradient(90deg, #2563eb 0%, #06b6d4 100%);
            color: #fff;
            padding: 2rem;
            text-align: center;
        }
        
        .status-form {
            padding: 2rem;
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
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        
        .btn {
            background: linear-gradient(90deg, #2563eb 0%, #06b6d4 100%);
            color: #fff;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
        }
        
        .status-result {
            padding: 2rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .status-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
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
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #374151;
        }
        
        .info-value {
            color: #6b7280;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
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
                    <li><a href="#pengumuman">Pengumuman</a></li>
                    <li><a href="cek-status.php" class="active">Cek Status</a></li>
                    <li><a href="admin/login.php" class="btn-login">Login Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="status-container">
        <div class="status-header">
            <h2>Cek Status Pendaftaran</h2>
            <p>Masukkan nomor pendaftaran Anda untuk melihat status pendaftaran</p>
        </div>

        <div class="status-form">
            <form method="POST">
                <div class="form-group">
                    <label for="nomor_daftar">Nomor Pendaftaran</label>
                    <input type="text" id="nomor_daftar" name="nomor_daftar" 
                           placeholder="Contoh: PSB-2024-001" 
                           value="<?php echo htmlspecialchars($_POST['nomor_daftar'] ?? ''); ?>" required>
                </div>
                <button type="submit" class="btn">Cek Status</button>
            </form>
        </div>

        <?php if ($error): ?>
            <div class="status-result">
                <div class="status-card">
                    <div style="color: #dc2626; text-align: center;">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($status): ?>
            <div class="status-result">
                <div class="status-card">
                    <h3>Status Pendaftaran</h3>
                    
                    <?php
                    $statusClass = 'status-pending';
                    $statusText = 'Menunggu Verifikasi';
                    
                    if ($status['status_verifikasi'] === 'verified') {
                        $statusClass = 'status-verified';
                        $statusText = 'Terverifikasi';
                    } elseif ($status['status_verifikasi'] === 'rejected') {
                        $statusClass = 'status-rejected';
                        $statusText = 'Ditolak';
                    }
                    
                    if ($status['status_pendaftaran'] === 'approved') {
                        $statusClass = 'status-approved';
                        $statusText = 'Diterima';
                    }
                    ?>
                    
                    <div class="status-badge <?php echo $statusClass; ?>">
                        <?php echo $statusText; ?>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Nomor Pendaftaran:</span>
                        <span class="info-value"><?php echo htmlspecialchars($status['nomor_daftar']); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Nama Lengkap:</span>
                        <span class="info-value"><?php echo htmlspecialchars($status['nama_lengkap']); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Tanggal Daftar:</span>
                        <span class="info-value">
                            <?php echo date('d/m/Y H:i', strtotime($status['created_at'])); ?>
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Status Verifikasi:</span>
                        <span class="info-value">
                            <?php 
                            switch($status['status_verifikasi']) {
                                case 'pending': echo 'Menunggu Verifikasi'; break;
                                case 'verified': echo 'Terverifikasi'; break;
                                case 'rejected': echo 'Ditolak'; break;
                                default: echo 'Tidak diketahui';
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Status Seleksi:</span>
                        <span class="info-value">
                            <?php 
                            switch($status['status_seleksi']) {
                                case 'pending': echo 'Menunggu Seleksi'; break;
                                case 'lulus': echo 'Lulus'; break;
                                case 'tidak_lulus': echo 'Tidak Lulus'; break;
                                default: echo 'Tidak diketahui';
                            }
                            ?>
                        </span>
                    </div>
                    
                    <?php if ($status['status_verifikasi'] === 'rejected' && !empty($status['catatan'])): ?>
                        <div class="info-row">
                            <span class="info-label">Catatan:</span>
                            <span class="info-value"><?php echo htmlspecialchars($status['catatan']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <a href="index.php" class="back-link">← Kembali ke Beranda</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const nomorDaftar = document.getElementById('nomor_daftar').value.trim();
            
            if (!nomorDaftar) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Nomor Pendaftaran Kosong',
                    text: 'Mohon masukkan nomor pendaftaran Anda'
                });
                return;
            }
            
            // Validate format (PSB-YYYY-XXX)
            if (!/^PSB-\d{4}-\d{3}$/.test(nomorDaftar)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Format Nomor Pendaftaran Salah',
                    text: 'Format yang benar: PSB-YYYY-XXX (contoh: PSB-2024-001)'
                });
                return;
            }
        });
    </script>
</body>
</html> 