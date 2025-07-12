<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Include database configuration
define('SECURE_ACCESS', true);
require_once '../config/config.php';

$admin_username = $_SESSION['admin_username'];
$admin_role = $_SESSION['admin_role'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MTs Ulul Albab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #1f2937;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .welcome-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .welcome-card h2 {
            color: #1f2937;
            margin-bottom: 1rem;
            font-size: 2rem;
        }
        
        .welcome-card p {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #667eea;
        }
        
        .stat-card h3 {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #1f2937;
        }
        
        .quick-actions {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .quick-actions h3 {
            margin-bottom: 1.5rem;
            color: #1f2937;
        }
        
        .action-grid {
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
        }
        
        .action-btn:hover {
            border-color: #667eea;
            background: #f0f9ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102,126,234,0.2);
        }
        
        .action-btn i {
            font-size: 1.2rem;
            color: #667eea;
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }
            
            .container {
                padding: 0 1rem;
                margin: 1rem auto;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .action-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($admin_username, 0, 1)); ?>
            </div>
            <span><?php echo htmlspecialchars($admin_username); ?> (<?php echo ucfirst($admin_role); ?>)</span>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </header>

    <div class="container">
        <div class="welcome-card">
            <h2>Selamat Datang di Panel Admin</h2>
            <p>MTs Ulul Albab - Sistem Penerimaan Siswa Baru</p>
            <p><strong>Login terakhir:</strong> <?php echo date('d/m/Y H:i'); ?></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Pendaftar</h3>
                <div class="number">0</div>
            </div>
            <div class="stat-card">
                <h3>Menunggu Verifikasi</h3>
                <div class="number">0</div>
            </div>
            <div class="stat-card">
                <h3>Sudah Diverifikasi</h3>
                <div class="number">0</div>
            </div>
            <div class="stat-card">
                <h3>Diterima</h3>
                <div class="number">0</div>
            </div>
        </div>

        <div class="quick-actions">
            <h3>Menu Cepat</h3>
            <div class="action-grid">
                <a href="pendaftaran-list.php" class="action-btn">
                    <i class="fas fa-list"></i>
                    <span>Daftar Pendaftar</span>
                </a>
                <a href="verifikasi.php" class="action-btn">
                    <i class="fas fa-check-circle"></i>
                    <span>Verifikasi Berkas</span>
                </a>
                <a href="jadwal-tes.php" class="action-btn">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Jadwal Tes</span>
                </a>
                <a href="pengumuman.php" class="action-btn">
                    <i class="fas fa-bullhorn"></i>
                    <span>Pengumuman</span>
                </a>
                <a href="laporan.php" class="action-btn">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan</span>
                </a>
                <a href="pengaturan.php" class="action-btn">
                    <i class="fas fa-cog"></i>
                    <span>Pengaturan</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to stat cards
            document.querySelectorAll('.stat-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)';
                });
            });
        });
    </script>
</body>
</html> 