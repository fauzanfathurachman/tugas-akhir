<?php
// activity-log.php
// Panel Log Aktivitas Admin PSB Online

require_once 'auth_check.php';
$page_title = 'Log Aktivitas';
$current_page = 'activity-log';

include 'includes/header.php';
include 'includes/sidebar.php';

// Ambil data log aktivitas dari database
try {
    $db = Database::getInstance();
    $logs = $db->fetchAll("SELECT al.*, u.username FROM activity_log al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 100");
} catch (Exception $e) {
    $logs = [];
}
?>

<main class="main-content">
    <div class="page-header">
        <h2>Log Aktivitas Admin</h2>
        <p>Riwayat aktivitas admin/operator PSB Online MTs Ulul Albab.</p>
    </div>
    <div class="panel-card">
        <div class="panel-card-header">
            <h3>Daftar Log Aktivitas</h3>
        </div>
        <div class="panel-card-body">
            <?php if (empty($logs)): ?>
                <div class="empty-data-message" style="text-align:center;padding:60px 0;color:#6b7280;font-size:1.1rem;">
                    <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i> Belum ada data log aktivitas untuk ditampilkan
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Aksi</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $i => $log): ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
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
