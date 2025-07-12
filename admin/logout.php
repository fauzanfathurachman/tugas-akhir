<?php
// Start session
session_start();

// Include database configuration
define('SECURE_ACCESS', true);
require_once '../config/config.php';

// Log logout activity if user was logged in
if (isset($_SESSION['admin_id'])) {
    try {
        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO activity_log (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)",
            [$_SESSION['admin_id'], 'logout', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]
        );
    } catch (Exception $e) {
        // Ignore logging errors
    }
}

// Clear remember token from database
if (isset($_SESSION['admin_id'])) {
    try {
        $db = Database::getInstance();
        $db->execute(
            "UPDATE users SET remember_token = NULL, remember_expires = NULL WHERE id = ?",
            [$_SESSION['admin_id']]
        );
    } catch (Exception $e) {
        // Ignore database errors
    }
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Clear remember me cookie
setcookie('admin_remember', '', time() - 3600, '/', '', true, true);

// Redirect to login page
header('Location: login.php');
exit();
?> 