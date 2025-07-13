<?php
// admin/includes/registration_number.php
// Generate nomor pendaftaran otomatis aman dan unik

require_once __DIR__ . '/../../config/database.php';

function generateRegistrationNumber($tahun = null) {
    global $db;
    if (!$tahun) {
        $tahun = date('Y');
    }
    
    // Lock table to prevent race condition
    $db->beginTransaction();
    try {
        // Ambil nomor urut terakhir untuk tahun berjalan
        $stmt = $db->prepare("SELECT nomor_urut FROM nomor_pendaftaran WHERE tahun = ? ORDER BY nomor_urut DESC LIMIT 1 FOR UPDATE");
        $stmt->execute([$tahun]);
        $last = $stmt->fetch(PDO::FETCH_ASSOC);
        $next = $last ? $last['nomor_urut'] + 1 : 1;
        $nomor_lengkap = sprintf('PSB-%s-%04d', $tahun, $next);
        // Cek duplikat
        $cek = $db->prepare("SELECT COUNT(*) FROM nomor_pendaftaran WHERE nomor_lengkap = ?");
        $cek->execute([$nomor_lengkap]);
        if ($cek->fetchColumn() > 0) {
            $db->rollBack();
            throw new Exception('Nomor pendaftaran sudah ada, silakan coba lagi.');
        }
        // Simpan ke tabel tracking
        $ins = $db->prepare("INSERT INTO nomor_pendaftaran (tahun, nomor_urut, nomor_lengkap, created_at) VALUES (?, ?, ?, NOW())");
        $ins->execute([$tahun, $next, $nomor_lengkap]);
        $db->commit();
        // Logging
        logRegistrationNumber($nomor_lengkap, 'generated');
        return $nomor_lengkap;
    } catch (Exception $e) {
        $db->rollBack();
        logRegistrationNumber(null, 'failed', $e->getMessage());
        throw $e;
    }
}

function checkRegistrationNumberExists($nomor) {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) FROM nomor_pendaftaran WHERE nomor_lengkap = ?");
    $stmt->execute([$nomor]);
    return $stmt->fetchColumn() > 0;
}

function logRegistrationNumber($nomor, $status, $keterangan = null) {
    global $db;
    $stmt = $db->prepare("INSERT INTO nomor_pendaftaran_log (nomor, status, keterangan, log_time) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$nomor, $status, $keterangan]);
}

// Untuk backup/restore counter, bisa dump tabel nomor_pendaftaran
// Monitoring: SELECT * FROM nomor_pendaftaran ORDER BY tahun DESC, nomor_urut DESC
