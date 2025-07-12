<?php
// Include database configuration
define('SECURE_ACCESS', true);
require_once 'config/config.php';

// Initialize variables
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'all';
$students = [];
$total_students = 0;

try {
    $db = Database::getInstance();
    
    // Build query based on filters
    $where_conditions = ["cs.status_seleksi IN ('lulus', 'cadangan')"];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(cs.nama_lengkap LIKE ? OR cs.nisn LIKE ? OR cs.nomor_daftar LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if ($status_filter !== 'all') {
        $where_conditions[] = "cs.status_seleksi = ?";
        $params[] = $status_filter;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM calon_siswa cs WHERE {$where_clause}";
    $total_students = $db->fetchValue($count_sql, $params);
    
    // Get students data
    $sql = "
        SELECT 
            cs.nomor_daftar,
            cs.nama_lengkap,
            cs.nisn,
            cs.asal_sekolah,
            cs.status_seleksi,
            cs.status_verifikasi,
            p.tanggal_submit,
            p.rata_rata_un
        FROM calon_siswa cs
        LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id
        WHERE {$where_clause}
        ORDER BY cs.status_seleksi DESC, p.rata_rata_un DESC, cs.nama_lengkap ASC
    ";
    
    $students = $db->fetchAll($sql, $params);
    
} catch (Exception $e) {
    $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman Hasil Seleksi - MTs Ulul Albab</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .announcement-container {
            max-width: 1200px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .announcement-header {
            background: linear-gradient(90deg, #2563eb 0%, #06b6d4 100%);
            color: #fff;
            padding: 2rem;
            text-align: center;
        }
        
        .announcement-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .announcement-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .controls-section {
            padding: 1.5rem 2rem;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .controls-row {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-box {
            flex: 1;
            min-width: 250px;
        }
        
        .search-box input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        
        .filter-dropdown {
            min-width: 150px;
        }
        
        .filter-dropdown select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            background: #fff;
            cursor: pointer;
        }
        
        .btn-print {
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            color: #fff;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
            color: #fff;
        }
        
        .table-container {
            padding: 2rem;
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .table th {
            background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
            color: #374151;
            font-weight: 700;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .table td {
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background: #f8fafc;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            text-align: center;
            min-width: 100px;
        }
        
        .status-diterima {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-cadangan {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-pending {
            background: #e5e7eb;
            color: #6b7280;
        }
        
        .stats-section {
            padding: 1rem 2rem;
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2563eb;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
        }
        
        .no-data {
            text-align: center;
            padding: 3rem 2rem;
            color: #6b7280;
        }
        
        .no-data-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        /* Print Styles */
        @media print {
            .header, .nav, .controls-section, .btn-print, .stats-section {
                display: none !important;
            }
            
            .announcement-container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .table {
                box-shadow: none;
            }
            
            .table th {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact;
            }
            
            .status-badge {
                -webkit-print-color-adjust: exact;
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .controls-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                min-width: auto;
            }
            
            .table-container {
                padding: 1rem;
            }
            
            .table {
                font-size: 0.9rem;
            }
            
            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
            }
            
            .stats-section {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-flex">
            <div class="logo">
                <img src="assets/images/logo.png" alt="Logo MTs Ulul Albab" />
                <span>MTs Ulul Albab</span>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="pendaftaran.php">Pendaftaran</a></li>
                    <li><a href="pengumuman.php" class="active">Pengumuman</a></li>
                    <li><a href="cek-status.php">Cek Status</a></li>
                    <li><a href="admin/login.php" class="btn-login">Login Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="announcement-container">
        <!-- Header -->
        <div class="announcement-header">
            <h1>PENGUMUMAN HASIL SELEKSI</h1>
            <p>Penerimaan Siswa Baru Tahun Ajaran 2024/2025</p>
            <p><strong>Madrasah Tsanawiyah Ulul Albab</strong></p>
        </div>

        <!-- Controls Section -->
        <div class="controls-section">
            <form method="GET" class="controls-row">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Cari berdasarkan nama, NISN, atau nomor daftar..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-dropdown">
                    <select name="status">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="lulus" <?php echo $status_filter === 'lulus' ? 'selected' : ''; ?>>Diterima</option>
                        <option value="cadangan" <?php echo $status_filter === 'cadangan' ? 'selected' : ''; ?>>Cadangan</option>
                    </select>
                </div>
                <button type="submit" class="btn-print">Cari</button>
                <button type="button" class="btn-print" onclick="printAnnouncement()">Cetak Pengumuman</button>
            </form>
        </div>

        <!-- Statistics -->
        <div class="stats-section">
            <div class="stat-item">
                <div class="stat-number"><?php echo $total_students; ?></div>
                <div class="stat-label">Total Siswa</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">
                    <?php 
                    $diterima_count = array_filter($students, function($s) { return $s['status_seleksi'] === 'lulus'; });
                    echo count($diterima_count);
                    ?>
                </div>
                <div class="stat-label">Diterima</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">
                    <?php 
                    $cadangan_count = array_filter($students, function($s) { return $s['status_seleksi'] === 'cadangan'; });
                    echo count($cadangan_count);
                    ?>
                </div>
                <div class="stat-label">Cadangan</div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <?php if (empty($students)): ?>
                <div class="no-data">
                    <div class="no-data-icon">📋</div>
                    <h3>Tidak ada data ditemukan</h3>
                    <p>Coba ubah filter pencarian Anda</p>
                </div>
            <?php else: ?>
                <table class="table" id="studentsTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Daftar</th>
                            <th>Nama Lengkap</th>
                            <th>NISN</th>
                            <th>Asal Sekolah</th>
                            <th>Rata-rata UN</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $student): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($student['nomor_daftar']); ?></strong></td>
                                <td><?php echo htmlspecialchars($student['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($student['nisn']); ?></td>
                                <td><?php echo htmlspecialchars($student['asal_sekolah']); ?></td>
                                <td>
                                    <?php 
                                    if ($student['rata_rata_un']) {
                                        echo number_format($student['rata_rata_un'], 2);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $status_class = 'status-pending';
                                    $status_text = 'Menunggu';
                                    
                                    if ($student['status_seleksi'] === 'lulus') {
                                        $status_class = 'status-diterima';
                                        $status_text = 'DITERIMA';
                                    } elseif ($student['status_seleksi'] === 'cadangan') {
                                        $status_class = 'status-cadangan';
                                        $status_text = 'CADANGAN';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Print function
        function printAnnouncement() {
            // Create print window
            const printWindow = window.open('', '_blank');
            const currentUrl = window.location.href;
            
            // Get current filters
            const searchParams = new URLSearchParams(window.location.search);
            const search = searchParams.get('search') || '';
            const status = searchParams.get('status') || 'all';
            
            // Create print content
            const printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Pengumuman Hasil Seleksi - MTs Ulul Albab</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                        .print-header { text-align: center; margin-bottom: 30px; }
                        .print-header h1 { color: #2563eb; margin-bottom: 10px; }
                        .print-header p { margin: 5px 0; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                        th { background-color: #f8fafc; font-weight: bold; }
                        .status-badge { padding: 5px 10px; border-radius: 15px; font-weight: bold; font-size: 12px; }
                        .status-diterima { background-color: #d1fae5; color: #065f46; }
                        .status-cadangan { background-color: #fef3c7; color: #92400e; }
                        .print-footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
                        @media print { body { margin: 0; } }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <h1>PENGUMUMAN HASIL SELEKSI</h1>
                        <p>Penerimaan Siswa Baru Tahun Ajaran 2024/2025</p>
                        <p><strong>Madrasah Tsanawiyah Ulul Albab</strong></p>
                        <p>Tanggal: ${new Date().toLocaleDateString('id-ID')}</p>
                        ${search ? `<p>Filter: ${search}</p>` : ''}
                        ${status !== 'all' ? `<p>Status: ${status === 'lulus' ? 'Diterima' : 'Cadangan'}</p>` : ''}
                    </div>
                    
                    <div id="print-content">
                        <p>Memuat data...</p>
                    </div>
                    
                    <div class="print-footer">
                        <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                        <p>MTs Ulul Albab - Sistem PSB Online</p>
                    </div>
                </body>
                </html>
            `;
            
            printWindow.document.write(printContent);
            printWindow.document.close();
            
            // Load data via AJAX
            fetch(`${currentUrl}?${searchParams.toString()}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const table = doc.querySelector('.table');
                    
                    if (table) {
                        printWindow.document.getElementById('print-content').innerHTML = table.outerHTML;
                        printWindow.print();
                    } else {
                        printWindow.document.getElementById('print-content').innerHTML = '<p>Tidak ada data untuk dicetak</p>';
                    }
                })
                .catch(error => {
                    printWindow.document.getElementById('print-content').innerHTML = '<p>Error memuat data</p>';
                    console.error('Print error:', error);
                });
        }

        // Auto-submit form on filter change
        document.querySelector('select[name="status"]').addEventListener('change', function() {
            this.form.submit();
        });

        // Search with debounce
        let searchTimeout;
        document.querySelector('input[name="search"]').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 500);
        });

        // Initialize DataTable if jQuery is available
        if (typeof $ !== 'undefined') {
            $(document).ready(function() {
                $('#studentsTable').DataTable({
                    pageLength: 25,
                    order: [[0, 'asc']],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                    },
                    responsive: true
                });
            });
        }
    </script>
</body>
</html> 