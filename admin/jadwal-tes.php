<?php
// Jadwal Tes Panel - Admin
require_once 'auth_check.php';
$page_title = 'Jadwal Tes';
$current_page = 'jadwal-tes';
include 'includes/header.php';
include 'includes/sidebar.php';
?>
<main class="main-content">
    <div class="container" style="max-width:900px;margin:0 auto;">
        <div class="page-header">
            <h1><i class="fas fa-calendar-alt"></i> Jadwal Tes</h1>
            <p class="page-subtitle">Atur dan lihat jadwal tes seleksi PSB</p>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="alert alert-info text-center" style="margin:40px 0;">
                    <i class="fas fa-info-circle"></i> Fitur jadwal tes belum tersedia. Silakan hubungi admin untuk pengaturan jadwal.
                </div>
            </div>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
