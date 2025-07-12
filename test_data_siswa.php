<?php
/**
 * Test Script for Data Siswa Page
 * 
 * This script tests the functionality of the admin data siswa page
 * including DataTables, modals, CRUD operations, and export features.
 */

echo "<h1>Test Data Siswa Page</h1>\n";
echo "<p>Testing admin data siswa functionality...</p>\n";

// Test 1: Check if required files exist
echo "<h2>1. File Existence Check</h2>\n";
$required_files = [
    'admin/data_siswa.php',
    'admin/get_siswa_detail.php',
    'admin/get_siswa_data.php',
    'admin/update_siswa.php',
    'admin/export_siswa.php',
    'admin/includes/header.php',
    'admin/includes/sidebar.php',
    'admin/includes/footer.php',
    'config/database.php',
    'config/validation_rules.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✓ $file exists<br>\n";
    } else {
        echo "✗ $file missing<br>\n";
    }
}

// Test 2: Check database connection
echo "<h2>2. Database Connection Test</h2>\n";
try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    echo "✓ Database connection successful<br>\n";
    
    // Check if required tables exist
    $tables = ['calon_siswa', 'pendaftaran', 'activity_log'];
    foreach ($tables as $table) {
        $stmt = $db->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->fetch()) {
            echo "✓ Table '$table' exists<br>\n";
        } else {
            echo "✗ Table '$table' missing<br>\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "<br>\n";
}

// Test 3: Check sample data
echo "<h2>3. Sample Data Check</h2>\n";
try {
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM calon_siswa");
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "✓ Found $count student records<br>\n";
    
    if ($count > 0) {
        $stmt = $db->prepare("SELECT cs.*, p.status_verifikasi, p.status_seleksi 
                             FROM calon_siswa cs 
                             LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id 
                             LIMIT 1");
        $stmt->execute();
        $sample = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✓ Sample data structure: " . implode(', ', array_keys($sample)) . "<br>\n";
    }
    
} catch (Exception $e) {
    echo "✗ Data check failed: " . $e->getMessage() . "<br>\n";
}

// Test 4: Check file permissions
echo "<h2>4. File Permissions Check</h2>\n";
$upload_dirs = [
    'uploads/foto',
    'uploads/kk',
    'uploads/akta',
    'uploads/ijazah'
];

foreach ($upload_dirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "✓ $dir is writable<br>\n";
        } else {
            echo "✗ $dir is not writable<br>\n";
        }
    } else {
        echo "✗ $dir directory missing<br>\n";
    }
}

// Test 5: Check JavaScript libraries
echo "<h2>5. JavaScript Libraries Check</h2>\n";
$js_libs = [
    'https://code.jquery.com/jquery-3.7.1.min.js',
    'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
    'https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'
];

foreach ($js_libs as $lib) {
    $headers = get_headers($lib);
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "✓ $lib accessible<br>\n";
    } else {
        echo "✗ $lib not accessible<br>\n";
    }
}

// Test 6: Check CSS libraries
echo "<h2>6. CSS Libraries Check</h2>\n";
$css_libs = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

foreach ($css_libs as $lib) {
    $headers = get_headers($lib);
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "✓ $lib accessible<br>\n";
    } else {
        echo "✗ $lib not accessible<br>\n";
    }
}

// Test 7: Validate HTML structure
echo "<h2>7. HTML Structure Validation</h2>\n";
$data_siswa_content = file_get_contents('admin/data_siswa.php');
$required_elements = [
    'dataSiswaTable',
    'viewModal',
    'editModal',
    'DataTable',
    'SweetAlert',
    'Bootstrap'
];

foreach ($required_elements as $element) {
    if (strpos($data_siswa_content, $element) !== false) {
        echo "✓ Found $element in data_siswa.php<br>\n";
    } else {
        echo "✗ Missing $element in data_siswa.php<br>\n";
    }
}

// Test 8: Check AJAX endpoints
echo "<h2>8. AJAX Endpoints Check</h2>\n";
$ajax_files = [
    'admin/get_siswa_detail.php',
    'admin/get_siswa_data.php',
    'admin/update_siswa.php'
];

foreach ($ajax_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'Content-Type: application/json') !== false) {
            echo "✓ $file has proper JSON headers<br>\n";
        } else {
            echo "✗ $file missing JSON headers<br>\n";
        }
        
        if (strpos($content, 'auth_check.php') !== false) {
            echo "✓ $file includes authentication check<br>\n";
        } else {
            echo "✗ $file missing authentication check<br>\n";
        }
    }
}

// Test 9: Check export functionality
echo "<h2>9. Export Functionality Check</h2>\n";
$export_content = file_get_contents('admin/export_siswa.php');
if (strpos($export_content, 'exportToExcel') !== false) {
    echo "✓ Excel export function found<br>\n";
} else {
    echo "✗ Excel export function missing<br>\n";
}

if (strpos($export_content, 'exportToPDF') !== false) {
    echo "✓ PDF export function found<br>\n";
} else {
    echo "✗ PDF export function missing<br>\n";
}

// Test 10: Check validation rules
echo "<h2>10. Validation Rules Check</h2>\n";
if (file_exists('config/validation_rules.php')) {
    $validation_content = file_get_contents('config/validation_rules.php');
    if (strpos($validation_content, 'calon_siswa') !== false) {
        echo "✓ Calon siswa validation rules found<br>\n";
    } else {
        echo "✗ Calon siswa validation rules missing<br>\n";
    }
} else {
    echo "✗ Validation rules file missing<br>\n";
}

echo "<h2>Test Summary</h2>\n";
echo "<p>Data Siswa page test completed. Check the results above for any issues.</p>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>Access admin/data_siswa.php to test the interface</li>\n";
echo "<li>Test DataTables functionality (search, sort, pagination)</li>\n";
echo "<li>Test modal dialogs (view, edit)</li>\n";
echo "<li>Test CRUD operations (create, read, update, delete)</li>\n";
echo "<li>Test export functionality (Excel, PDF)</li>\n";
echo "<li>Test bulk actions and filters</li>\n";
echo "</ul>\n";
?> 