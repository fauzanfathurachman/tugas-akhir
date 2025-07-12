<?php
/**
 * Email Helper Class
 * 
 * Handles email sending with SMTP support and HTML templates
 */
class EmailHelper {
    
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $smtp_encryption;
    private $from_name;
    private $from_email;
    
    public function __construct() {
        $this->smtp_host = config('SMTP_HOST', 'smtp.gmail.com');
        $this->smtp_port = config('SMTP_PORT', 587);
        $this->smtp_username = config('SMTP_USERNAME', 'noreply@psbonline.com');
        $this->smtp_password = config('SMTP_PASSWORD', '');
        $this->smtp_encryption = config('SMTP_ENCRYPTION', 'tls');
        $this->from_name = config('SMTP_FROM_NAME', 'PSB Online System');
        $this->from_email = config('SMTP_FROM_EMAIL', 'noreply@psbonline.com');
    }
    
    /**
     * Send verification email
     */
    public function sendVerificationEmail($student, $status, $notes, $verifier_name) {
        $subject = "Hasil Verifikasi Berkas - " . $student['nama'];
        
        $status_text = ($status === 'verified') ? 'DITERIMA' : 'DITOLAK';
        $status_color = ($status === 'verified') ? '#10b981' : '#ef4444';
        
        $message = $this->getVerificationEmailTemplate($student, $status_text, $status_color, $notes, $verifier_name);
        
        return $this->sendEmail($student['email'], $subject, $message);
    }
    
    /**
     * Send bulk verification email
     */
    public function sendBulkVerificationEmail($student, $verifier_name) {
        $subject = "Verifikasi Berkas Diterima - " . $student['nama'];
        
        $message = $this->getBulkVerificationEmailTemplate($student, $verifier_name);
        
        return $this->sendEmail($student['email'], $subject, $message);
    }
    
