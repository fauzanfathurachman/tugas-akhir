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
    $student_ids = $_POST['student_ids'] ?? [];
    
    if (empty($student_ids) || !is_array($student_ids)) {
        throw new Exception('ID siswa tidak valid');
    }
    
    // Validate student IDs
    $student_ids = array_map('intval', $student_ids);
    $student_ids = array_filter($student_ids);
    
    if (empty($student_ids)) {
        throw new Exception('Tidak ada ID siswa yang valid');
    }
    
    $db->beginTransaction();
    
    // Get current user
    $current_user_id = $_SESSION['admin_id'] ?? 0;
    $current_user_query = "SELECT nama_lengkap FROM users WHERE id = ?";
    $current_user_stmt = $db->prepare($current_user_query);
    $current_user_stmt->execute([$current_user_id]);
    $current_user = $current_user_stmt->fetch(PDO::FETCH_ASSOC);
    $verifier_name = $current_user ? $current_user['nama_lengkap'] : 'Admin';
    
    // Get students data
    $placeholders = str_repeat('?,', count($student_ids) - 1) . '?';
    $students_query = "SELECT cs.*, p.id as pendaftaran_id, p.email 
                       FROM calon_siswa cs 
                       LEFT JOIN pendaftaran p ON cs.id = p.calon_siswa_id 
                       WHERE cs.id IN ($placeholders) AND cs.status_verifikasi = 'pending'";
    $students_stmt = $db->prepare($students_query);
    $students_stmt->execute($student_ids);
    $students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($students)) {
        throw new Exception('Tidak ada siswa yang menunggu verifikasi');
    }
    
    $verified_count = 0;
    $emails_sent = 0;
    
    foreach ($students as $student) {
        // Update student verification status
        $update_student_query = "UPDATE calon_siswa SET 
                                status_verifikasi = 'verified', 
                                updated_at = NOW() 
                                WHERE id = ?";
        $db->execute($update_student_query, [$student['id']]);
        
        // Update pendaftaran table
        if ($student['pendaftaran_id']) {
            $verification_notes = "✓ Verifikasi massal oleh $verifier_name\n✓ Semua dokumen lengkap dan valid\n✓ Tanggal: " . date('d/m/Y H:i');
            
            $update_pendaftaran_query = "UPDATE pendaftaran SET 
                                        status_verifikasi = 'verified', 
                                        catatan_verifikasi = ?, 
                                        verified_by = ?, 
                                        verified_at = NOW() 
                                        WHERE id = ?";
            $db->execute($update_pendaftaran_query, [
                $verification_notes,
                $current_user_id,
                $student['pendaftaran_id']
            ]);
        }
        
        // Send email notification
        if ($student['email']) {
            if (sendBulkVerificationEmail($student, $verifier_name)) {
                $emails_sent++;
            }
        }
        
        $verified_count++;
    }
    
    // Log bulk verification activity
    $activity_query = "INSERT INTO activity_log (user_id, action, ip_address, user_agent) 
                       VALUES (?, ?, ?, ?)";
    $db->execute($activity_query, [
        $current_user_id,
        'bulk_verification_verified',
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Berhasil memverifikasi $verified_count siswa" . ($emails_sent > 0 ? " dan mengirim $emails_sent email notifikasi" : "")
    ]);
    
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function sendBulkVerificationEmail($student, $verifier_name) {
    try {
        // Email configuration
        $from_name = config('SMTP_FROM_NAME', 'PSB Online System');
        $from_email = config('SMTP_FROM_EMAIL', 'noreply@psbonline.com');
        
        // Email content
        $subject = "Verifikasi Berkas Diterima - " . $student['nama'];
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: #667eea; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .status { background: #10b981; color: white; padding: 10px 20px; border-radius: 5px; display: inline-block; }
                .footer { background: #f8fafc; padding: 20px; text-align: center; color: #666; }
                .notes { background: #f8fafc; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>Verifikasi Berkas Diterima</h2>
                <p>Penerimaan Siswa Baru MTs Ulul Albab</p>
            </div>
            
            <div class='content'>
                <p>Kepada Yth.</p>
                <h3>{$student['nama']}</h3>
                <p>NISN: {$student['nisn']}</p>
                <p>Nomor Pendaftaran: " . ($student['nomor_pendaftaran'] ?? '-') . "</p>
                
                <p>Berdasarkan hasil verifikasi berkas yang telah dilakukan, maka:</p>
                
                <div class='status'>
                    <strong>STATUS: DITERIMA</strong>
                </div>
                
                <div class='notes'>
                    <h4>Detail Verifikasi:</h4>
                    <ul>
                        <li>✓ Verifikasi massal oleh $verifier_name</li>
                        <li>✓ Semua dokumen lengkap dan valid</li>
                        <li>✓ Tanggal: " . date('d/m/Y H:i') . "</li>
                    </ul>
                </div>
                
                <p>Selamat! Berkas Anda telah diverifikasi dan diterima. Silakan menunggu pengumuman selanjutnya.</p>
                
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
            error_log("Bulk verification email sent to: " . $student['email']);
            return true;
        } else {
            error_log("Failed to send bulk verification email to: " . $student['email']);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Bulk email sending error: " . $e->getMessage());
        return false;
    }
}
?> 