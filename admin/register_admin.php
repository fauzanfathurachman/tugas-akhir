<?php
// admin/register_admin.php
// Halaman registrasi admin baru (hanya untuk super_admin)

define('SECURE_ACCESS', true);
require_once '../config/config.php';
require_once __DIR__ . '/includes/validator.php';
require_once __DIR__ . '/includes/session.php';

SessionManager::start();


// Jika sudah login sebagai admin/super_admin, tetap bisa akses. Jika belum login, tetap bisa register (untuk pendaftaran admin pertama).

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = Validator::sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = Validator::sanitizeInput($_POST['email'] ?? '');
    $nama = Validator::sanitizeInput($_POST['nama'] ?? '');
    $role = $_POST['role'] ?? 'admin';
    $status = 'active';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!Validator::validateCsrf($csrf_token)) {
        $error = Validator::$errors[0] ?? 'Token CSRF tidak valid.';
    } elseif (empty($username) || empty($password) || empty($email) || empty($nama)) {
        $error = 'Semua field harus diisi!';
    } elseif (!Validator::validateEmail($email)) {
        $error = Validator::$errors[0] ?? 'Email tidak valid!';
    } else {
        try {
            $db = Database::getInstance();
            $existing = $db->fetchOne('SELECT id FROM users WHERE username = ? OR email = ?', [$username, $email]);
            if ($existing) {
                $error = 'Username atau email sudah terdaftar!';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $db->execute(
                    'INSERT INTO users (username, password, email, role, nama_lengkap, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())',
                    [$username, $hashed, $email, $role, $nama, $status]
                );
                $success = 'Admin baru berhasil didaftarkan!';
                // Auto-login admin baru dan redirect ke dashboard
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_role'] = $role;
                $_SESSION['login_time'] = time();
                // Ambil id user baru
                $user = $db->fetchOne('SELECT id FROM users WHERE username = ?', [$username]);
                if ($user && isset($user['id'])) {
                    $_SESSION['admin_id'] = $user['id'];
                }
                session_regenerate_id(true);
                header('Location: dashboard.php');
                exit();
            }
        } catch (Exception $e) {
            error_log('Register admin error: ' . $e->getMessage());
            $error = 'Terjadi kesalahan sistem.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Admin Baru</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div class="login-container" style="background: #fff; border-radius: 20px; box-shadow: 0 8px 32px rgba(80,80,160,0.15); padding: 2.5rem 2rem; max-width: 400px; width: 100%;">
        <div style="text-align:center; margin-bottom: 1.5rem;">
            <div style="font-size:2.5rem; color:#4f46e5; margin-bottom:0.5rem;"><i class="fas fa-user-plus"></i></div>
            <h2 style="margin-bottom:0.5rem; color:#1f2937;">Registrasi Admin Baru</h2>
            <p style="color:#6b7280; font-size:0.95rem;">Buat akun admin untuk mengelola sistem PSB</p>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-error" style="background:#fee2e2; color:#b91c1c; padding:0.75rem 1rem; border-radius:8px; margin-bottom:1rem; font-size:0.95rem;"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success" style="background:#d1fae5; color:#065f46; padding:0.75rem 1rem; border-radius:8px; margin-bottom:1rem; font-size:0.95rem;"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST" autocomplete="off" style="display:flex; flex-direction:column; gap:1rem;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(Validator::csrfToken()); ?>">
            <div class="form-group" style="display:flex; flex-direction:column; gap:0.25rem;">
                <label style="font-weight:500;">Username</label>
                <input type="text" name="username" required style="padding:0.5rem; border-radius:6px; border:1px solid #d1d5db;">
            </div>
            <div class="form-group" style="display:flex; flex-direction:column; gap:0.25rem;">
                <label style="font-weight:500;">Password</label>
                <input type="password" name="password" required style="padding:0.5rem; border-radius:6px; border:1px solid #d1d5db;">
            </div>
            <div class="form-group" style="display:flex; flex-direction:column; gap:0.25rem;">
                <label style="font-weight:500;">Email</label>
                <input type="email" name="email" required style="padding:0.5rem; border-radius:6px; border:1px solid #d1d5db;">
            </div>
            <div class="form-group" style="display:flex; flex-direction:column; gap:0.25rem;">
                <label style="font-weight:500;">Nama Lengkap</label>
                <input type="text" name="nama" required style="padding:0.5rem; border-radius:6px; border:1px solid #d1d5db;">
            </div>
            <div class="form-group" style="display:flex; flex-direction:column; gap:0.25rem;">
                <label style="font-weight:500;">Role</label>
                <select name="role" style="padding:0.5rem; border-radius:6px; border:1px solid #d1d5db;">
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            <button type="submit" style="background:#4f46e5; color:#fff; border:none; border-radius:8px; padding:0.75rem 0; font-weight:600; font-size:1rem; cursor:pointer; transition:background 0.2s;">Daftarkan Admin</button>
        </form>
        <div class="back-link" style="margin-top:1.5rem;">
            <a href="login.php" style="color:#4f46e5; text-decoration:none; font-weight:500;"><i class="fas fa-arrow-left"></i> Kembali ke Login</a>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>
</html>
