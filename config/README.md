# PSB Online - Configuration System

Sistem konfigurasi untuk PSB Online dengan database connection, error handling, dan security measures.

## 📁 File Structure

```
config/
├── config.php          # Main configuration file
├── database.php        # Database connection class
├── validation_rules.php # Form validation rules
└── README.md          # This file
```

## 🔧 Configuration Files

### 1. `config.php` - Main Configuration

File konfigurasi utama yang berisi semua konstanta dan pengaturan sistem.

#### **Application Configuration**
- `APP_NAME` - Nama aplikasi
- `APP_VERSION` - Versi aplikasi
- `APP_ENVIRONMENT` - Environment (development/staging/production)
- `APP_DEBUG` - Mode debug

#### **Database Configuration**
- `DB_HOST` - Host database
- `DB_NAME` - Nama database
- `DB_USER` - Username database
- `DB_PASS` - Password database
- `DB_PORT` - Port database
- `DB_CHARSET` - Charset database (utf8mb4)

#### **Security Configuration**
- Session settings
- Password requirements
- CSRF protection
- Rate limiting

#### **File Upload Configuration**
- Upload size limits
- Allowed file types
- Image processing settings

#### **Email Configuration**
- SMTP settings
- Email templates

#### **Logging Configuration**
- Log levels
- Log file paths
- Database logging

### 2. `database.php` - Database Connection Class

Class untuk menangani koneksi database menggunakan PDO dengan fitur keamanan.

#### **Features:**
- ✅ **Singleton Pattern** - Single database connection
- ✅ **PDO Connection** - Secure database connection
- ✅ **Prepared Statements** - SQL injection protection
- ✅ **Error Handling** - Comprehensive error management
- ✅ **Logging** - Database operation logging
- ✅ **Transactions** - ACID compliance
- ✅ **Statistics** - Query performance monitoring
- ✅ **Helper Functions** - Easy-to-use functions

#### **Main Methods:**
```php
// Get database instance
$db = Database::getInstance();

// Basic queries
$results = $db->fetchAll($sql, $params);
$row = $db->fetchOne($sql, $params);
$value = $db->fetchValue($sql, $params);

// Insert/Update/Delete
$id = $db->insert($sql, $params);
$affected = $db->execute($sql, $params);

// Transactions
$db->beginTransaction();
$db->commit();
$db->rollback();

// Connection test
$isConnected = $db->testConnection();

// Statistics
$stats = $db->getStats();
```

#### **Helper Functions:**
```php
// Get database instance
$db = db();

// Fetch data
$results = db_fetch_all($sql, $params);
$row = db_fetch_one($sql, $params);
$value = db_fetch_value($sql, $params);

// Execute queries
$id = db_insert($sql, $params);
$affected = db_execute($sql, $params);

// Test connection
$isConnected = db_test_connection();

// Get statistics
$stats = db_get_stats();

// Escape strings
$safeString = db_escape($string);
```

### 3. `validation_rules.php` - Form Validation Rules

Aturan validasi untuk semua form dalam sistem.

#### **Available Validation Rules:**
- `user_login` - Login form validation
- `user_create` - User creation validation
- `user_update` - User update validation
- `calon_siswa_create` - Student registration validation
- `calon_siswa_update` - Student update validation
- `pendaftaran_create` - Registration form validation
- `pengumuman_create` - Announcement creation validation
- `pengaturan_create` - Settings creation validation
- `file_upload` - File upload validation
- `image_upload` - Image upload validation
- `document_upload` - Document upload validation
- `search` - Search form validation
- `password_change` - Password change validation

#### **Validation Types:**
- `required` - Field is required
- `type` - Data type validation (string, integer, email, date, etc.)
- `min_length` / `max_length` - String length validation
- `min` / `max` - Numeric range validation
- `pattern` - Regex pattern validation
- `enum` - Enumeration validation
- `unique` - Database uniqueness validation
- `exists` - Database existence validation
- `match` - Field matching validation

## 🚀 Usage Examples

### 1. Basic Database Usage

```php
<?php
// Include configuration
require_once 'config/config.php';

// Get database instance
$db = Database::getInstance();

// Fetch all users
$users = $db->fetchAll("SELECT * FROM users WHERE role = ?", ['admin']);

// Insert new user
$userId = $db->insert(
    "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)",
    ['john_doe', 'john@example.com', password_hash('password123', PASSWORD_DEFAULT), 'operator']
);

// Update user
$affected = $db->execute(
    "UPDATE users SET email = ? WHERE id = ?",
    ['newemail@example.com', $userId]
);

// Delete user
$affected = $db->execute("DELETE FROM users WHERE id = ?", [$userId]);
```

### 2. Transaction Usage

```php
<?php
try {
    $db = Database::getInstance();
    $db->beginTransaction();
    
    // Insert calon siswa
    $siswaId = $db->insert(
        "INSERT INTO calon_siswa (nomor_daftar, nama_lengkap, ...) VALUES (?, ?, ...)",
        [$nomorDaftar, $namaLengkap, ...]
    );
    
    // Insert pendaftaran
    $pendaftaranId = $db->insert(
        "INSERT INTO pendaftaran (calon_siswa_id, tahun_ajaran, ...) VALUES (?, ?, ...)",
        [$siswaId, $tahunAjaran, ...]
    );
    
    $db->commit();
    echo "Registration successful!";
    
} catch (Exception $e) {
    $db->rollback();
    echo "Registration failed: " . $e->getMessage();
}
```

### 3. Helper Functions Usage

