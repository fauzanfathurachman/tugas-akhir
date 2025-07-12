<?php
// Include database configuration
define('SECURE_ACCESS', true);
require_once 'config/config.php';

// Get filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'all';
$students = [];

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
    
    // Get students data
    $sql = "
        SELECT 
            cs.nomor_daftar,
            cs.nama_lengkap,
            cs.nisn,
            cs.asal_sekolah,
            cs.status_seleksi,
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
    <style>
        @media print {
            body { margin: 0; padding: 20px; }
            .no-print { display: none !important; }
        }
        
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #000;
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
        }
        
        .school-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 10px;
            display: block;
        }
        
        .print-header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin: 10px 0;
            color: #000;
        }
        
        .print-header h2 {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
            color: #000;
        }
        
        .print-header p {
            font-size: 12pt;
            margin: 5px 0;
        }
        
        .print-info {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #000;
            background: #f9f9f9;
        }
        
        .print-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .print-info td {
            padding: 5px;
            font-size: 11pt;
        }
        
        .print-info td:first-child {
            font-weight: bold;
            width: 150px;
        }
        
        .students-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10pt;
        }
        
        .students-table th,
        .students-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }
        
        .students-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .students-table td:first-child {
            text-align: center;
            font-weight: bold;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9pt;
            text-align: center;
            display: inline-block;
            min-width: 80px;
        }
        
        .status-diterima {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-cadangan {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .print-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10pt;
            border-top: 1px solid #000;
            padding-top: 20px;
        }
        
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            margin-bottom: 10px;
        }
        
        .no-data {
            text-align: center;
            padding: 50px 20px;
            font-size: 12pt;
            color: #666;
        }
        
        .print-actions {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #f0f0f0;
            border-radius: 5px;
        }
        
        .print-btn {
            background: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12pt;
            margin: 0 10px;
        }
        
        .print-btn:hover {
            background: #0056b3;
        }
        
        .back-btn {
            background: #6c757d;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12pt;
            margin: 0 10px;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-btn:hover {
            background: #545b62;
            color: #fff;
        }
    </style>
</head>
<body>
    <!-- Print Actions -->
    <div class="print-actions no-print">
        <button class="print-btn" onclick="window.print()">Cetak Pengumuman</button>
        <a href="pengumuman.php" class="back-btn">Kembali ke Pengumuman</a>
    </div>

    <!-- Header -->
    <div class="print-header">
        <img src="assets/images/logo.png" alt="Logo MTs Ulul Albab" class="school-logo">
        <h1>MADRASAH TSANAWIYAH ULUL ALBAB</h1>
        <h2>PENGUMUMAN HASIL SELEKSI</h2>
        <p>Penerimaan Siswa Baru Tahun Ajaran 2024/2025</p>
        <p>Nomor: 001/PSB/MTs-UA/2024</p>
    </div>

    <!-- Print Info -->
    <div class="print-info">
        <table>
            <tr>
                <td>Tanggal Pengumuman:</td>
                <td><?php echo date('d F Y', time()); ?></td>
            </tr>
            <tr>
                <td>Total Pendaftar:</td>
                <td><?php echo count($students); ?> siswa</td>
            </tr>
            <tr>
                <td>Filter Pencarian:</td>
                <td>
                    <?php 
                    if (!empty($search)) echo "Kata kunci: " . htmlspecialchars($search) . " ";
                    if ($status_filter !== 'all') echo "Status: " . ($status_filter === 'lulus' ? 'Diterima' : 'Cadangan');
                    if (empty($search) && $status_filter === 'all') echo "Semua data";
                    ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- Students Table -->
    <?php if (empty($students)): ?>
        <div class="no-data">
            <h3>Tidak ada data ditemukan</h3>
            <p>Berdasarkan filter yang dipilih</p>
        </div>
    <?php else: ?>
        <table class="students-table">
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
                <?php 
                $diterima_count = 0;
                $cadangan_count = 0;
                foreach ($students as $index => $student): 
                    if ($student['status_seleksi'] === 'lulus') $diterima_count++;
                    if ($student['status_seleksi'] === 'cadangan') $cadangan_count++;
                ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><strong><?php echo htmlspecialchars($student['nomor_daftar']); ?></strong></td>
                        <td><?php echo htmlspecialchars($student['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($student['nisn']); ?></td>
                        <td><?php echo htmlspecialchars($student['asal_sekolah']); ?></td>
                        <td style="text-align: center;">
                            <?php 
                            if ($student['rata_rata_un']) {
                                echo number_format($student['rata_rata_un'], 2);
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td style="text-align: center;">
                            <?php
                            $status_class = 'status-diterima';
                            $status_text = 'DITERIMA';
                            
                            if ($student['status_seleksi'] === 'cadangan') {
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

        <!-- Summary -->
        <div style="margin-top: 20px; padding: 15px; border: 1px solid #000; background: #f9f9f9;">
            <h3 style="margin: 0 0 10px 0; font-size: 12pt;">RINGKASAN:</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 5px; font-weight: bold;">Total Siswa Diterima:</td>
                    <td style="padding: 5px;"><?php echo $diterima_count; ?> siswa</td>
                </tr>
                <tr>
                    <td style="padding: 5px; font-weight: bold;">Total Siswa Cadangan:</td>
                    <td style="padding: 5px;"><?php echo $cadangan_count; ?> siswa</td>
                </tr>
                <tr>
                    <td style="padding: 5px; font-weight: bold;">Total Keseluruhan:</td>
                    <td style="padding: 5px;"><?php echo count($students); ?> siswa</td>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Kepala Madrasah</p>
            <p>MTs Ulul Albab</p>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Ketua Panitia PSB</p>
            <p>MTs Ulul Albab</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="print-footer">
        <p><strong>Catatan:</strong></p>
        <p>1. Siswa yang dinyatakan DITERIMA wajib melakukan daftar ulang pada tanggal 5-10 Maret 2024</p>
        <p>2. Siswa CADANGAN akan dipanggil jika ada kuota yang tersedia</p>
        <p>3. Pengumuman ini sah dan berlaku sejak tanggal ditetapkan</p>
        <p style="margin-top: 20px;">Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html> 