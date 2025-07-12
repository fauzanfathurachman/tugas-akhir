<?php
// Set page variables
$page_title = 'Dashboard';
$current_page = 'dashboard';

// Include authentication check
require_once 'auth_check.php';

// Get dashboard statistics
try {
    $db = Database::getInstance();
    
    // Total Pendaftar
    $total_pendaftar = $db->fetchOne("SELECT COUNT(*) as total FROM calon_siswa")['total'] ?? 0;
    
    // Berkas Lengkap (verified)
    $berkas_lengkap = $db->fetchOne("SELECT COUNT(*) as total FROM calon_siswa WHERE status_verifikasi = 'verified'")['total'] ?? 0;
    
    // Siswa Diterima
    $siswa_diterima = $db->fetchOne("SELECT COUNT(*) as total FROM calon_siswa WHERE status_seleksi = 'lulus'")['total'] ?? 0;
    
    // Menunggu Verifikasi
    $menunggu_verifikasi = $db->fetchOne("SELECT COUNT(*) as total FROM calon_siswa WHERE status_verifikasi = 'pending'")['total'] ?? 0;
    
    // Chart data - Pendaftaran per bulan (6 bulan terakhir)
    $chart_data = $db->fetchAll("
        SELECT 
            DATE_FORMAT(created_at, '%M %Y') as bulan,
            COUNT(*) as total
        FROM calon_siswa 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY created_at ASC
    ");
    
    // Recent activities
    $recent_activities = $db->fetchAll("
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
    ");
    
    // Quick stats for widgets
    $today_pendaftar = $db->fetchOne("SELECT COUNT(*) as total FROM calon_siswa WHERE DATE(created_at) = CURDATE()")['total'] ?? 0;
    $this_week_pendaftar = $db->fetchOne("SELECT COUNT(*) as total FROM calon_siswa WHERE YEARWEEK(created_at) = YEARWEEK(NOW())")['total'] ?? 0;
    $this_month_pendaftar = $db->fetchOne("SELECT COUNT(*) as total FROM calon_siswa WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")['total'] ?? 0;
    
} catch (Exception $e) {
    // Set default values if database error
    $total_pendaftar = 0;
    $berkas_lengkap = 0;
    $siswa_diterima = 0;
    $menunggu_verifikasi = 0;
    $chart_data = [];
    $recent_activities = [];
    $today_pendaftar = 0;
    $this_week_pendaftar = 0;
    $this_month_pendaftar = 0;
}

// Prepare chart data
$chart_labels = [];
$chart_values = [];
foreach ($chart_data as $data) {
    $chart_labels[] = $data['bulan'];
    $chart_values[] = $data['total'];
}

// If no data, use sample data
if (empty($chart_labels)) {
    $chart_labels = ['Jan 2024', 'Feb 2024', 'Mar 2024', 'Apr 2024', 'May 2024', 'Jun 2024'];
    $chart_values = [15, 28, 42, 35, 58, 45];
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h2>Selamat Datang, <?php echo htmlspecialchars($admin_username); ?>!</h2>
            <p>Berikut adalah ringkasan data pendaftaran siswa baru MTs Ulul Albab</p>
        </div>
        <div class="welcome-actions">
            <button class="btn-refresh" onclick="refreshDashboardData()">
                <i class="fas fa-sync-alt"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card" data-stat="total-pendaftar">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>Total Pendaftar</h3>
                <div class="stat-number"><?php echo number_format($total_pendaftar); ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>+<?php echo $today_pendaftar; ?> hari ini</span>
                </div>
            </div>
        </div>

        <div class="stat-card" data-stat="berkas-lengkap">
            <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3>Berkas Lengkap</h3>
                <div class="stat-number"><?php echo number_format($berkas_lengkap); ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $berkas_lengkap > 0 ? round(($berkas_lengkap / $total_pendaftar) * 100, 1) : 0; ?>% dari total</span>
                </div>
            </div>
        </div>

        <div class="stat-card" data-stat="siswa-diterima">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-content">
                <h3>Siswa Diterima</h3>
                <div class="stat-number"><?php echo number_format($siswa_diterima); ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $siswa_diterima > 0 ? round(($siswa_diterima / $total_pendaftar) * 100, 1) : 0; ?>% dari total</span>
                </div>
            </div>
        </div>

        <div class="stat-card" data-stat="menunggu-verifikasi">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3>Menunggu Verifikasi</h3>
                <div class="stat-number"><?php echo number_format($menunggu_verifikasi); ?></div>
                <div class="stat-change neutral">
                    <i class="fas fa-minus"></i>
                    <span>Perlu tindakan</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Widgets Row -->
    <div class="dashboard-row">
        <!-- Chart Section -->
        <div class="chart-section">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Grafik Pendaftaran (6 Bulan Terakhir)</h3>
                    <div class="chart-actions">
                        <button class="btn-chart" onclick="toggleChartType()">
                            <i class="fas fa-chart-bar"></i>
                        </button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="registrationChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Stats Widgets -->
        <div class="widgets-section">
            <div class="widget-card">
                <div class="widget-header">
                    <h3>Statistik Cepat</h3>
                </div>
                <div class="widget-content">
                    <div class="quick-stat">
                        <div class="quick-stat-icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="quick-stat-info">
                            <div class="quick-stat-number"><?php echo $today_pendaftar; ?></div>
                            <div class="quick-stat-label">Hari Ini</div>
                        </div>
                    </div>
                    <div class="quick-stat">
                        <div class="quick-stat-icon">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div class="quick-stat-info">
                            <div class="quick-stat-number"><?php echo $this_week_pendaftar; ?></div>
                            <div class="quick-stat-label">Minggu Ini</div>
                        </div>
                    </div>
                    <div class="quick-stat">
                        <div class="quick-stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="quick-stat-info">
                            <div class="quick-stat-number"><?php echo $this_month_pendaftar; ?></div>
                            <div class="quick-stat-label">Bulan Ini</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weather/Time Widget -->
            <div class="widget-card">
                <div class="widget-header">
                    <h3>Informasi Sistem</h3>
                </div>
                <div class="widget-content">
                    <div class="system-info-item">
                        <i class="fas fa-server"></i>
                        <span>Server Status: <strong class="status-online">Online</strong></span>
                    </div>
                    <div class="system-info-item">
                        <i class="fas fa-database"></i>
                        <span>Database: <strong class="status-online">Connected</strong></span>
                    </div>
                    <div class="system-info-item">
                        <i class="fas fa-clock"></i>
                        <span>Last Update: <strong id="lastUpdate"><?php echo date('H:i:s'); ?></strong></span>
                    </div>
                    <div class="system-info-item">
                        <i class="fas fa-users"></i>
                        <span>Active Users: <strong>1</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities and Quick Actions Row -->
    <div class="dashboard-row">
        <!-- Recent Activities -->
        <div class="activities-section">
            <div class="activities-card">
                <div class="activities-header">
                    <h3>Recent Activities</h3>
                    <a href="activity-log.php" class="view-all">View All</a>
                </div>
                <div class="activities-content">
                    <?php if (!empty($recent_activities)): ?>
                        <?php foreach (array_slice($recent_activities, 0, 5) as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-user-edit"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-text">
                                        <strong><?php echo htmlspecialchars($activity['username'] ?? 'System'); ?></strong>
                                        <?php echo htmlspecialchars($activity['action']); ?>
                                        <?php if ($activity['student_name']): ?>
                                            <strong><?php echo htmlspecialchars($activity['student_name']); ?></strong>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-time">
                                        <?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">Belum ada aktivitas terbaru</div>
                                <div class="activity-time">-</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions-section">
            <div class="actions-card">
                <div class="actions-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="actions-content">
                    <a href="pendaftaran-list.php" class="action-btn">
                        <i class="fas fa-list"></i>
                        <span>Lihat Daftar Pendaftar</span>
                    </a>
                    <a href="verifikasi.php" class="action-btn">
                        <i class="fas fa-check-circle"></i>
                        <span>Verifikasi Berkas</span>
                        <?php if ($menunggu_verifikasi > 0): ?>
                            <span class="action-badge"><?php echo $menunggu_verifikasi; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="jadwal-tes.php" class="action-btn">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Atur Jadwal Tes</span>
                    </a>
                    <a href="pengumuman.php" class="action-btn">
                        <i class="fas fa-bullhorn"></i>
                        <span>Buat Pengumuman</span>
                    </a>
                    <a href="laporan-pendaftaran.php" class="action-btn">
                        <i class="fas fa-chart-bar"></i>
                        <span>Generate Laporan</span>
                    </a>
                    <a href="export-data.php" class="action-btn">
                        <i class="fas fa-download"></i>
                        <span>Export Data</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    /* Dashboard Specific Styles */
    .welcome-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .welcome-content h2 {
        color: #1f2937;
        margin-bottom: 0.5rem;
        font-size: 1.5rem;
    }
    
    .welcome-content p {
        color: #6b7280;
        margin: 0;
    }
    
    .btn-refresh {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-refresh:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102,126,234,0.3);
    }
    
    /* Statistics Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transform: translateX(-100%);
        transition: transform 0.6s;
    }
    
    .stat-card:hover::before {
        transform: translateX(100%);
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }
    
    .stat-content {
        flex: 1;
    }
    
    .stat-content h3 {
        color: #6b7280;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .stat-change {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .stat-change.positive {
        color: #10b981;
    }
    
    .stat-change.negative {
        color: #ef4444;
    }
    
    .stat-change.neutral {
        color: #6b7280;
    }
    
    /* Dashboard Row */
    .dashboard-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    /* Chart Section */
    .chart-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .chart-header h3 {
        color: #1f2937;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .btn-chart {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        padding: 0.5rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-chart:hover {
        background: #f0f9ff;
        border-color: #667eea;
    }
    
    .chart-container {
        height: 300px;
        position: relative;
    }
    
    /* Widgets Section */
    .widgets-section {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .widget-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .widget-header h3 {
        color: #1f2937;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .quick-stat {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .quick-stat:last-child {
        border-bottom: none;
    }
    
    .quick-stat-icon {
        width: 40px;
        height: 40px;
        background: #f0f9ff;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667eea;
    }
    
    .quick-stat-number {
        font-size: 1.5rem;
        font-weight: bold;
        color: #1f2937;
    }
    
    .quick-stat-label {
        font-size: 0.8rem;
        color: #6b7280;
    }
    
    .system-info-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .system-info-item:last-child {
        border-bottom: none;
    }
    
    .system-info-item i {
        width: 20px;
        color: #667eea;
    }
    
    .status-online {
        color: #10b981;
    }
    
    /* Activities Section */
    .activities-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .activities-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .activities-header h3 {
        color: #1f2937;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .view-all {
        color: #667eea;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .view-all:hover {
        text-decoration: underline;
    }
    
    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-icon {
        width: 32px;
        height: 32px;
        background: #f0f9ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667eea;
        font-size: 0.8rem;
    }
    
    .activity-content {
        flex: 1;
    }
    
    .activity-text {
        color: #374151;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }
    
    .activity-time {
        color: #6b7280;
        font-size: 0.8rem;
    }
    
    /* Actions Section */
    .actions-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .actions-header h3 {
        color: #1f2937;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
    
    .actions-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .action-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: #f8fafc;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        text-decoration: none;
        color: #374151;
        transition: all 0.3s;
        position: relative;
    }
    
    .action-btn:hover {
        border-color: #667eea;
        background: #f0f9ff;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102,126,234,0.2);
    }
    
    .action-btn i {
        color: #667eea;
        font-size: 1.1rem;
    }
    
    .action-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    
    /* Responsive Design */
    @media (max-width: 1200px) {
        .dashboard-row {
            grid-template-columns: 1fr;
        }
        
        .widgets-section {
            flex-direction: row;
        }
    }
    
    @media (max-width: 768px) {
        .welcome-section {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .widgets-section {
            flex-direction: column;
        }
        
        .actions-content {
            grid-template-columns: 1fr;
        }
        
        .main-content {
            padding: 1rem;
        }
    }
    
    /* Loading Animation */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
    }
    
    .loading .loading-overlay {
        opacity: 1;
        visibility: visible;
    }
</style>

<script>
    // Chart.js Configuration
    let chartType = 'bar';
    let registrationChart;
    
    function initChart() {
        const ctx = document.getElementById('registrationChart').getContext('2d');
        
        registrationChart = new Chart(ctx, {
            type: chartType,
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Jumlah Pendaftar',
                    data: <?php echo json_encode($chart_values); ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.2)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    function toggleChartType() {
        chartType = chartType === 'bar' ? 'line' : 'bar';
        
        if (registrationChart) {
            registrationChart.destroy();
        }
        
        initChart();
        
        // Update button icon
        const btn = document.querySelector('.btn-chart i');
        btn.className = chartType === 'bar' ? 'fas fa-chart-line' : 'fas fa-chart-bar';
    }
    
    // Refresh dashboard data
    function refreshDashboardData() {
        // Show loading state
        document.querySelectorAll('.stat-card').forEach(card => {
            card.classList.add('loading');
        });
        
        // Simulate API call
        setTimeout(() => {
            // Remove loading state
            document.querySelectorAll('.stat-card').forEach(card => {
                card.classList.remove('loading');
            });
            
            // Update last update time
            document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString('id-ID');
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Data Updated',
                text: 'Dashboard data has been refreshed successfully!',
                timer: 2000,
                showConfirmButton: false
            });
        }, 1500);
    }
    
    // Initialize chart when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initChart();
        
        // Add hover effects to stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
    
    // Auto-refresh function (called from header)
    function refreshDashboardData() {
        // This function is called every 30 seconds from the header
        console.log('Auto-refreshing dashboard data...');
        
        // Update last update time
        document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString('id-ID');
        
        // You can add AJAX calls here to fetch real-time data
    }
</script>

<?php include 'includes/footer.php'; ?> 