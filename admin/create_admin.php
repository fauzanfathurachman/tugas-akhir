<?php
// Include database configuration
define('SECURE_ACCESS', true);
require_once '../config/config.php';

// Admin user data
$admin_data = [
    'username' => 'admin',
    'password' => 'admin123',
    'email' => 'admin@mtululalbab.sch.id',
    'role' => 'admin',
    'status' => 'active'
];

try {
    $db = Database::getInstance();
    
    // Check if admin user already exists
    $existing_user = $db->fetchOne(
        "SELECT id FROM users WHERE username = ?",
        [$admin_data['username']]
    );
    
    if ($existing_user) {
        echo "Admin user already exists!\n";
        echo "Username: " . $admin_data['username'] . "\n";
        echo "Password: " . $admin_data['password'] . "\n";
    } else {
        // Hash password
        $hashed_password = password_hash($admin_data['password'], PASSWORD_DEFAULT);
        
        // Insert admin user
        $db->execute(
            "INSERT INTO users (username, password, email, role, status, created_at) 
             VALUES (?, ?, ?, ?, ?, NOW())",
            [
                $admin_data['username'],
                $hashed_password,
                $admin_data['email'],
                $admin_data['role'],
                $admin_data['status']
            ]
        );
        
        echo "Admin user created successfully!\n";
        echo "Username: " . $admin_data['username'] . "\n";
        echo "Password: " . $admin_data['password'] . "\n";
        echo "Role: " . $admin_data['role'] . "\n";
        echo "\nYou can now login at: admin/login.php\n";
    }
    
} catch (Exception $e) {
    echo "Error creating admin user: " . $e->getMessage() . "\n";
}
?> 