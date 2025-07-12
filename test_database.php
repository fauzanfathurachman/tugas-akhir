<?php
/**
 * PSB Online - Database Connection Test
 * 
 * This file tests the database connection and basic functionality
 * 
 * @author PSB Online Team
 * @version 1.0
 */

// Define secure access
define('SECURE_ACCESS', true);

// Include configuration
require_once 'config/config.php';

// Test database connection
echo "=== PSB Online Database Connection Test ===\n\n";

try {
    // Test 1: Basic Connection
    echo "1. Testing basic connection...\n";
    $db = Database::getInstance();
    echo "✓ Database instance created successfully\n";
    
    // Test 2: Connection Test
    echo "\n2. Testing database connection...\n";
    if ($db->testConnection()) {
        echo "✓ Database connection successful\n";
    } else {
        echo "✗ Database connection failed\n";
        exit(1);
    }
    
    // Test 3: Basic Query
    echo "\n3. Testing basic query...\n";
    $result = $db->fetchValue("SELECT 1 as test");
    if ($result == 1) {
        echo "✓ Basic query executed successfully\n";
    } else {
        echo "✗ Basic query failed\n";
    }
    
    // Test 4: Get Database Info
    echo "\n4. Getting database information...\n";
    $dbInfo = $db->getDatabaseSize();
    if ($dbInfo) {
        echo "✓ Database: " . $dbInfo['database'] . "\n";
        echo "✓ Size: " . $dbInfo['size_mb'] . " MB\n";
    } else {
        echo "✗ Failed to get database information\n";
    }
    
    // Test 5: Get Tables
    echo "\n5. Getting table list...\n";
    $tables = $db->getTables();
    if (!empty($tables)) {
        echo "✓ Found " . count($tables) . " tables:\n";
        foreach ($tables as $table) {
            echo "  - " . $table . "\n";
        }
    } else {
        echo "✗ No tables found\n";
    }
    
    // Test 6: Test Sample Data
    echo "\n6. Testing sample data queries...\n";
    
    // Test users table
    if ($db->tableExists('users')) {
        $userCount = $db->fetchValue("SELECT COUNT(*) FROM users");
        echo "✓ Users table: " . $userCount . " records\n";
        
        $adminUser = $db->fetchOne("SELECT username, email, role FROM users WHERE role = 'admin' LIMIT 1");
        if ($adminUser) {
            echo "✓ Admin user found: " . $adminUser['username'] . " (" . $adminUser['email'] . ")\n";
        }
    }
    
    // Test calon_siswa table
    if ($db->tableExists('calon_siswa')) {
        $siswaCount = $db->fetchValue("SELECT COUNT(*) FROM calon_siswa");
        echo "✓ Calon siswa table: " . $siswaCount . " records\n";
        
        $sampleSiswa = $db->fetchOne("SELECT nomor_daftar, nama_lengkap, asal_sekolah FROM calon_siswa LIMIT 1");
        if ($sampleSiswa) {
            echo "✓ Sample siswa: " . $sampleSiswa['nama_lengkap'] . " (" . $sampleSiswa['nomor_daftar'] . ")\n";
        }
    }
    
    // Test pendaftaran table
    if ($db->tableExists('pendaftaran')) {
        $pendaftaranCount = $db->fetchValue("SELECT COUNT(*) FROM pendaftaran");
        echo "✓ Pendaftaran table: " . $pendaftaranCount . " records\n";
        
        $avgNilai = $db->fetchValue("SELECT AVG(rata_rata_un) FROM pendaftaran WHERE rata_rata_un IS NOT NULL");
        if ($avgNilai) {
            echo "✓ Average UN score: " . round($avgNilai, 2) . "\n";
        }
    }
    
    // Test 7: Test Views
    echo "\n7. Testing database views...\n";
    
    try {
        $pendaftarLengkap = $db->fetchAll("SELECT * FROM v_pendaftar_lengkap LIMIT 3");
        echo "✓ v_pendaftar_lengkap view: " . count($pendaftarLengkap) . " records\n";
    } catch (Exception $e) {
        echo "✗ v_pendaftar_lengkap view failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $statistik = $db->fetchAll("SELECT * FROM v_statistik_pendaftaran");
        echo "✓ v_statistik_pendaftaran view: " . count($statistik) . " records\n";
    } catch (Exception $e) {
        echo "✗ v_statistik_pendaftaran view failed: " . $e->getMessage() . "\n";
    }
    
    // Test 8: Test Stored Procedures
    echo "\n8. Testing stored procedures...\n";
    
    try {
        $db->query("CALL GenerateNomorDaftar('2024/2025', @nomor_daftar)");
        $nomorDaftar = $db->fetchValue("SELECT @nomor_daftar");
        echo "✓ GenerateNomorDaftar procedure: " . $nomorDaftar . "\n";
    } catch (Exception $e) {
        echo "✗ GenerateNomorDaftar procedure failed: " . $e->getMessage() . "\n";
    }
    
    // Test 9: Test Transactions
    echo "\n9. Testing transactions...\n";
    
    try {
        $db->beginTransaction();
        echo "✓ Transaction started\n";
        
        // Test insert in transaction
        $testData = [
            'nama_setting' => 'test_setting_' . time(),
            'nilai' => 'test_value',
            'deskripsi' => 'Test setting for database test',
            'kategori' => 'sistem'
        ];
        
        $insertId = $db->insert(
            "INSERT INTO pengaturan (nama_setting, nilai, deskripsi, kategori) VALUES (?, ?, ?, ?)",
            [$testData['nama_setting'], $testData['nilai'], $testData['deskripsi'], $testData['kategori']]
        );
        
        if ($insertId) {
            echo "✓ Test record inserted with ID: " . $insertId . "\n";
            
            // Rollback to clean up
            $db->rollback();
            echo "✓ Transaction rolled back (test data cleaned up)\n";
        } else {
            echo "✗ Failed to insert test record\n";
            $db->rollback();
        }
        
    } catch (Exception $e) {
        echo "✗ Transaction test failed: " . $e->getMessage() . "\n";
        try {
            $db->rollback();
        } catch (Exception $rollbackError) {
            echo "✗ Rollback failed: " . $rollbackError->getMessage() . "\n";
        }
    }
    
    // Test 10: Get Statistics
    echo "\n10. Getting database statistics...\n";
    $stats = $db->getStats();
    echo "✓ Queries executed: " . $stats['queries'] . "\n";
    echo "✓ Errors encountered: " . $stats['errors'] . "\n";
    echo "✓ Execution time: " . $stats['execution_time'] . " seconds\n";
    echo "✓ Memory usage: " . round($stats['memory_usage'] / 1024 / 1024, 2) . " MB\n";
    echo "✓ Peak memory: " . round($stats['peak_memory'] / 1024 / 1024, 2) . " MB\n";
    
    // Test 11: Test Helper Functions
    echo "\n11. Testing helper functions...\n";
    
    // Test db() function
    $dbInstance = db();
    if ($dbInstance instanceof Database) {
        echo "✓ db() helper function works\n";
    } else {
        echo "✗ db() helper function failed\n";
    }
    
    // Test db_fetch_one function
    $user = db_fetch_one("SELECT username, role FROM users LIMIT 1");
    if ($user) {
        echo "✓ db_fetch_one() helper function works\n";
    } else {
        echo "✗ db_fetch_one() helper function failed\n";
    }
    
    // Test db_fetch_value function
    $userCount = db_fetch_value("SELECT COUNT(*) FROM users");
    if (is_numeric($userCount)) {
        echo "✓ db_fetch_value() helper function works\n";
    } else {
        echo "✗ db_fetch_value() helper function failed\n";
    }
    
    // Test 12: Test Configuration
    echo "\n12. Testing configuration...\n";
    echo "✓ Database host: " . config('DB_HOST') . "\n";
    echo "✓ Database name: " . config('DB_NAME') . "\n";
    echo "✓ Database charset: " . config('DB_CHARSET') . "\n";
    echo "✓ App name: " . config('APP_NAME') . "\n";
    echo "✓ App version: " . config('APP_VERSION') . "\n";
    echo "✓ Debug mode: " . (config('APP_DEBUG') ? 'Enabled' : 'Disabled') . "\n";
    
    echo "\n=== All Tests Completed Successfully! ===\n";
    echo "Database connection and functionality are working properly.\n";
    
} catch (Exception $e) {
    echo "\n✗ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 