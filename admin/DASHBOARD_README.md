# Admin Dashboard - MTs Ulul Albab

## Overview
Dashboard admin yang modern dan responsif untuk sistem PSB MTs Ulul Albab dengan fitur statistik real-time, grafik interaktif, dan manajemen data yang komprehensif.

## 🎯 Fitur Utama

### 📊 Statistics Cards
- **Total Pendaftar**: Menampilkan jumlah total pendaftar dengan ikon users
- **Berkas Lengkap**: Menampilkan jumlah berkas yang sudah diverifikasi
- **Siswa Diterima**: Menampilkan jumlah siswa yang lulus seleksi
- **Menunggu Verifikasi**: Menampilkan jumlah berkas yang perlu diverifikasi

### 📈 Charts & Analytics
- **Chart.js Integration**: Grafik pendaftaran 6 bulan terakhir
- **Toggle Chart Type**: Bisa beralih antara bar chart dan line chart
- **Responsive Charts**: Otomatis menyesuaikan ukuran layar
- **Real-time Data**: Data diambil langsung dari database

### 🔄 Recent Activities
- **Activity Logging**: Mencatat aktivitas admin terbaru
- **User Actions**: Menampilkan siapa melakukan apa
- **Time Stamps**: Waktu aktivitas yang akurat
- **View All Link**: Link ke halaman activity log lengkap

### ⚡ Quick Actions
- **Shortcut Buttons**: Akses cepat ke fitur utama
- **Notification Badges**: Menampilkan jumlah pending items
- **Hover Effects**: Animasi interaktif saat hover
- **Responsive Grid**: Layout yang menyesuaikan ukuran layar

### 🕒 System Widgets
- **Real-time Clock**: Jam yang update setiap detik
- **System Status**: Status server dan database
- **Last Update**: Waktu update data terakhir
- **Active Users**: Jumlah user yang sedang aktif

## 🏗️ Architecture

### File Structure
```
admin/
├── dashboard.php              # Main dashboard page
├── includes/
│   ├── header.php            # Header with navigation
│   ├── sidebar.php           # Sidebar navigation
│   └── footer.php            # Footer and closing tags
├── auth_check.php            # Authentication middleware
├── login.php                 # Login page
├── logout.php                # Logout functionality
└── README.md                 # Documentation
```

### Database Queries
```sql
-- Total Pendaftar
SELECT COUNT(*) as total FROM calon_siswa

-- Berkas Lengkap
SELECT COUNT(*) as total FROM calon_siswa WHERE status_verifikasi = 'verified'

-- Siswa Diterima
SELECT COUNT(*) as total FROM calon_siswa WHERE status_seleksi = 'lulus'

-- Menunggu Verifikasi
SELECT COUNT(*) as total FROM calon_siswa WHERE status_verifikasi = 'pending'

-- Chart Data (6 bulan terakhir)
SELECT 
    DATE_FORMAT(created_at, '%M %Y') as bulan,
    COUNT(*) as total
FROM calon_siswa 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY YEAR(created_at), MONTH(created_at)
ORDER BY created_at ASC

-- Recent Activities
SELECT 
    al.action,
    al.created_at,
    u.username,
    cs.nama_lengkap as student_name
FROM activity_log al
LEFT JOIN users u ON al.user_id = u.id
LEFT JOIN calon_siswa cs ON al.action LIKE CONCAT('%', cs.id, '%')
ORDER BY al.created_at DESC
LIMIT 10
```

## 🎨 Design Features

