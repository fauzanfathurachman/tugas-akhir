<?php
require_once '../config/database.php';
// Tidak perlu cek login, langsung izinkan akses (untuk demo/tugas kecil)

if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
    die('ID siswa tidak valid');
}

$student_id = (int)$_GET['student_id'];
$download_type = $_GET['type'] ?? 'individual'; // individual, zip
$document_type = $_GET['document'] ?? '';

$db = Database::getInstance();

try {
    // Get student data
    $query = "SELECT cs.*, p.nomor_pendaftaran 
              FROM calon_siswa cs 
              LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id 
              WHERE cs.id = ?";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        die('Data siswa tidak ditemukan');
    }
    
    // Define documents
    $documents = [
        'foto' => [
            'title' => 'Foto_3x4',
            'file' => $student['foto'],
            'path' => '../uploads/foto/'
        ],
        'kk' => [
            'title' => 'Kartu_Keluarga',
            'file' => $student['kartu_keluarga'],
            'path' => '../uploads/kk/'
        ],
        'akta' => [
            'title' => 'Akta_Kelahiran',
            'file' => $student['akta'],
            'path' => '../uploads/akta/'
        ],
        'ijazah' => [
            'title' => 'Ijazah_SKL',
            'file' => $student['ijazah'],
            'path' => '../uploads/ijazah/'
        ]
    ];
    
    if ($download_type === 'individual' && !empty($document_type)) {
        // Download individual document
        if (!isset($documents[$document_type])) {
            die('Jenis dokumen tidak valid');
        }
        
        $doc = $documents[$document_type];
        $file_path = $doc['path'] . $doc['file'];
        
        if (empty($doc['file']) || !file_exists($file_path)) {
            die('File tidak ditemukan');
        }
        
        $file_extension = pathinfo($doc['file'], PATHINFO_EXTENSION);
        $filename = $doc['title'] . '_' . $student['nama'] . '.' . $file_extension;
        
        // Set headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        // Output file
        readfile($file_path);
        exit;
        
    } elseif ($download_type === 'zip') {
        // Create ZIP archive
        $zip_filename = 'Dokumen_' . $student['nama'] . '_' . date('Y-m-d_H-i-s') . '.zip';
        $zip_path = '../uploads/temp/' . $zip_filename;
        
        // Create temp directory if not exists
        if (!is_dir('../uploads/temp/')) {
            mkdir('../uploads/temp/', 0755, true);
        }
        
        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
            die('Tidak dapat membuat file ZIP');
        }
        
        $added_files = 0;
        
        foreach ($documents as $type => $doc) {
            if (!empty($doc['file'])) {
                $file_path = $doc['path'] . $doc['file'];
                
                if (file_exists($file_path)) {
                    $file_extension = pathinfo($doc['file'], PATHINFO_EXTENSION);
                    $filename = $doc['title'] . '_' . $student['nama'] . '.' . $file_extension;
                    
                    $zip->addFile($file_path, $filename);
                    $added_files++;
                }
            }
        }
        
        $zip->close();
        
        if ($added_files === 0) {
            unlink($zip_path);
            die('Tidak ada dokumen yang tersedia untuk didownload');
        }
        
        // Set headers for ZIP download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
        header('Content-Length: ' . filesize($zip_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        // Output ZIP file
        readfile($zip_path);
        
        // Clean up temp file
        unlink($zip_path);
        exit;
        
    } else {
        die('Tipe download tidak valid');
    }
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?> 