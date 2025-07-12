# Admin Login System - MTs Ulul Albab

## Overview
Sistem login admin yang aman dan modern untuk panel administrasi PSB MTs Ulul Albab.

## Fitur Utama

### 🔐 Keamanan
- **Password Hashing**: Menggunakan `password_hash()` dengan algoritma bcrypt
- **Session Management**: Session timeout 8 jam dengan regenerasi ID
- **Brute Force Protection**: Akun terkunci setelah 5 percobaan gagal (15 menit)
- **CSRF Protection**: Captcha sederhana untuk mencegah bot attacks
- **Secure Cookies**: Remember me cookie dengan flag secure dan httpOnly
- **SQL Injection Protection**: Menggunakan prepared statements

### 🎨 User Interface
- **Modern Design**: Gradient background dengan glassmorphism effect
- **Responsive**: Optimized untuk desktop dan mobile
- **Loading Animations**: Spinner dan overlay saat proses login
- **Interactive Elements**: Hover effects dan smooth transitions
- **Font Awesome Icons**: Icon yang konsisten dan menarik

### 🔄 Session Management
- **Auto Login**: Remember me functionality (30 hari)
- **Session Timeout**: Otomatis logout setelah 8 jam tidak aktif
- **Activity Logging**: Mencatat login/logout activity
- **Multi-tab Support**: Session tetap aktif di multiple tabs

## File Structure

```
admin/
├── login.php              # Halaman login utama
├── dashboard.php          # Dashboard setelah login
├── logout.php             # Script logout
├── auth_check.php         # Session protection (include)
├── refresh_captcha.php    # AJAX endpoint untuk refresh captcha
├── create_admin.php       # Script untuk membuat user admin
└── README.md              # Dokumentasi ini
```

## Setup dan Instalasi

### 1. Buat User Admin
Jalankan script untuk membuat user admin default:
```bash
php admin/create_admin.php
```

**Default Credentials:**
- Username: `admin`
- Password: `admin123`
- Role: `admin`

### 2. Akses Login
Buka browser dan akses:
```
http://your-domain/admin/login.php
```

### 3. Login ke Dashboard
Masukkan credentials dan klik "Masuk ke Panel Admin"

## Database Requirements

### Tabel `users`
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    login_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    remember_token VARCHAR(64) NULL,
    remember_expires DATETIME NULL,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Tabel `activity_log`
```sql
CREATE TABLE activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## Fitur Detail

### Login Form
- **Username Field**: Validasi required dan format
- **Password Field**: Hidden input dengan icon lock
- **Captcha**: 4-digit number dengan refresh capability
- **Remember Me**: Checkbox untuk auto-login 30 hari
- **Validation**: Client-side dan server-side validation

### Security Features
- **Password Verification**: Menggunakan `password_verify()`
- **Account Lockout**: 5 failed attempts = 15 minutes lock
- **Session Regeneration**: New session ID after login
- **Secure Headers**: HTTP-only cookies, secure flags
- **Input Sanitization**: XSS protection dengan `htmlspecialchars()`

### Remember Me
- **Token Generation**: 32-byte random token
- **Database Storage**: Token dan expiry time
- **Cookie Security**: Secure, httpOnly, 30-day expiry
- **Auto Cleanup**: Token dihapus saat logout

### Session Protection
- **Timeout Check**: 8-hour session timeout
- **Activity Logging**: Login/logout tracking
- **Multi-device**: Support untuk multiple devices
- **Secure Logout**: Complete session cleanup

## Usage Examples

### Include Session Protection
```php
<?php
require_once 'auth_check.php';
// Your admin page code here
?>
```

### Check User Role
```php
<?php
if ($admin_role === 'super_admin') {
    // Super admin specific code
}
?>
```

### Log Activity
```php
<?php
$db->execute(
    "INSERT INTO activity_log (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)",
    [$admin_id, 'custom_action', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]
);
?>
```

## Customization

### Change Session Timeout
Edit `admin/auth_check.php`:
```php
$session_timeout = 4 * 60 * 60; // 4 hours
```

### Modify Login Attempts
Edit `admin/login.php`:
```php
if ($attempts >= 3) { // Change from 5 to 3
    $locked_until = date('Y-m-d H:i:s', strtotime('+30 minutes')); // Change lock time
}
```

### Update Remember Me Duration
Edit `admin/login.php`:
```php
$expires = date('Y-m-d H:i:s', strtotime('+7 days')); // Change from 30 to 7 days
```

## Security Best Practices

1. **Change Default Password**: Ganti password admin default setelah setup
2. **Use HTTPS**: Selalu gunakan HTTPS di production
3. **Regular Updates**: Update dependencies secara berkala
4. **Monitor Logs**: Periksa activity_log secara rutin
5. **Backup Database**: Backup database secara berkala
6. **Strong Passwords**: Gunakan password yang kuat untuk admin

## Troubleshooting

### Login Issues
- **"Username atau password salah"**: Periksa credentials
- **"Akun terkunci"**: Tunggu 15 menit atau reset di database
- **"Kode captcha salah"**: Refresh halaman atau klik captcha

### Session Issues
- **"Session expired"**: Login ulang
- **"Remember me tidak bekerja"**: Periksa cookie settings browser
- **"Logout tidak bekerja"**: Clear browser cookies

### Database Issues
- **"Terjadi kesalahan sistem"**: Periksa database connection
- **"Table tidak ditemukan"**: Jalankan SQL schema

## Support

Untuk bantuan teknis atau pertanyaan, silakan hubungi:
- Email: admin@mtululalbab.sch.id
- Phone: +62-xxx-xxx-xxxx

---

**Version**: 1.0.0  
**Last Updated**: December 2024  
**Author**: MTs Ulul Albab Development Team 