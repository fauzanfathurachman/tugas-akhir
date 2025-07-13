<?php
// admin/includes/session.php
// Sistem session aman & lengkap

class SessionManager {
    private static $timeout = 1800; // 30 menit
    private static $regenerate = 300; // 5 menit
    private static $cookieParams = [
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => true, // Hanya HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ];
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(self::$cookieParams);
            session_start();
            self::checkTimeout();
            self::checkHijack();
            self::regenerate();
        }
    }
    public static function login($userId) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['session_token'] = bin2hex(random_bytes(32));
        self::log('login', $userId);
        self::preventConcurrent($userId);
    }
    public static function logout() {
        self::log('logout', $_SESSION['user_id'] ?? null);
        session_unset();
        session_destroy();
    }
    public static function checkTimeout() {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > self::$timeout)) {
            self::logout();
            header('Location: login.php?timeout=1');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
    public static function checkHijack() {
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            self::logout();
            header('Location: login.php?hijack=1');
            exit;
        }
    }
    public static function regenerate() {
        if (!isset($_SESSION['regen_time']) || time() - $_SESSION['regen_time'] > self::$regenerate) {
            session_regenerate_id(true);
            $_SESSION['regen_time'] = time();
        }
    }
    public static function preventConcurrent($userId) {
        $file = sys_get_temp_dir() . '/psb_session_' . md5($userId);
        file_put_contents($file, session_id());
        $_SESSION['concurrent_file'] = $file;
    }
    public static function checkConcurrent($userId) {
        $file = sys_get_temp_dir() . '/psb_session_' . md5($userId);
        if (file_exists($file) && file_get_contents($file) !== session_id()) {
            self::logout();
            header('Location: login.php?concurrent=1');
            exit;
        }
    }
    public static function rememberMe($userId) {
        $token = bin2hex(random_bytes(32));
        setcookie('rememberme', $token, time()+2592000, '/', '', true, true);
        // Simpan token ke DB (implementasi di luar class)
    }
    public static function encrypt($data, $key) {
        $iv = random_bytes(16);
        $enc = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $enc);
    }
    public static function decrypt($data, $key) {
        $raw = base64_decode($data);
        $iv = substr($raw, 0, 16);
        $enc = substr($raw, 16);
        return openssl_decrypt($enc, 'AES-256-CBC', $key, 0, $iv);
    }
    public static function log($action, $userId) {
        $log = date('c') . ", $action, $userId, " . $_SERVER['REMOTE_ADDR'] . "\n";
        file_put_contents(__DIR__ . '/../../logs/session.log', $log, FILE_APPEND);
    }
    public static function cleanup() {
        // Hapus session file expired (opsional, tergantung handler)
    }
}
// Untuk auto-logout JS: hitung mundur dari $_SESSION['last_activity']
// Monitoring: tampilkan isi logs/session.log di dashboard admin
