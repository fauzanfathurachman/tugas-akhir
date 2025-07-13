<?php
// users.php
// Panel Manajemen Admin/User PSB Online

require_once 'auth_check.php';
$page_title = 'Manajemen Admin/User';
$current_page = 'users';

include 'includes/header.php';
include 'includes/sidebar.php';

// Ambil data user dari database
try {
    $db = Database::getInstance();
    $users = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");
} catch (Exception $e) {
    $users = [];
}
?>

<main class="main-content">
    <div class="page-header">
        <h2>Manajemen Admin/User</h2>
        <p>Kelola akun admin/operator PSB Online MTs Ulul Albab.</p>
    </div>
    <div class="panel-card">
        <div class="panel-card-header">
            <h3>Daftar Admin/User</h3>
        </div>
        <div class="panel-card-body">
            <?php if (empty($users)): ?>
                <div class="empty-data-message" style="text-align:center;padding:60px 0;color:#6b7280;font-size:1.1rem;">
                    <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i> Belum ada data admin/user untuk ditampilkan
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Tanggal Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $i => $user): ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['nama_lengkap'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($user['role'] ?? 'admin'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
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
