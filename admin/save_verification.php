<?php
require_once '../config/database.php';
require_once 'auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$db = Database::getInstance();

try {
    $db->beginTransaction();
    
    // Validate input
    $student_id = (int)($_POST['student_id'] ?? 0);
    $decision = $_POST['decision'] ?? '';
    $comments = trim($_POST['comments'] ?? '');
    $checklist = $_POST['checklist'] ?? [];
    
    if (!$student_id) {
        throw new Exception('ID siswa tidak valid');
    }
    
    if (!in_array($decision, ['approved', 'rejected'])) {
        throw new Exception('Keputusan verifikasi tidak valid');
    }
    
    // Get student data
    $student_query = "SELECT cs.*, p.id as pendaftaran_id, p.email 
                      FROM calon_siswa cs 
                      LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id 
                      WHERE cs.id = ?";
    $student_stmt = $db->prepare($student_query);
    $student_stmt->execute([$student_id]);
    $student = $student_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        throw new Exception('Data siswa tidak ditemukan');
    }
    
    // Get current user
    $current_user_id = $_SESSION['admin_id'] ?? 0;
    $current_user_query = "SELECT nama_lengkap FROM users WHERE id = ?";
    $current_user_stmt = $db->prepare($current_user_query);
    $current_user_stmt->execute([$current_user_id]);
    $current_user = $current_user_stmt->fetch(PDO::FETCH_ASSOC);
    $verifier_name = $current_user ? $current_user['nama_lengkap'] : 'Admin';
    
    // Prepare verification data
    $verification_status = ($decision === 'approved') ? 'verified' : 'rejected';
    $verification_notes = [];
    
    // Process checklist
    foreach ($checklist as $item => $data) {
        if ($data['checked']) {
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
    
    // Update student verification status
    $update_student_query = "UPDATE calon_siswa SET 
                            status_verifikasi = ?, 
                            updated_at = NOW() 
                            WHERE id = ?";
    $db->execute($update_student_query, [$verification_status, $student_id]);
    
    // Update pendaftaran table
    if ($student['pendaftaran_id']) {
        $update_pendaftaran_query = "UPDATE pendaftaran SET 
                                    status_verifikasi = ?, 
                                    catatan_verifikasi = ?, 
                                    verified_by = ?, 
                                    verified_at = NOW() 
                                    WHERE id = ?";
        $db->execute($update_pendaftaran_query, [
            $verification_status, 
            $final_notes, 
            $current_user_id, 
            $student['pendaftaran_id']
        ]);
    }
    
    // Log verification activity
    $activity_query = "INSERT INTO activity_log (user_id, action, ip_address, user_agent) 
                       VALUES (?, ?, ?, ?)";
    $db->execute($activity_query, [
        $current_user_id,
        'verification_' . $verification_status,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    // Send email notification
    if ($student['email']) {
        sendVerificationEmail($student, $verification_status, $final_notes, $verifier_name);
    }
    
    $db->commit();
    
    $status_text = ($verification_status === 'verified') ? 'diverifikasi' : 'ditolak';
    echo json_encode([
        'success' => true, 
        'message' => "Berkas berhasil $status_text"
    ]);
    
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

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

function sendVerificationEmail($student, $status, $notes, $verifier_name) {
    try {
        // Email configuration
        $smtp_host = config('SMTP_HOST', 'smtp.gmail.com');
        $smtp_port = config('SMTP_PORT', 587);
        $smtp_username = config('SMTP_USERNAME', 'noreply@psbonline.com');
        $smtp_password = config('SMTP_PASSWORD', '');
        $from_name = config('SMTP_FROM_NAME', 'PSB Online System');
        $from_email = config('SMTP_FROM_EMAIL', 'noreply@psbonline.com');
        
        // Email content
        $subject = "Hasil Verifikasi Berkas - " . $student['nama'];
        
        $status_text = ($status === 'verified') ? 'DITERIMA' : 'DITOLAK';
        $status_color = ($status === 'verified') ? '#10b981' : '#ef4444';
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: #667eea; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .status { background: $status_color; color: white; padding: 10px 20px; border-radius: 5px; display: inline-block; }
                .footer { background: #f8fafc; padding: 20px; text-align: center; color: #666; }
                .notes { background: #f8fafc; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>Hasil Verifikasi Berkas</h2>
                <p>Penerimaan Siswa Baru MTs Ulul Albab</p>
            </div>
            
            <div class='content'>
                <p>Kepada Yth.</p>
                <h3>{$student['nama']}</h3>
                <p>NISN: {$student['nisn']}</p>
                <p>Nomor Pendaftaran: " . ($student['nomor_pendaftaran'] ?? '-') . "</p>
                
                <p>Berdasarkan hasil verifikasi berkas yang telah dilakukan, maka:</p>
                
                <div class='status'>
                    <strong>STATUS: $status_text</strong>
                </div>
                
                <div class='notes'>
                    <h4>Detail Verifikasi:</h4>
                    <pre>" . htmlspecialchars($notes) . "</pre>
                </div>
                
                <p><strong>Diverifikasi oleh:</strong> $verifier_name</p>
                <p><strong>Tanggal:</strong> " . date('d/m/Y H:i') . "</p>
                
                <p>Jika ada pertanyaan, silakan hubungi panitia PSB MTs Ulul Albab.</p>
            </div>
            
            <div class='footer'>
                <p>MTs Ulul Albab - Sistem PSB Online</p>
                <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
            </div>
        </body>
        </html>";
        
        // Email headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: ' . $from_email,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Send email
        if (mail($student['email'], $subject, $message, implode("\r\n", $headers))) {
            // Log email sent
            error_log("Verification email sent to: " . $student['email']);
        } else {
            error_log("Failed to send verification email to: " . $student['email']);
        }
        
    } catch (Exception $e) {
        error_log("Email sending error: " . $e->getMessage());
    }
}
?> 