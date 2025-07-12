<?php
// Start session
session_start();

// Include database configuration
define('SECURE_ACCESS', true);
require_once '../config/config.php';

// Check if user is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

// Check remember me cookie
if (!isset($_SESSION['admin_logged_in']) && isset($_COOKIE['admin_remember'])) {
    try {
        $db = Database::getInstance();
        $token = $_COOKIE['admin_remember'];
        
        // Verify remember token
        $user = $db->fetchOne(
            "SELECT id, username, role FROM users WHERE remember_token = ? AND remember_expires > NOW()",
            [$token]
        );
        
        if ($user) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            header('Location: dashboard.php');
            exit();
        }
    } catch (Exception $e) {
        // Clear invalid cookie
        setcookie('admin_remember', '', time() - 3600, '/', '', true, true);
    }
}

$error = '';
$success = '';

// Generate captcha
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = rand(1000, 9999);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha = trim($_POST['captcha'] ?? '');
    $remember = isset($_POST['remember']);
    
    // Validate input
    if (empty($username) || empty($password) || empty($captcha)) {
        $error = 'Semua field harus diisi';
    } elseif ($captcha != $_SESSION['captcha']) {
        $error = 'Kode captcha salah';
        $_SESSION['captcha'] = rand(1000, 9999); // Regenerate captcha
    } else {
        try {
            $db = Database::getInstance();
            
            // Get user by username
            $user = $db->fetchOne(
                "SELECT id, username, password, role, status, last_login, login_attempts, locked_until 
                 FROM users WHERE username = ? AND role IN ('admin', 'super_admin')",
                [$username]
            );
            
            if (!$user) {
                $error = 'Username atau password salah';
            } elseif ($user['status'] !== 'active') {
                $error = 'Akun tidak aktif';
            } elseif ($user['locked_until'] && $user['locked_until'] > date('Y-m-d H:i:s')) {
                $error = 'Akun terkunci. Silakan coba lagi nanti.';
            } else {
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Reset login attempts on successful login
                    $db->execute(
                        "UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?",
                        [$user['id']]
                    );
                    
                    // Set session
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Handle remember me
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                        
                        $db->execute(
                            "UPDATE users SET remember_token = ?, remember_expires = ? WHERE id = ?",
                            [$token, $expires, $user['id']]
                        );
                        
                        // Set secure cookie
                        setcookie('admin_remember', $token, strtotime('+30 days'), '/', '', true, true);
                    }
                    
                    // Log successful login
                    $db->execute(
                        "INSERT INTO activity_log (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)",
                        [$user['id'], 'login', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]
                    );
                    
                    header('Location: dashboard.php');
                    exit();
                    
                } else {
                    // Increment login attempts
                    $attempts = $user['login_attempts'] + 1;
                    $locked_until = null;
                    
                    if ($attempts >= 5) {
                        $locked_until = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                        $error = 'Terlalu banyak percobaan login. Akun terkunci selama 15 menit.';
                    } else {
                        $error = 'Username atau password salah';
                    }
                    
                    $db->execute(
                        "UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?",
                        [$attempts, $locked_until, $user['id']]
                    );
                    
                    // Regenerate captcha
                    $_SESSION['captcha'] = rand(1000, 9999);
                }
            }
            
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem';
        }
    }
}

// Regenerate captcha on page load for security
if (!isset($_POST['username'])) {
    $_SESSION['captcha'] = rand(1000, 9999);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MTs Ulul Albab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .login-header h1 {
            color: #1f2937;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #6b7280;
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            transition: color 0.3s;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.8);
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: white;
        }
        
        .form-control:focus + i {
            color: #667eea;
        }
        
        .captcha-group {
            display: flex;
            gap: 1rem;
            align-items: flex-end;
        }
        
        .captcha-group .form-group {
            flex: 1;
        }
        
        .captcha-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
            min-width: 100px;
            cursor: pointer;
            user-select: none;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            transition: transform 0.2s;
        }
        
        .captcha-display:hover {
            transform: scale(1.05);
        }
        
        .captcha-display:active {
            transform: scale(0.95);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 0.75rem;
            accent-color: #667eea;
        }
        
        .checkbox-group label {
            margin: 0;
            font-size: 0.9rem;
            color: #6b7280;
            cursor: pointer;
        }
        
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-login .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .btn-login.loading .spinner {
            display: inline-block;
        }
        
        .btn-login.loading span {
            display: none;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
        
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        
        .alert i {
            margin-right: 0.5rem;
        }
        
        .back-link {
            text-align: center;
            margin-top: 2rem;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .back-link a:hover {
            color: #5a67d8;
            text-decoration: underline;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 2rem;
            color: #9ca3af;
            font-size: 0.8rem;
        }
        
        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }
        
        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        /* Responsive design */
        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
                padding: 2rem;
                border-radius: 16px;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
            
            .captcha-group {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .captcha-display {
                min-width: auto;
            }
        }
        
        /* Animation for form elements */
        .form-group {
            animation: slideUp 0.6s ease-out;
        }
        
        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
        .form-group:nth-child(5) { animation-delay: 0.5s; }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>Admin Login</h1>
            <p>MTs Ulul Albab - Panel Administrasi</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-group">
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="Masukkan username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    <i class="fas fa-user"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Masukkan password" required>
                    <i class="fas fa-lock"></i>
                </div>
            </div>

            <div class="captcha-group">
                <div class="form-group">
                    <label for="captcha">Kode Keamanan</label>
                    <input type="text" id="captcha" name="captcha" class="form-control" 
                           placeholder="Masukkan kode" maxlength="4" required>
                </div>
                <div class="captcha-display" id="captchaDisplay" title="Klik untuk refresh">
                    <?php echo $_SESSION['captcha']; ?>
                </div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat saya selama 30 hari</label>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <div class="spinner"></div>
                <span>Masuk ke Panel Admin</span>
            </button>
        </form>

        <div class="back-link">
            <a href="../index.php">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>

        <div class="footer-text">
            &copy; <?php echo date('Y'); ?> MTs Ulul Albab. All rights reserved.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Form validation and submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const captcha = document.getElementById('captcha').value.trim();
            const loginBtn = document.getElementById('loginBtn');
            
            // Basic validation
            if (!username || !password || !captcha) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Form Tidak Lengkap',
                    text: 'Mohon isi semua field yang diperlukan'
                });
                return;
            }
            
            // Show loading state
            loginBtn.classList.add('loading');
            loginBtn.disabled = true;
            
            // Show loading overlay
            document.getElementById('loadingOverlay').classList.add('show');
        });

        // Captcha refresh
        document.getElementById('captchaDisplay').addEventListener('click', function() {
            // Add click animation
            this.style.transform = 'scale(0.9)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 100);
            
            // Refresh captcha via AJAX
            fetch('refresh_captcha.php')
                .then(response => response.text())
                .then(captcha => {
                    this.textContent = captcha;
                })
                .catch(error => {
                    console.error('Error refreshing captcha:', error);
                });
        });

        // Password visibility toggle (optional enhancement)
        document.getElementById('password').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').submit();
            }
        });

        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html> 