### Color Scheme
- **Primary**: Blue gradient (#667eea → #764ba2)
- **Success**: Green gradient (#10b981 → #059669)
- **Warning**: Orange gradient (#f59e0b → #d97706)
- **Danger**: Red gradient (#ef4444 → #dc2626)

### UI Components
- **Glassmorphism**: Modern glass effect design
- **Gradient Backgrounds**: Beautiful color transitions
- **Hover Animations**: Smooth interactive effects
- **Loading States**: Visual feedback during operations
- **Responsive Grid**: Flexible layout system

### Typography
- **Font Family**: Segoe UI, Tahoma, Geneva, Verdana, sans-serif
- **Headings**: Bold weights with proper hierarchy
- **Body Text**: Readable with good contrast
- **Icons**: Font Awesome 6.0 integration

## 📱 Responsive Design

### Breakpoints
- **Desktop**: > 1200px (Full layout)
- **Tablet**: 768px - 1200px (Adjusted grid)
- **Mobile**: < 768px (Single column)

### Mobile Features
- **Collapsible Sidebar**: Toggle menu for mobile
- **Touch-friendly**: Large touch targets
- **Optimized Layout**: Single column on small screens
- **Hidden Elements**: Non-essential widgets hidden

## ⚡ Performance Features

### Auto-refresh
- **30-second intervals**: Data refresh automatically
- **Background updates**: Non-intrusive updates
- **Loading indicators**: Visual feedback during refresh
- **Error handling**: Graceful fallback on errors

### Optimization
- **Efficient Queries**: Optimized database queries
- **Caching**: Session-based caching
- **Lazy Loading**: Components load as needed
- **Minimal Dependencies**: Only essential libraries

## 🔧 Technical Implementation

### JavaScript Features
```javascript
// Chart.js Configuration
let registrationChart = new Chart(ctx, {
    type: 'bar',
    data: { /* chart data */ },
    options: { /* chart options */ }
});

// Toggle Chart Type
function toggleChartType() {
    chartType = chartType === 'bar' ? 'line' : 'bar';
    registrationChart.destroy();
    initChart();
}

// Auto-refresh
setInterval(function() {
    refreshDashboardData();
}, 30000);

// Real-time Clock
setInterval(updateTime, 1000);
```

### CSS Features
```css
/* Grid Layout */
.dashboard-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
}

/* Hover Effects */
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

/* Loading Animation */
.loading::after {
    content: '';
    animation: spin 1s linear infinite;
}
```

## 🚀 Setup Instructions

### 1. Database Setup
```bash
# Import database schema
mysql -u username -p database_name < database/psb_online.sql
```

### 2. Create Admin User
```bash
# Run admin creation script
php admin/create_admin.php
```

### 3. Access Dashboard
```
URL: http://your-domain/admin/dashboard.php
Login: admin / admin123
```

### 4. Configuration
- Update `config/config.php` with database credentials
- Ensure proper file permissions
- Configure web server for PHP

## 📊 Data Sources

### Statistics Cards
- **Total Pendaftar**: `calon_siswa` table count
- **Berkas Lengkap**: Filtered by `status_verifikasi = 'verified'`
- **Siswa Diterima**: Filtered by `status_seleksi = 'lulus'`
- **Menunggu Verifikasi**: Filtered by `status_verifikasi = 'pending'`

### Chart Data
- **Time Range**: 6 months from current date
- **Grouping**: By month and year
- **Aggregation**: Count of registrations per month

### Activity Log
- **Source**: `activity_log` table
- **Limit**: 10 most recent activities
- **Joins**: With `users` and `calon_siswa` tables

## 🔒 Security Features

### Authentication
- **Session-based**: Secure session management
- **Role-based**: Different access levels
- **Timeout**: Automatic logout after inactivity
- **CSRF Protection**: Form token validation

### Data Protection
- **SQL Injection**: Prepared statements
- **XSS Prevention**: Input sanitization
- **Access Control**: File-level protection
- **Error Handling**: Secure error messages

## 🛠️ Customization

### Adding New Statistics
```php
// Add new stat query
$new_stat = $db->fetchOne("SELECT COUNT(*) as total FROM table_name WHERE condition")['total'] ?? 0;

// Add to stats grid
<div class="stat-card">
    <div class="stat-icon" style="background: linear-gradient(135deg, #color1, #color2);">
        <i class="fas fa-icon-name"></i>
    </div>
    <div class="stat-content">
        <h3>Statistic Name</h3>
        <div class="stat-number"><?php echo number_format($new_stat); ?></div>
    </div>
</div>
```

### Modifying Chart Data
```php
// Update chart query
$chart_data = $db->fetchAll("
    SELECT 
        DATE_FORMAT(created_at, '%M %Y') as bulan,
        COUNT(*) as total
    FROM your_table 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY created_at ASC
");
```

### Adding New Widgets
```php
// Create new widget
<div class="widget-card">
    <div class="widget-header">
        <h3>Widget Title</h3>
    </div>
    <div class="widget-content">
        <!-- Widget content -->
    </div>
</div>
```

## 📈 Performance Monitoring

### Key Metrics
- **Page Load Time**: < 2 seconds
- **Database Queries**: Optimized for speed
- **Memory Usage**: Efficient resource management
- **User Experience**: Smooth interactions

### Monitoring Tools
- **Browser DevTools**: Performance analysis
- **Database Logs**: Query performance
- **Error Logs**: System monitoring
- **User Analytics**: Usage tracking

## 🔄 Maintenance

### Regular Tasks
- **Database Backup**: Daily automated backups
- **Log Rotation**: Manage activity logs
- **Performance Tuning**: Optimize queries
- **Security Updates**: Keep dependencies updated

### Troubleshooting
- **Database Connection**: Check credentials
- **File Permissions**: Ensure proper access
- **Session Issues**: Clear browser cache
- **Chart Loading**: Check JavaScript console

## 📞 Support

### Documentation
- **Code Comments**: Inline documentation
- **API Reference**: Function documentation
- **User Guide**: Step-by-step instructions
- **FAQ**: Common questions and answers

### Contact
- **Technical Support**: admin@mtululalbab.sch.id
- **Bug Reports**: Submit via issue tracker
- **Feature Requests**: Development roadmap
- **Training**: User training sessions

---

**Version**: 1.0.0  
**Last Updated**: December 2024  
**Author**: MTs Ulul Albab Development Team  
**License**: Proprietary 