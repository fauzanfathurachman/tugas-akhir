<?php
// hasil-seleksi.php
// Panel Hasil Seleksi PSB Online

$page_title = 'Hasil Seleksi';
$current_page = 'hasil-seleksi';
require_once 'auth_check.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Ambil data hasil seleksi dari database
try {
    $db = Database::getInstance();
    $hasil_seleksi = $db->fetchAll("SELECT * FROM calon_siswa WHERE status_seleksi IS NOT NULL ORDER BY updated_at DESC, created_at DESC");
} catch (Exception $e) {
    $hasil_seleksi = [];
}

$empty = empty($hasil_seleksi);
?>

<main class="main-content">
    <div class="panel-header">
        <h2>Hasil Seleksi</h2>
        <p>Daftar hasil seleksi penerimaan siswa baru MTs Ulul Albab</p>
    </div>
    <div class="panel-body">
        <?php if ($empty): ?>
            <div class="empty-data-message" style="text-align:center;padding:60px 0;color:#6b7280;font-size:1.1rem;">
                <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i> Belum ada data hasil seleksi untuk ditampilkan
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>No. Pendaftaran</th>
                            <th>Status Seleksi</th>
                            <th>Tanggal Seleksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hasil_seleksi as $i => $row): ?>
                            <tr>
                                <td><?php echo $i+1; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($row['nomor_pendaftaran']); ?></td>
                                <td>
                                    <?php if ($row['status_seleksi'] === 'lulus'): ?>
                                        <span class="badge badge-success">Lulus</span>
                                    <?php elseif ($row['status_seleksi'] === 'tidak lulus'): ?>
                                        <span class="badge badge-danger">Tidak Lulus</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Belum Ada</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['tanggal_seleksi'] ? date('d/m/Y', strtotime($row['tanggal_seleksi'])) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<style>
    .panel-header { margin-bottom: 2rem; }
    .panel-header h2 { color: #1f2937; font-size: 1.5rem; margin-bottom: 0.5rem; }
    .panel-header p { color: #6b7280; margin: 0; }
    .table-responsive { overflow-x: auto; }
    .table { width: 100%; border-collapse: collapse; }
    .table th, .table td { padding: 0.75rem 1rem; border-bottom: 1px solid #f3f4f6; text-align: left; }
    .table th { background: #f8fafc; color: #374151; font-weight: 600; }
    .badge { display: inline-block; padding: 0.3em 0.8em; border-radius: 8px; font-size: 0.9em; font-weight: 600; }
    .badge-success { background: #10b981; color: #fff; }
    .badge-danger { background: #ef4444; color: #fff; }
    .badge-secondary { background: #6b7280; color: #fff; }
    @media (max-width: 768px) {
        .panel-header { text-align: center; }
        .table th, .table td { padding: 0.5rem 0.5rem; font-size: 0.95em; }
    }
</style>