```php
<?php
// Fetch data using helper functions
$users = db_fetch_all("SELECT * FROM users");
$user = db_fetch_one("SELECT * FROM users WHERE id = ?", [1]);
$userCount = db_fetch_value("SELECT COUNT(*) FROM users");

// Insert data
$userId = db_insert("INSERT INTO users (username, email) VALUES (?, ?)", ['test', 'test@example.com']);

// Execute update/delete
$affected = db_execute("UPDATE users SET active = 0 WHERE id = ?", [1]);

// Test connection
if (db_test_connection()) {
    echo "Database connected!";
}

// Get statistics
$stats = db_get_stats();
echo "Queries executed: " . $stats['queries'];
```

### 4. Configuration Usage

```php
<?php
// Get configuration values
$dbHost = config('DB_HOST');
$appName = config('APP_NAME');
$debugMode = config('APP_DEBUG', false);

// Check environment
if (is_debug()) {
    echo "Debug mode enabled";
}

if (is_production()) {
    echo "Production environment";
}

// Format dates
$today = format_date();
$now = format_datetime();
```

## 🔒 Security Features

### 1. Database Security
- **Prepared Statements** - Prevents SQL injection
- **PDO Error Mode** - Exception-based error handling
- **UTF-8 Charset** - Proper character encoding
- **Connection Encryption** - Secure database connection

### 2. Input Validation
- **Type Validation** - Ensures correct data types
- **Length Validation** - Prevents buffer overflow
- **Pattern Validation** - Regex-based validation
- **Database Validation** - Uniqueness and existence checks

### 3. Session Security
- **Secure Cookies** - HttpOnly and SameSite attributes
- **Session Regeneration** - Prevents session fixation
- **CSRF Protection** - Cross-site request forgery protection

### 4. File Upload Security
- **File Type Validation** - Whitelist of allowed types
- **Size Limits** - Prevents large file uploads
- **Virus Scanning** - Optional virus scanning
- **Secure Storage** - Files stored outside web root

## 📊 Monitoring and Logging

### 1. Database Logging
```php
// Log file location
LOGS_PATH . '/database.log'

// Log format
[2024-01-15 10:30:00] [INFO] Database connection established successfully
[2024-01-15 10:30:01] [ERROR] Query execution failed: Table 'users' doesn't exist
```

### 2. Performance Monitoring
```php
// Get database statistics
$stats = $db->getStats();
echo "Queries: " . $stats['queries'];
echo "Errors: " . $stats['errors'];
echo "Execution time: " . $stats['execution_time'];
echo "Memory usage: " . $stats['memory_usage'];
```

### 3. Query Logging (Debug Mode)
```php
// Enable query logging
define('DB_LOG_QUERIES', true);

// Get query log
$queryLog = $db->getQueryLog();
foreach ($queryLog as $query) {
    echo "SQL: " . $query['sql'];
    echo "Params: " . print_r($query['params'], true);
    echo "Time: " . $query['time'];
}
```

## 🧪 Testing

### 1. Run Database Test
```bash
php test_database.php
```

### 2. Test Output
```
=== PSB Online Database Connection Test ===

1. Testing basic connection...
✓ Database instance created successfully

2. Testing database connection...
✓ Database connection successful

3. Testing basic query...
✓ Basic query executed successfully

...

=== All Tests Completed Successfully! ===
Database connection and functionality are working properly.
```

## 🔧 Configuration Management

### 1. Environment-Specific Configuration
```php
// Development
define('APP_ENVIRONMENT', 'development');
define('APP_DEBUG', true);
define('DB_HOST', 'localhost');

// Production
define('APP_ENVIRONMENT', 'production');
define('APP_DEBUG', false);
define('DB_HOST', 'production-db-server');
```

### 2. Database Configuration
```php
// MySQL Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'psb_online');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');
```

### 3. Security Configuration
```php
// Session Security
define('SESSION_SECURE', true); // For HTTPS
define('SESSION_HTTP_ONLY', true);
define('SESSION_SAME_SITE', 'Strict');

// Password Requirements
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_SPECIAL', true);
define('PASSWORD_HASH_COST', 12);
```

## 🚨 Error Handling

### 1. Database Errors
```php
try {
    $result = $db->fetchAll("SELECT * FROM non_existent_table");
} catch (PDOException $e) {
    // Log error
    error_log("Database error: " . $e->getMessage());
    
    // Show user-friendly message
    echo "Database error occurred. Please try again later.";
}
```

### 2. Configuration Errors
```php
// Check if required configuration exists
if (!defined('DB_HOST')) {
    die('Database configuration is missing');
}

// Validate configuration values
if (empty(config('DB_USER'))) {
    die('Database username is not configured');
}
```

## 📝 Best Practices

### 1. Database Usage
- Always use prepared statements
- Handle transactions properly
- Log database errors
- Monitor query performance
- Use appropriate indexes

### 2. Configuration Management
- Use environment-specific configurations
- Never commit sensitive data to version control
- Validate configuration values
- Use constants for configuration keys

### 3. Security
- Validate all user inputs
- Use HTTPS in production
- Implement proper session management
- Regular security audits

### 4. Performance
- Use connection pooling
- Optimize database queries
- Implement caching where appropriate
- Monitor resource usage

## 🆘 Troubleshooting

### 1. Connection Issues
```php
// Test database connection
if (!db_test_connection()) {
    echo "Database connection failed";
    // Check DB_HOST, DB_USER, DB_PASS, DB_NAME
}
```

### 2. Permission Issues
```php
// Check file permissions
if (!is_writable(LOGS_PATH)) {
    echo "Log directory is not writable";
}
```

### 3. Configuration Issues
```php
// Validate configuration
if (!config('DB_HOST')) {
    echo "Database host not configured";
}
```

## 📞 Support

Untuk bantuan terkait konfigurasi sistem, silakan hubungi tim development atau buat issue di repository. 