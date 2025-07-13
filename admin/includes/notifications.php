<?php
// admin/includes/notifications.php
// Sistem notifikasi email & SMS dengan queue dan logging

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

class EmailNotification {
    private $mail;
    public function __construct() {
        $this->mail = new PHPMailer(true);
        // Konfigurasi SMTP
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com'; // Ganti sesuai hosting/email
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'your_email@gmail.com'; // Ganti
        $this->mail->Password = 'your_password'; // Ganti
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        $this->mail->setFrom('your_email@gmail.com', 'Admin PSB');
        $this->mail->isHTML(true);
    }
    public function send($to, $subject, $body, $attachments = []) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            foreach ($attachments as $file) {
                $this->mail->addAttachment($file);
            }
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Email gagal: ' . $e->getMessage());
            return false;
        }
    }
}

class SMSNotification {
    // Contoh Nexmo/Twilio, ganti dengan API asli
    public static function send($to, $message) {
        // Implementasi SMS gateway di sini
        // Contoh: Nexmo/Twilio API call
        // Simulasi sukses
        return true;
    }
}

function queue_notification($type, $recipient, $subject, $body, $via = 'email', $attachments = null) {
    global $db;
    $stmt = $db->prepare("INSERT INTO queue_notifications (type, recipient, subject, body, via, attachments, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->execute([$type, $recipient, $subject, $body, $via, $attachments ? json_encode($attachments) : null]);
}

function process_notification_queue($limit = 10) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM queue_notifications WHERE status = 'pending' ORDER BY id ASC LIMIT ?");
    $stmt->execute([$limit]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $emailNotif = new EmailNotification();
    foreach ($rows as $row) {
        $success = false;
        if ($row['via'] === 'email') {
            $success = $emailNotif->send($row['recipient'], $row['subject'], $row['body'], json_decode($row['attachments'], true) ?? []);
        } elseif ($row['via'] === 'sms') {
            $success = SMSNotification::send($row['recipient'], $row['body']);
        }
        $status = $success ? 'sent' : 'failed';
        $db->prepare("UPDATE queue_notifications SET status = ?, sent_at = NOW() WHERE id = ?")->execute([$status, $row['id']]);
    }
}

// Email templates
function template_konfirmasi_pendaftaran($nama, $nomor) {
    return '<h2>Konfirmasi Pendaftaran</h2><p>Halo ' . htmlspecialchars($nama) . ',<br>Pendaftaran Anda dengan nomor <b>' . htmlspecialchars($nomor) . '</b> telah diterima.</p>';
}
function template_reminder_berkas($nama) {
    return '<h2>Reminder Melengkapi Berkas</h2><p>Halo ' . htmlspecialchars($nama) . ',<br>Mohon segera lengkapi berkas pendaftaran Anda melalui portal PSB.</p>';
}
function template_pengumuman_hasil($nama, $status) {
    return '<h2>Pengumuman Hasil Seleksi</h2><p>Halo ' . htmlspecialchars($nama) . ',<br>Status seleksi Anda: <b>' . htmlspecialchars($status) . '</b>.</p>';
}
function template_panduan_daftar_ulang($nama) {
    return '<h2>Panduan Daftar Ulang</h2><p>Halo ' . htmlspecialchars($nama) . ',<br>Silakan ikuti panduan daftar ulang pada portal PSB.</p>';
}
