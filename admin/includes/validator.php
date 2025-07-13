<?php
// admin/includes/validator.php
// Library validasi & sanitasi input lengkap

class Validator {
    public static $errors = [];
    public static $messages = [
        'en' => [
            'invalid_email' => 'Invalid email address.',
            'invalid_phone' => 'Invalid phone number.',
            'csrf' => 'Invalid CSRF token.',
            'rate_limit' => 'Too many requests. Please try again later.',
            'invalid_nisn' => 'Invalid NISN format.',
            'invalid_nomor_daftar' => 'Invalid registration number format.',
            'invalid_file' => 'Invalid file upload.',
        ],
        'id' => [
            'invalid_email' => 'Email tidak valid.',
            'invalid_phone' => 'Nomor HP tidak valid.',
            'csrf' => 'Token CSRF tidak valid.',
            'rate_limit' => 'Terlalu banyak permintaan. Coba lagi nanti.',
            'invalid_nisn' => 'Format NISN tidak valid.',
            'invalid_nomor_daftar' => 'Format nomor pendaftaran tidak valid.',
            'invalid_file' => 'Upload file tidak valid.',
        ]
    ];
    public static $lang = 'id';

    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return trim(strip_tags($input));
    }
    public static function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::$errors[] = self::msg('invalid_email');
            return false;
        }
        return true;
    }
    public static function validatePhone($phone) {
        // Format Indonesia: 08xx... atau +628xx...
        if (!preg_match('/^(\+62|62|0)8[1-9][0-9]{6,10}$/', $phone)) {
            self::$errors[] = self::msg('invalid_phone');
            return false;
        }
        return true;
    }
    public static function csrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    public static function validateCsrf($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            self::$errors[] = self::msg('csrf');
            return false;
        }
        return true;
    }
    public static function xss($input) {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    public static function rateLimit($key, $limit = 10, $seconds = 60) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $k = 'rate_' . md5($key . $ip);
        if (!isset($_SESSION[$k])) $_SESSION[$k] = [];
        $_SESSION[$k] = array_filter($_SESSION[$k], function($t) use ($seconds) { return $t > time() - $seconds; });
        if (count($_SESSION[$k]) >= $limit) {
            self::$errors[] = self::msg('rate_limit');
            return false;
        }
        $_SESSION[$k][] = time();
        return true;
    }
    public static function validateNISN($nisn) {
        if (!preg_match('/^[0-9]{10}$/', $nisn)) {
            self::$errors[] = self::msg('invalid_nisn');
            return false;
        }
        return true;
    }
    public static function validateNomorDaftar($nomor) {
        if (!preg_match('/^PSB-\d{4}-\d{4}$/', $nomor)) {
            self::$errors[] = self::msg('invalid_nomor_daftar');
            return false;
        }
        return true;
    }
    public static function validateFile($file, $allowed = ['jpg','png','pdf'], $maxSize = 2097152) {
        if ($file['error'] !== UPLOAD_ERR_OK) return false;
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            self::$errors[] = self::msg('invalid_file');
            return false;
        }
        if ($file['size'] > $maxSize) {
            self::$errors[] = self::msg('invalid_file');
            return false;
        }
        return true;
    }
    public static function msg($key) {
        return self::$messages[self::$lang][$key] ?? $key;
    }
    public static function getErrors() {
        return self::$errors;
    }
    public static function clearErrors() {
        self::$errors = [];
    }
}
// Dokumentasi: Lihat fungsi dan pesan error di atas. Semua input WAJIB divalidasi sebelum diproses!
