<?php
// laporan-pendaftaran.php
// Panel Laporan Pendaftaran Siswa - PSB Online

require_once 'auth_check.php';
$page_title = 'Laporan Pendaftaran';
$current_page = 'laporan-pendaftaran';

include 'includes/header.php';
include 'includes/sidebar.php';

// Ambil data pendaftaran dari database
try {
    $db = Database::getInstance();
    $pendaftar = $db->fetchAll("SELECT * FROM calon_siswa ORDER BY created_at DESC");
} catch (Exception $e) {
    $pendaftar = [];
}
?>

<main class="main-content">
    <div class="page-header">
        <h2>Laporan Pendaftaran Siswa Baru</h2>
        <p>Berikut adalah laporan data pendaftaran siswa baru MTs Ulul Albab.</p>
    </div>
    <div class="panel-card">
        <div class="panel-card-header">
            <h3>Daftar Pendaftar</h3>
        </div>
        <div class="panel-card-body">
            <?php if (empty($pendaftar)): ?>
                <div class="empty-data-message" style="text-align:center;padding:60px 0;color:#6b7280;font-size:1.1rem;">
                    <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i> Belum ada data pendaftaran untuk ditampilkan
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Lengkap</th>
                                <th>No. Pendaftaran</th>
                                <th>Status Verifikasi</th>
                                <th>Status Seleksi</th>
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendaftar as $i => $siswa): ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($siswa['nomor_pendaftaran']); ?></td>
                                    <td>
                                        <?php if ($siswa['status_verifikasi'] === 'verified'): ?>
                                            <span class="badge badge-success">Terverifikasi</span>
                                        <?php elseif ($siswa['status_verifikasi'] === 'pending'): ?>
                                            <span class="badge badge-warning">Menunggu</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Belum Ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($siswa['status_seleksi'] === 'lulus'): ?>
                                            <span class="badge badge-success">Lulus</span>
                                        <?php elseif ($siswa['status_seleksi'] === 'tidak lulus'): ?>
                                            <span class="badge badge-danger">Tidak Lulus</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Belum Ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($siswa['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
