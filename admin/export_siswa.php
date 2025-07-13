<?php
require_once '../config/database.php';
// Tidak perlu cek login, langsung izinkan akses (untuk demo/tugas kecil)

$type = $_GET['type'] ?? 'excel';
$db = Database::getInstance();

try {
    // Get all student data
    $query = "SELECT cs.*, p.status_verifikasi, p.status_seleksi, p.nomor_pendaftaran, 
                     p.tanggal_daftar, p.catatan_verifikasi, p.nilai_tes
              FROM calon_siswa cs 
              LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id 
              ORDER BY cs.nama ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($type === 'excel') {
        exportToExcel($data);
    } elseif ($type === 'pdf') {
        exportToPDF($data);
    } else {
        echo "Invalid export type";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

function exportToExcel($data) {
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="data_siswa_' . date('Y-m-d_H-i-s') . '.xls"');
    header('Cache-Control: max-age=0');
    
    // Start output buffering
    ob_start();
    
    echo '<table border="1">';
    echo '<tr style="background-color: #f0f0f0; font-weight: bold;">';
    echo '<th>No</th>';
    echo '<th>Nomor Pendaftaran</th>';
    echo '<th>Nama</th>';
    echo '<th>NISN</th>';
    echo '<th>Tempat Lahir</th>';
    echo '<th>Tanggal Lahir</th>';
    echo '<th>Jenis Kelamin</th>';
    echo '<th>Agama</th>';
    echo '<th>Alamat</th>';
    echo '<th>Telepon</th>';
    echo '<th>Email</th>';
    echo '<th>Asal Sekolah</th>';
    echo '<th>Nama Ayah</th>';
    echo '<th>Pekerjaan Ayah</th>';
    echo '<th>Pendapatan Ayah</th>';
    echo '<th>Nama Ibu</th>';
    echo '<th>Pekerjaan Ibu</th>';
    echo '<th>Pendapatan Ibu</th>';
    echo '<th>Status Verifikasi</th>';
    echo '<th>Status Seleksi</th>';
    echo '<th>Nilai Tes</th>';
    echo '<th>Tanggal Daftar</th>';
    echo '</tr>';
    
    $no = 1;
    foreach ($data as $row) {
        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td>' . ($row['nomor_pendaftaran'] ?: '-') . '</td>';
        echo '<td>' . $row['nama'] . '</td>';
        echo '<td>' . $row['nisn'] . '</td>';
        echo '<td>' . ($row['tempat_lahir'] ?: '-') . '</td>';
        echo '<td>' . ($row['tanggal_lahir'] ? date('d/m/Y', strtotime($row['tanggal_lahir'])) : '-') . '</td>';
        echo '<td>' . ($row['jenis_kelamin'] === 'L' ? 'Laki-laki' : ($row['jenis_kelamin'] === 'P' ? 'Perempuan' : '-')) . '</td>';
        echo '<td>' . ($row['agama'] ?: '-') . '</td>';
        echo '<td>' . ($row['alamat'] ?: '-') . '</td>';
        echo '<td>' . ($row['telepon'] ?: '-') . '</td>';
        echo '<td>' . ($row['email'] ?: '-') . '</td>';
        echo '<td>' . ($row['asal_sekolah'] ?: '-') . '</td>';
        echo '<td>' . ($row['nama_ayah'] ?: '-') . '</td>';
        echo '<td>' . ($row['pekerjaan_ayah'] ?: '-') . '</td>';
        echo '<td>' . ($row['pendapatan_ayah'] ?: '-') . '</td>';
        echo '<td>' . ($row['nama_ibu'] ?: '-') . '</td>';
        echo '<td>' . ($row['pekerjaan_ibu'] ?: '-') . '</td>';
        echo '<td>' . ($row['pendapatan_ibu'] ?: '-') . '</td>';
        echo '<td>' . ($row['status_verifikasi'] ?: '-') . '</td>';
        echo '<td>' . ($row['status_seleksi'] ?: '-') . '</td>';
        echo '<td>' . ($row['nilai_tes'] ?: '-') . '</td>';
        echo '<td>' . ($row['tanggal_daftar'] ? date('d/m/Y H:i', strtotime($row['tanggal_daftar'])) : '-') . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    // Get the content and output it
    $content = ob_get_clean();
    echo $content;
}

function exportToPDF($data) {
    // Simple HTML to PDF conversion
    header('Content-Type: text/html; charset=utf-8');
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Data Siswa - MTs Ulul Albab</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .header { text-align: center; margin-bottom: 20px; }
            .header h1 { margin: 0; color: #333; }
            .header p { margin: 5px 0; color: #666; }
            .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #666; }
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>DATA SISWA CALON PESERTA DIDIK</h1>
            <p>MADRASAH TSANAWIYAH ULUL ALBAB</p>
            <p>TAHUN AJARAN 2024/2025</p>
            <p>Tanggal Export: ' . date('d/m/Y H:i') . '</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nomor Pendaftaran</th>
                    <th>Nama</th>
                    <th>NISN</th>
                    <th>Jenis Kelamin</th>
                    <th>Asal Sekolah</th>
                    <th>Status Verifikasi</th>
                    <th>Status Seleksi</th>
                    <th>Nilai Tes</th>
                </tr>
            </thead>
            <tbody>';
    
    $no = 1;
    foreach ($data as $row) {
        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td>' . ($row['nomor_pendaftaran'] ?: '-') . '</td>';
        echo '<td>' . htmlspecialchars($row['nama']) . '</td>';
        echo '<td>' . $row['nisn'] . '</td>';
        echo '<td>' . ($row['jenis_kelamin'] === 'L' ? 'Laki-laki' : ($row['jenis_kelamin'] === 'P' ? 'Perempuan' : '-')) . '</td>';
        echo '<td>' . htmlspecialchars($row['asal_sekolah'] ?: '-') . '</td>';
        echo '<td>' . ($row['status_verifikasi'] ?: '-') . '</td>';
        echo '<td>' . ($row['status_seleksi'] ?: '-') . '</td>';
        echo '<td>' . ($row['nilai_tes'] ?: '-') . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>
        </table>
        
        <div class="footer">
            <p>Dicetak pada: ' . date('d/m/Y H:i:s') . '</p>
            <p>Total Data: ' . count($data) . ' siswa</p>
        </div>
        
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()">Cetak PDF</button>
            <button onclick="window.close()">Tutup</button>
        </div>
    </body>
    </html>';
}
?> 