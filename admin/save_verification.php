
<?php
// DEMO MODE: Tidak ada akses database, hanya validasi input dan response dummy
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Ambil data dari POST
$student_id = (int)($_POST['student_id'] ?? 0);
$decision = $_POST['decision'] ?? '';
$comments = trim($_POST['comments'] ?? '');
$checklist = $_POST['checklist'] ?? [];

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'ID siswa tidak valid (dummy)']);
    exit;
}
if (!in_array($decision, ['approved', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Keputusan verifikasi tidak valid (dummy)']);
    exit;
}

// Data siswa dummy
$student = [
    'nama' => 'Fulan Bin Fulan',
    'nisn' => '1234567890',
    'nomor_pendaftaran' => 'PSB2025-001',
    'email' => 'fulan@example.com'
];
$verifier_name = 'Admin Demo';

// Proses checklist dummy
$verification_notes = [];
foreach ($checklist as $item => $data) {
    if (isset($data['checked']) && $data['checked']) {
        $verification_notes[] = "✓ " . getChecklistItemName($item);
    } else {
        $verification_notes[] = "✗ " . getChecklistItemName($item);
    }
    if (!empty($data['notes'])) {
        $verification_notes[] = "  - " . $data['notes'];
    }
}
if (!empty($comments)) {
    $verification_notes[] = "Catatan: " . $comments;
}
$final_notes = implode("\n", $verification_notes);

$verification_status = ($decision === 'approved') ? 'verified' : 'rejected';
$status_text = ($verification_status === 'verified') ? 'diverifikasi' : 'ditolak';

// Simulasi kirim email (tidak benar-benar mengirim)
// sendVerificationEmail($student, $verification_status, $final_notes, $verifier_name);

echo json_encode([
    'success' => true,
    'message' => "Berkas berhasil $status_text (dummy)",
    'dummy' => true,
    'student' => $student,
    'notes' => $final_notes,
    'verifier' => $verifier_name
]);

function getChecklistItemName($item) {
    $names = [
        'check-foto' => 'Foto (3x4)',
        'check-kk' => 'Kartu Keluarga',
        'check-akta' => 'Akta Kelahiran',
        'check-ijazah' => 'Ijazah/SKL',
        'check-skhun' => 'SKHUN',
        'check-nisn' => 'NISN Valid'
    ];
    return $names[$item] ?? $item;
}