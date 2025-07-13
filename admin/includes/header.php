<?php
// Include authentication check
require_once 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> - MTs Ulul Albab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            overflow-x: hidden;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Header Styles */
        .admin-header {
            position: fixed;
            top: 0;
            right: 0;
            left: 250px;
            height: 70px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 100;
            transition: left 0.3s ease;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #6b7280;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .menu-toggle:hover {
            background: #f3f4f6;
            color: #374151;
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .header-widgets {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .widget {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f8fafc;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #6b7280;
        }
        
        .widget i {
            color: #667eea;
        }
        
        .user-menu {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: #f8fafc;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .user-menu:hover {
            background: #f0f9ff;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 0.9rem;
        }
        
        .user-role {
            font-size: 0.8rem;
            color: #6b7280;
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .user-menu:hover .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #374151;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .dropdown-item:hover {
            background: #f8fafc;
        }
        
        .dropdown-item.logout {
            color: #dc2626;
            border-top: 1px solid #e5e7eb;
        }
        
        .dropdown-item.logout:hover {
            background: #fef2f2;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            margin-top: 70px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .admin-header {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .header-widgets {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .admin-header {
                padding: 0 1rem;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .page-title {
                font-size: 1.25rem;
            }
            
            .user-info {
                display: none;
            }
        }
        
        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f4f6;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Notifications */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title"><?php echo $page_title ?? 'Dashboard'; ?></h1>
            </div>
            
            <div class="header-right">
                <div class="header-widgets">
                    <div class="widget">
                        <i class="fas fa-clock"></i>
                        <span id="currentTime"><?php echo date('H:i'); ?></span>
                    </div>
                    <div class="widget">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo date('d/m/Y'); ?></span>
                    </div>
                    <div class="widget">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                </div>
                
                <div class="user-menu">
                    <div class="user-avatar">
                        <?php echo isset($admin_username) && $admin_username !== null && $admin_username !== '' ? strtoupper(substr($admin_username, 0, 1)) : ''; ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($admin_username ?? ''); ?></div>
                        <div class="user-role"><?php echo $admin_role ? ucfirst($admin_role) : ''; ?></div>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                    
                    <div class="user-dropdown">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                        <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                        <a href="logout.php" class="dropdown-item logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <script>
            // Update current time
            function updateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                document.getElementById('currentTime').textContent = timeString;
            }
            
            // Update time every second
            setInterval(updateTime, 1000);
            
            // Mobile menu toggle
            document.getElementById('menuToggle').addEventListener('click', function() {
                document.body.classList.toggle('sidebar-open');
            });
            
            // Auto-refresh data every 30 seconds
            setInterval(function() {
                // Refresh dashboard data
                if (typeof refreshDashboardData === 'function') {
                    refreshDashboardData();
                }
            }, 30000);
        </script> 