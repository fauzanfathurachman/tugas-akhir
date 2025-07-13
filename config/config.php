<?php
/**
 * PSB Online - Main Configuration File
 * 
 * This file contains all global constants and configuration settings
 * for the PSB Online system.
 * 
 * @author PSB Online Team
 * @version 1.0
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

// =====================================================
// APPLICATION CONFIGURATION
// =====================================================

// Application Information
define('APP_NAME', 'PSB Online');
define('APP_VERSION', '1.0.0');
define('APP_ENVIRONMENT', 'development'); // development, staging, production
define('APP_DEBUG', true); // Set to false in production

// Application Paths
define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('LOGS_PATH', BASE_PATH . '/logs');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('CACHE_PATH', BASE_PATH . '/cache');

// URL Configuration
define('BASE_URL', 'http://localhost/psb-online');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');

// =====================================================
// DATABASE CONFIGURATION
// =====================================================

// Database Connection Settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'psb_online');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// Database Connection Options
define('DB_OPTIONS', [
    'PDO::ATTR_ERRMODE' => PDO::ERRMODE_EXCEPTION,
    'PDO::ATTR_DEFAULT_FETCH_MODE' => PDO::FETCH_ASSOC,
    'PDO::ATTR_EMULATE_PREPARES' => false,
    'PDO::MYSQL_ATTR_INIT_COMMAND' => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
]);

// =====================================================
// SECURITY CONFIGURATION
// =====================================================

// Session Configuration
define('SESSION_NAME', 'PSB_SESSION');
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_PATH', '/');
define('SESSION_DOMAIN', '');
define('SESSION_SECURE', false); // Set to true if using HTTPS
define('SESSION_HTTP_ONLY', true);
define('SESSION_SAME_SITE', 'Lax');

// Password Configuration
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_SPECIAL', true);
define('PASSWORD_HASH_COST', 12);

// CSRF Protection
define('CSRF_TOKEN_NAME', 'psb_csrf_token');
define('CSRF_TOKEN_LENGTH', 32);

// Rate Limiting
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_MAX_REQUESTS', 100); // requests per minute
define('RATE_LIMIT_WINDOW', 60); // seconds

// =====================================================
// FILE UPLOAD CONFIGURATION
// =====================================================

// Upload Settings
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', [
    'image' => ['jpg', 'jpeg', 'png', 'gif'],
    'document' => ['pdf', 'doc', 'docx'],
    'archive' => ['zip', 'rar']
]);

// Image Processing
define('IMAGE_MAX_WIDTH', 1920);
define('IMAGE_MAX_HEIGHT', 1080);
define('IMAGE_QUALITY', 85);
define('THUMBNAIL_WIDTH', 300);
define('THUMBNAIL_HEIGHT', 300);

// =====================================================
// EMAIL CONFIGURATION
// =====================================================

// SMTP Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@psbonline.com');
define('SMTP_PASSWORD', 'your_smtp_password');
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_FROM_NAME', 'PSB Online System');
define('SMTP_FROM_EMAIL', 'noreply@psbonline.com');

// Email Templates
define('EMAIL_TEMPLATE_PATH', BASE_PATH . '/templates/email');

// =====================================================
// LOGGING CONFIGURATION
// =====================================================

// Log Settings
define('LOG_ENABLED', true);
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR, CRITICAL
define('LOG_FILE', LOGS_PATH . '/app.log');
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('LOG_MAX_FILES', 5);

// Database Log Settings
define('DB_LOG_ENABLED', true);
define('DB_LOG_FILE', LOGS_PATH . '/database.log');
define('DB_LOG_QUERIES', APP_DEBUG); // Log queries only in debug mode

// =====================================================
// CACHE CONFIGURATION
// =====================================================

// Cache Settings
define('CACHE_ENABLED', true);
define('CACHE_DRIVER', 'file'); // file, redis, memcached
define('CACHE_TTL', 3600); // 1 hour
define('CACHE_PREFIX', 'psb_');

// =====================================================
// TIME AND LOCALE CONFIGURATION
// =====================================================

// Timezone
define('TIMEZONE', 'Asia/Jakarta');

// Locale
define('LOCALE', 'id_ID');
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i:s');
define('TIME_FORMAT', 'H:i:s');

// =====================================================
// ERROR HANDLING CONFIGURATION
// =====================================================

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Custom Error Handler
define('CUSTOM_ERROR_HANDLER', true);

// =====================================================
// VALIDATION CONFIGURATION
// =====================================================

// Form Validation
define('VALIDATION_RULES_PATH', CONFIG_PATH . '/validation_rules.php');

// File Validation
define('FILE_VALIDATION_ENABLED', true);

// =====================================================
// API CONFIGURATION
// =====================================================

// API Settings
define('API_ENABLED', true);
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 1000); // requests per hour
define('API_TOKEN_EXPIRY', 3600); // 1 hour

// =====================================================
// MAINTENANCE CONFIGURATION
// =====================================================

// Maintenance Mode
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_ALLOWED_IPS', ['127.0.0.1', '::1']);

// =====================================================
// HELPER FUNCTIONS
// =====================================================

/**
 * Get configuration value
 * 
 * @param string $key Configuration key
 * @param mixed $default Default value if key not found
 * @return mixed
 */
