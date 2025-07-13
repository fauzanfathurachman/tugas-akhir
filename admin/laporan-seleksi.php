<?php
// laporan-seleksi.php
// Panel Laporan Seleksi Siswa - PSB Online

require_once 'auth_check.php';
$page_title = 'Laporan Seleksi';
$current_page = 'laporan-seleksi';

include 'includes/header.php';
include 'includes/sidebar.php';

// Ambil data hasil seleksi dari database
try {
    $db = Database::getInstance();
    $hasil_seleksi = $db->fetchAll("SELECT * FROM calon_siswa WHERE status_seleksi IS NOT NULL ORDER BY updated_at DESC, created_at DESC");
} catch (Exception $e) {
    $hasil_seleksi = [];
}
?>

<main class="main-content">
    <div class="page-header">
        <h2>Laporan Hasil Seleksi Siswa Baru</h2>
        <p>Berikut adalah laporan hasil seleksi penerimaan siswa baru MTs Ulul Albab.</p>
    </div>
    <div class="panel-card">
        <div class="panel-card-header">
            <h3>Daftar Hasil Seleksi</h3>
        </div>
        <div class="panel-card-body">
            <?php if (empty($hasil_seleksi)): ?>
                <div class="empty-data-message" style="text-align:center;padding:60px 0;color:#6b7280;font-size:1.1rem;">
                    <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i> Belum ada data hasil seleksi untuk ditampilkan
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
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
                            <?php foreach ($hasil_seleksi as $i => $siswa): ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($siswa['nomor_pendaftaran']); ?></td>
                                    <td>
                                        <?php if ($siswa['status_seleksi'] === 'lulus'): ?>
                                            <span class="badge badge-success">Lulus</span>
                                        <?php elseif ($siswa['status_seleksi'] === 'tidak lulus'): ?>
                                            <span class="badge badge-danger">Tidak Lulus</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Belum Ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $siswa['tanggal_seleksi'] ? date('d/m/Y', strtotime($siswa['tanggal_seleksi'])) : '-'; ?></td>
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