    /**
     * Send email using PHP mail() function
     */
    private function sendEmail($to, $subject, $message) {
        try {
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . $this->from_name . ' <' . $this->from_email . '>',
                'Reply-To: ' . $this->from_email,
                'X-Mailer: PSB Online System'
            ];
            
            if (mail($to, $subject, $message, implode("\r\n", $headers))) {
                error_log("Email sent successfully to: $to");
                return true;
            } else {
                error_log("Failed to send email to: $to");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get verification email template
     */
    private function getVerificationEmailTemplate($student, $status_text, $status_color, $notes, $verifier_name) {
        return "
        <html>
        <head>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0; 
                    padding: 0; 
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: #fff;
                }
                .header { 
                    background: #667eea; 
                    color: white; 
                    padding: 30px 20px; 
                    text-align: center; 
                }
                .header h2 {
                    margin: 0 0 10px 0;
                    font-size: 24px;
                }
                .header p {
                    margin: 0;
                    opacity: 0.9;
                }
                .content { 
                    padding: 30px 20px; 
                }
                .student-info {
                    background: #f8fafc;
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                }
                .student-info h3 {
                    margin: 0 0 10px 0;
                    color: #1f2937;
                }
                .info-row {
                    margin: 5px 0;
                    font-size: 14px;
                }
                .status { 
                    background: $status_color; 
                    color: white; 
                    padding: 15px 25px; 
                    border-radius: 8px; 
                    display: inline-block; 
                    font-weight: bold;
                    font-size: 18px;
                    margin: 20px 0;
                }
                .footer { 
                    background: #f8fafc; 
                    padding: 20px; 
                    text-align: center; 
                    color: #666;
                    font-size: 12px;
                }
                .notes { 
                    background: #f8fafc; 
                    padding: 20px; 
                    border-radius: 8px; 
                    margin: 20px 0;
                    border-left: 4px solid #667eea;
                }
                .notes h4 {
                    margin: 0 0 15px 0;
                    color: #1f2937;
                }
                .notes pre {
                    margin: 0;
                    white-space: pre-wrap;
                    font-family: inherit;
                    font-size: 14px;
                    line-height: 1.5;
                }
                .verification-details {
                    background: #f0f9ff;
                    padding: 15px;
                    border-radius: 8px;
                    margin: 20px 0;
                    border-left: 4px solid #3b82f6;
                }
                .verification-details p {
                    margin: 5px 0;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Hasil Verifikasi Berkas</h2>
                    <p>Penerimaan Siswa Baru MTs Ulul Albab</p>
                </div>
                
                <div class='content'>
                    <div class='student-info'>
                        <h3>Data Pendaftar</h3>
                        <div class='info-row'><strong>Nama:</strong> {$student['nama']}</div>
                        <div class='info-row'><strong>NISN:</strong> {$student['nisn']}</div>
                        <div class='info-row'><strong>Nomor Pendaftaran:</strong> " . ($student['nomor_pendaftaran'] ?? '-') . "</div>
                        <div class='info-row'><strong>Asal Sekolah:</strong> {$student['asal_sekolah']}</div>
                    </div>
                    
                    <p>Berdasarkan hasil verifikasi berkas yang telah dilakukan, maka:</p>
                    
                    <div class='status'>
                        <strong>STATUS: $status_text</strong>
                    </div>
                    
                    <div class='notes'>
                        <h4>Detail Verifikasi:</h4>
                        <pre>" . htmlspecialchars($notes) . "</pre>
                    </div>
                    
                    <div class='verification-details'>
                        <p><strong>Diverifikasi oleh:</strong> $verifier_name</p>
                        <p><strong>Tanggal Verifikasi:</strong> " . date('d/m/Y H:i') . "</p>
                        <p><strong>Waktu Verifikasi:</strong> " . date('H:i:s') . " WIB</p>
                    </div>
                    
                    <p style='margin-top: 30px;'>
                        <strong>Catatan:</strong><br>
                        " . ($status_text === 'DITERIMA' ? 
                            'Selamat! Berkas Anda telah diverifikasi dan diterima. Silakan menunggu pengumuman selanjutnya.' : 
                            'Mohon perbaiki berkas yang kurang sesuai dengan ketentuan yang berlaku.') . "
                    </p>
                    
                    <p style='margin-top: 20px;'>
                        Jika ada pertanyaan, silakan hubungi panitia PSB MTs Ulul Albab melalui:<br>
                        <strong>Email:</strong> psb@mtululalbab.sch.id<br>
                        <strong>Telepon:</strong> (021) 1234567
                    </p>
                </div>
                
                <div class='footer'>
                    <p><strong>MTs Ulul Albab - Sistem PSB Online</strong></p>
                    <p>Jl. Pendidikan No. 123, Jakarta Selatan</p>
                    <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
                    <p style='margin-top: 15px; font-size: 11px; color: #999;'>
                        © " . date('Y') . " MTs Ulul Albab. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Get bulk verification email template
     */
    private function getBulkVerificationEmailTemplate($student, $verifier_name) {
        return "
        <html>
        <head>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0; 
                    padding: 0; 
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: #fff;
                }
                .header { 
                    background: #667eea; 
                    color: white; 
                    padding: 30px 20px; 
                    text-align: center; 
                }
                .header h2 {
                    margin: 0 0 10px 0;
                    font-size: 24px;
                }
                .header p {
                    margin: 0;
                    opacity: 0.9;
                }
                .content { 
                    padding: 30px 20px; 
                }
                .student-info {
                    background: #f8fafc;
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                }
                .student-info h3 {
                    margin: 0 0 10px 0;
                    color: #1f2937;
                }
                .info-row {
                    margin: 5px 0;
                    font-size: 14px;
                }
                .status { 
                    background: #10b981; 
                    color: white; 
                    padding: 15px 25px; 
                    border-radius: 8px; 
                    display: inline-block; 
                    font-weight: bold;
                    font-size: 18px;
                    margin: 20px 0;
                }
                .footer { 
                    background: #f8fafc; 
                    padding: 20px; 
                    text-align: center; 
                    color: #666;
                    font-size: 12px;
                }
                .notes { 
                    background: #f0f9ff; 
                    padding: 20px; 
                    border-radius: 8px; 
                    margin: 20px 0;
                    border-left: 4px solid #3b82f6;
                }
                .notes h4 {
                    margin: 0 0 15px 0;
                    color: #1f2937;
                }
                .notes ul {
                    margin: 0;
                    padding-left: 20px;
                }
                .notes li {
                    margin: 5px 0;
                    font-size: 14px;
                }
                .verification-details {
                    background: #f0f9ff;
                    padding: 15px;
                    border-radius: 8px;
                    margin: 20px 0;
                    border-left: 4px solid #3b82f6;
                }
                .verification-details p {
                    margin: 5px 0;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Verifikasi Berkas Diterima</h2>
                    <p>Penerimaan Siswa Baru MTs Ulul Albab</p>
                </div>
                
                <div class='content'>
                    <div class='student-info'>
                        <h3>Data Pendaftar</h3>
                        <div class='info-row'><strong>Nama:</strong> {$student['nama']}</div>
                        <div class='info-row'><strong>NISN:</strong> {$student['nisn']}</div>
                        <div class='info-row'><strong>Nomor Pendaftaran:</strong> " . ($student['nomor_pendaftaran'] ?? '-') . "</div>
                        <div class='info-row'><strong>Asal Sekolah:</strong> {$student['asal_sekolah']}</div>
                    </div>
                    
                    <p>Berdasarkan hasil verifikasi berkas yang telah dilakukan, maka:</p>
                    
                    <div class='status'>
                        <strong>STATUS: DITERIMA</strong>
                    </div>
                    
                    <div class='notes'>
                        <h4>Detail Verifikasi Massal:</h4>
                        <ul>
                            <li>✓ Verifikasi massal oleh $verifier_name</li>
                            <li>✓ Semua dokumen lengkap dan valid</li>
                            <li>✓ Foto (3x4) - Tersedia dan valid</li>
                            <li>✓ Kartu Keluarga - Tersedia dan valid</li>
                            <li>✓ Akta Kelahiran - Tersedia dan valid</li>
                            <li>✓ Ijazah/SKL - Tersedia dan valid</li>
                            <li>✓ NISN - Valid dan terverifikasi</li>
                        </ul>
                    </div>
                    
                    <div class='verification-details'>
                        <p><strong>Diverifikasi oleh:</strong> $verifier_name</p>
                        <p><strong>Tanggal Verifikasi:</strong> " . date('d/m/Y H:i') . "</p>
                        <p><strong>Waktu Verifikasi:</strong> " . date('H:i:s') . " WIB</p>
                        <p><strong>Metode:</strong> Verifikasi Massal</p>
                    </div>
                    
                    <p style='margin-top: 30px;'>
                        <strong>Selamat! Berkas Anda telah diverifikasi dan diterima.</strong><br>
                        Silakan menunggu pengumuman selanjutnya untuk tahap seleksi.
                    </p>
                    
                    <p style='margin-top: 20px;'>
                        Jika ada pertanyaan, silakan hubungi panitia PSB MTs Ulul Albab melalui:<br>
                        <strong>Email:</strong> psb@mtululalbab.sch.id<br>
                        <strong>Telepon:</strong> (021) 1234567
                    </p>
                </div>
                
                <div class='footer'>
                    <p><strong>MTs Ulul Albab - Sistem PSB Online</strong></p>
                    <p>Jl. Pendidikan No. 123, Jakarta Selatan</p>
                    <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
                    <p style='margin-top: 15px; font-size: 11px; color: #999;'>
                        © " . date('Y') . " MTs Ulul Albab. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }
}
?> 