function config($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

/**
 * Check if application is in debug mode
 * 
 * @return bool
 */
function is_debug() {
    return config('APP_DEBUG', false);
}

/**
 * Check if application is in production mode
 * 
 * @return bool
 */
function is_production() {
    return config('APP_ENVIRONMENT') === 'production';
}

/**
 * Get current timestamp
 * 
 * @return int
 */
function now() {
    return time();
}

/**
 * Format date
 * 
 * @param string $format Date format
 * @param int $timestamp Timestamp
 * @return string
 */
function format_date($format = null, $timestamp = null) {
    $format = $format ?: config('DATE_FORMAT', 'd/m/Y');
    $timestamp = $timestamp ?: now();
    return date($format, $timestamp);
}

/**
 * Format datetime
 * 
 * @param string $format Datetime format
 * @param int $timestamp Timestamp
 * @return string
 */
function format_datetime($format = null, $timestamp = null) {
    $format = $format ?: config('DATETIME_FORMAT', 'd/m/Y H:i:s');
    $timestamp = $timestamp ?: now();
    return date($format, $timestamp);
}

// =====================================================
// INITIALIZATION
// =====================================================

// Set timezone
date_default_timezone_set(config('TIMEZONE', 'Asia/Jakarta'));

// Set locale
setlocale(LC_ALL, config('LOCALE', 'id_ID'));

// Create necessary directories
$directories = [
    LOGS_PATH,
    UPLOADS_PATH,
    CACHE_PATH,
    UPLOADS_PATH . '/images',
    UPLOADS_PATH . '/documents',
    UPLOADS_PATH . '/temp'
];

foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}

// Start session if not already started, and only if headers not sent
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_name(config('SESSION_NAME', 'PSB_SESSION'));
    session_set_cookie_params([
        'lifetime' => config('SESSION_LIFETIME', 3600),
        'path' => config('SESSION_PATH', '/'),
        'domain' => config('SESSION_DOMAIN', ''),
        'secure' => config('SESSION_SECURE', false),
        'httponly' => config('SESSION_HTTP_ONLY', true),
        'samesite' => config('SESSION_SAME_SITE', 'Lax')
    ]);
    session_start();
}

// Load additional configuration files
$additional_configs = [
    'database.php',
    'validation_rules.php'
];

foreach ($additional_configs as $config_file) {
    $config_path = CONFIG_PATH . '/' . $config_file;
    if (file_exists($config_path)) {
        require_once $config_path;
    }
} 