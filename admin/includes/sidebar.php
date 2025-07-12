<!-- Sidebar Navigation -->
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <img src="../assets/images/logo.png" alt="Logo MTs Ulul Albab" />
            <span>MTs Ulul Albab</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="nav-section-title">Pendaftaran</span>
            </li>
            
            <li class="nav-item">
                <a href="pendaftaran-list.php" class="nav-link <?php echo $current_page === 'pendaftaran-list' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i>
                    <span>Daftar Pendaftar</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="verifikasi.php" class="nav-link <?php echo $current_page === 'verifikasi' ? 'active' : ''; ?>">
                    <i class="fas fa-check-circle"></i>
                    <span>Verifikasi Berkas</span>
                    <span class="badge">5</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="jadwal-tes.php" class="nav-link <?php echo $current_page === 'jadwal-tes' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Jadwal Tes</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="nav-section-title">Seleksi</span>
            </li>
            
            <li class="nav-item">
                <a href="hasil-seleksi.php" class="nav-link <?php echo $current_page === 'hasil-seleksi' ? 'active' : ''; ?>">
                    <i class="fas fa-trophy"></i>
                    <span>Hasil Seleksi</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="pengumuman.php" class="nav-link <?php echo $current_page === 'pengumuman' ? 'active' : ''; ?>">
                    <i class="fas fa-bullhorn"></i>
                    <span>Pengumuman</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="nav-section-title">Laporan</span>
            </li>
            
            <li class="nav-item">
                <a href="laporan-pendaftaran.php" class="nav-link <?php echo $current_page === 'laporan-pendaftaran' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan Pendaftaran</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="laporan-seleksi.php" class="nav-link <?php echo $current_page === 'laporan-seleksi' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Laporan Seleksi</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="export-data.php" class="nav-link <?php echo $current_page === 'export-data' ? 'active' : ''; ?>">
                    <i class="fas fa-download"></i>
                    <span>Export Data</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="nav-section-title">Sistem</span>
            </li>
            
            <li class="nav-item">
                <a href="pengaturan.php" class="nav-link <?php echo $current_page === 'pengaturan' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Pengaturan</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="users.php" class="nav-link <?php echo $current_page === 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Manajemen User</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="backup.php" class="nav-link <?php echo $current_page === 'backup' ? 'active' : ''; ?>">
                    <i class="fas fa-database"></i>
                    <span>Backup Database</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="activity-log.php" class="nav-link <?php echo $current_page === 'activity-log' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i>
                    <span>Activity Log</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <div class="system-info">
            <div class="info-item">
                <i class="fas fa-server"></i>
                <span>Server: Online</span>
            </div>
            <div class="info-item">
                <i class="fas fa-database"></i>
                <span>DB: Connected</span>
            </div>
        </div>
    </div>
</aside>

<style>
    /* Sidebar Styles */
    .admin-sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: 250px;
        height: 100vh;
        background: white;
        border-right: 1px solid #e5e7eb;
        z-index: 200;
        transition: transform 0.3s ease;
        overflow-y: auto;
    }
    
    .sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .logo img {
        width: 40px;
        height: 40px;
        border-radius: 8px;
    }
    
    .logo span {
        font-weight: 600;
        color: #1f2937;
        font-size: 1.1rem;
    }
    
    .sidebar-nav {
        padding: 1rem 0;
    }
    
    .nav-menu {
        list-style: none;
    }
    
    .nav-section {
        padding: 0.75rem 1.5rem 0.5rem;
    }
    
    .nav-section-title {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .nav-item {
        margin: 0.25rem 0;
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.5rem;
        color: #6b7280;
        text-decoration: none;
        transition: all 0.3s;
        position: relative;
    }
    
    .nav-link:hover {
        background: #f8fafc;
        color: #374151;
    }
    
    .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .nav-link.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #667eea;
    }
    
    .nav-link i {
        width: 20px;
        text-align: center;
        font-size: 1rem;
    }
    
    .nav-link span {
        flex: 1;
        font-weight: 500;
    }
    
    .badge {
        background: #ef4444;
        color: white;
        border-radius: 12px;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        min-width: 20px;
        text-align: center;
    }
    
    .sidebar-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        background: #f8fafc;
    }
    
    .system-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: #6b7280;
    }
    
    .info-item i {
        width: 16px;
        text-align: center;
        color: #22c55e;
    }
    
    /* Responsive Sidebar */
    @media (max-width: 1024px) {
        .admin-sidebar {
            transform: translateX(-100%);
        }
        
        body.sidebar-open .admin-sidebar {
            transform: translateX(0);
        }
        
        body.sidebar-open::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 150;
        }
    }
    
    /* Scrollbar Styling */
    .admin-sidebar::-webkit-scrollbar {
        width: 6px;
    }
    
    .admin-sidebar::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    
    .admin-sidebar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    
    .admin-sidebar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<script>
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 1024) {
            const sidebar = document.querySelector('.admin-sidebar');
            const menuToggle = document.getElementById('menuToggle');
            
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                document.body.classList.remove('sidebar-open');
            }
        }
    });
    
    // Add active class to current page
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
                link.classList.add('active');
            }
        });
    });
</script> 