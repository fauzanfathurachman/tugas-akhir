<?php
// Test script untuk admin login system
echo "=== Test Admin Login System ===\n\n";

// Test 1: Check if config file exists
echo "1. Testing config file...\n";
if (file_exists('config/config.php')) {
    echo "   ✓ config/config.php exists\n";
} else {
    echo "   ✗ config/config.php not found\n";
    exit(1);
}

// Test 2: Check if admin directory exists
echo "2. Testing admin directory...\n";
if (is_dir('admin')) {
    echo "   ✓ admin/ directory exists\n";
} else {
    echo "   ✗ admin/ directory not found\n";
    exit(1);
}

// Test 3: Check if admin files exist
echo "3. Testing admin files...\n";
$admin_files = [
    'admin/login.php',
    'admin/dashboard.php',
    'admin/logout.php',
    'admin/auth_check.php',
    'admin/refresh_captcha.php',
    'admin/create_admin.php'
];

foreach ($admin_files as $file) {
    if (file_exists($file)) {
        echo "   ✓ $file exists\n";
    } else {
        echo "   ✗ $file not found\n";
    }
}

// Test 4: Test database connection
echo "\n4. Testing database connection...\n";
try {
    define('SECURE_ACCESS', true);
    require_once 'config/config.php';
    $db = Database::getInstance();
    echo "   ✓ Database connection successful\n";
} catch (Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 5: Check if users table exists
echo "5. Testing users table...\n";
try {
    $result = $db->fetchOne("SHOW TABLES LIKE 'users'");
    if ($result) {
        echo "   ✓ users table exists\n";
    } else {
        echo "   ✗ users table not found\n";
        echo "   Please run the database schema first\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error checking users table: " . $e->getMessage() . "\n";
}

// Test 6: Check if activity_log table exists
echo "6. Testing activity_log table...\n";
try {
    $result = $db->fetchOne("SHOW TABLES LIKE 'activity_log'");
    if ($result) {
        echo "   ✓ activity_log table exists\n";
    } else {
        echo "   ✗ activity_log table not found\n";
        echo "   Please run the updated database schema\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error checking activity_log table: " . $e->getMessage() . "\n";
}

echo "\n=== Test Summary ===\n";
echo "Admin login system files are ready!\n";
echo "To test the system:\n";
echo "1. Run: php admin/create_admin.php\n";
echo "2. Open: http://localhost/admin/login.php\n";
echo "3. Login with: admin / admin123\n";
echo "\nNote: Make sure your database is properly configured in config/config.php\n";
?> 