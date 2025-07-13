<?php
// pengaturan.php
// Panel Pengaturan Sistem PSB Online

require_once 'auth_check.php';
$page_title = 'Pengaturan';
$current_page = 'pengaturan';

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">
    <div class="page-header">
        <h2>Pengaturan Sistem</h2>
        <p>Kelola pengaturan aplikasi PSB Online MTs Ulul Albab.</p>
    </div>
    <div class="panel-card">
        <div class="panel-card-header">
            <h3>Pengaturan Umum</h3>
        </div>
        <div class="panel-card-body">
            <div class="empty-data-message" style="text-align:center;padding:60px 0;color:#6b7280;font-size:1.1rem;">
                <i class="fas fa-cog" style="margin-right: 0.5rem;"></i> Fitur pengaturan akan segera tersedia.
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
