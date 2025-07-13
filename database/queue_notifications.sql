CREATE TABLE IF NOT EXISTS queue_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    body TEXT NOT NULL,
    via ENUM('email','sms') NOT NULL DEFAULT 'email',
    attachments TEXT,
    status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    sent_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
