<?php
require_once 'auth_check.php';
$current_page = 'pendaftaran-list';
include 'includes/header.php';
include 'includes/sidebar.php';
require_once '../config/database.php';

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

$db = Database::getInstance();
$pendaftar = $db->fetchAll("SELECT * FROM calon_siswa ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pendaftar - Admin PSB MTs Ulul Albab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .student-photo { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e5e7eb; }
        .badge { font-size: 0.75rem; padding: 0.375rem 0.75rem; }
        .badge-warning { background-color: #fbbf24; color: #92400e; }
        .badge-success { background-color: #10b981; color: white; }
        .badge-danger { background-color: #ef4444; color: white; }
        .badge-info { background-color: #3b82f6; color: white; }
        .badge-secondary { background-color: #6b7280; color: white; }
        .card { border-radius: 16px; background: #fff; box-shadow: 0 4px 16px rgba(37,99,235,0.06); }
        .page-header h1 { color: #6366f1; font-weight: 700; }
    </style>
</head>
<body>
<main class="admin-main" style="min-height:100vh; background:#f4f6fb; padding-left:260px; padding-top:32px;">
    <div class="container" style="max-width:1200px; margin:0 auto;">
        <div class="page-header">
            <h1><i class="fas fa-list"></i> Daftar Pendaftar</h1>
            <p class="page-subtitle">Data seluruh pendaftar PSB</p>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Foto</th>
                                <th>Nama</th>
                                <th>NISN</th>
                                <th>Asal Sekolah</th>
                                <th>Status Verifikasi</th>
                                <th>Status Seleksi</th>
                                <th>Nomor Pendaftaran</th>
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($pendaftar) === 0): ?>
                            <tr>
                                <td colspan="9" style="text-align:center; color:#6b7280; font-size:1.1rem; padding:60px 0;">
                                    <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i> Belum ada data pendaftar untuk ditampilkan
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendaftar as $i => $row): ?>
                                <tr>
                                    <td><?= $i+1 ?></td>
                                    <td><img src="../uploads/foto/default.jpg" class="student-photo" alt="Foto"></td>
                                    <td><?= htmlspecialchars($row['nama_lengkap'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['nisn'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['asal_sekolah'] ?? '-') ?></td>
                                    <td><?= getStatusBadge($row['status_verifikasi'] ?? '') ?></td>
                                    <td><?= getStatusBadge($row['status_seleksi'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['nomor_pendaftaran'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars(isset($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
</body>
</html>
