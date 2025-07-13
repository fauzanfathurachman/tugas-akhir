<?php
// admin/includes/qr_generator.php
// QR code generation for kartu pendaftaran, cek status, verifikasi kehadiran
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../../vendor/phpqrcode/qrlib.php';

function generateQR($data, $filename, $size = 6, $margin = 2) {
    $dir = __DIR__ . '/../../uploads/qr/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $file = $dir . $filename;
    QRcode::png($data, $file, QR_ECLEVEL_M, $size, $margin);
    return $file;
}

function qrForKartuPendaftaran($nomor_daftar) {
    $url = 'https://yourdomain.com/cek-status.php?no=' . urlencode($nomor_daftar);
    return generateQR($url, $nomor_daftar . '_kartu.png');
}

function qrForVerifikasiKehadiran($nomor_daftar) {
    $url = 'https://yourdomain.com/verifikasi-kehadiran.php?no=' . urlencode($nomor_daftar);
    return generateQR($url, $nomor_daftar . '_hadir.png');
}

function bulkGenerateQR($list_nomor) {
    $files = [];
    foreach ($list_nomor as $no) {
        $files[] = qrForKartuPendaftaran($no);
    }
    // ZIP all QR
    $zipfile = __DIR__ . '/../../uploads/qr/qr_bulk_' . date('Ymd_His') . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipfile, ZipArchive::CREATE) === TRUE) {
        foreach ($files as $f) $zip->addFile($f, basename($f));
        $zip->close();
    }
    return $zipfile;
}

function logQRScan($nomor, $user) {
    $log = date('c') . ", $nomor, $user, " . $_SERVER['REMOTE_ADDR'] . "\n";
    file_put_contents(__DIR__ . '/../../logs/qr_scan.log', $log, FILE_APPEND);
}
