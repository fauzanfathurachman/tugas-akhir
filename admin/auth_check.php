<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tidak perlu cek login, langsung izinkan akses dashboard (untuk demo/tugas kecil)

// Check session timeout (8 hours)
$session_timeout = 8 * 60 * 60; // 8 hours in seconds
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $session_timeout) {
    // Session expired, redirect to login
    session_destroy();
    header('Location: login.php?expired=1');
    exit();
}

// Update login time on each request
$_SESSION['login_time'] = time();

// Get admin information
$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];
$admin_role = $_SESSION['admin_role'];

// Include database configuration
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
    require_once '../config/config.php';
}
?> 