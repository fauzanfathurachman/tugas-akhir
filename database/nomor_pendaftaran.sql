CREATE TABLE IF NOT EXISTS nomor_pendaftaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tahun VARCHAR(10) NOT NULL,
    nomor_urut INT NOT NULL,
    nomor_lengkap VARCHAR(20) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS nomor_pendaftaran_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor VARCHAR(20),
    status VARCHAR(20),
    keterangan TEXT,
    log_time DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
