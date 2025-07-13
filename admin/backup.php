<?php
// admin/backup.php
// Sistem backup & restore database dengan scheduling dan keamanan

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$backupDir = __DIR__ . '/../backup/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

function listBackupFiles($dir) {
    $files = glob($dir . '*.sql.gz.enc');
    $result = [];
    foreach ($files as $file) {
        $result[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => date('Y-m-d H:i:s', filemtime($file))
        ];
    }
    usort($result, function($a, $b) { return strcmp($b['name'], $a['name']); });
    return $result;
}

function cleanupOldBackups($dir, $days = 30) {
    foreach (glob($dir . '*.sql.gz.enc') as $file) {
        if (filemtime($file) < strtotime("-$days days")) {
            unlink($file);
        }
    }
}

// Cleanup otomatis backup lama
cleanupOldBackups($backupDir);

// Handle manual backup trigger
if (isset($_POST['backup_now'])) {
    $dbHost = 'localhost';
    $dbUser = 'root'; // Ganti sesuai config
    $dbPass = '';
    $dbName = 'psb_online';
    $date = date('Ymd_His');
    $filename = $backupDir . "backup_{$dbName}_{$date}.sql";
    $gzfile = $filename . '.gz';
    $encfile = $gzfile . '.enc';
    $enckey = 'ganti_password_enkripsi'; // Ganti, simpan aman
    
    // Jalankan mysqldump
    $cmd = "mysqldump -h $dbHost -u $dbUser " . ($dbPass ? "-p$dbPass " : "") . "$dbName > \"$filename\"";
    exec($cmd, $output, $result);
    if ($result === 0 && file_exists($filename)) {
        // Kompresi gzip
        exec("gzip -f \"$filename\"");
        // Enkripsi file
        exec("openssl enc -aes-256-cbc -salt -in \"$gzfile\" -out \"$encfile\" -k $enckey");
        unlink($gzfile);
        $msg = 'Backup berhasil: ' . basename($encfile);
        // Logging
        file_put_contents($backupDir . 'backup.log', date('c') . " SUCCESS $encfile\n", FILE_APPEND);
    } else {
        $msg = 'Backup gagal!';
        file_put_contents($backupDir . 'backup.log', date('c') . " FAILED $filename\n", FILE_APPEND);
    }
    echo "<script>window.location='backup.php?msg=" . urlencode($msg) . "';</script>";
    exit;
}

// Handle restore
if (isset($_POST['restore_file'])) {
    $restoreFile = $backupDir . basename($_POST['restore_file']);
    $enckey = 'ganti_password_enkripsi'; // Ganti sesuai backup
    $tmpGz = $restoreFile . '.tmp.gz';
    $tmpSql = $restoreFile . '.tmp.sql';
    // Dekripsi
    exec("openssl enc -d -aes-256-cbc -in \"$restoreFile\" -out \"$tmpGz\" -k $enckey");
    // Unzip
    exec("gzip -d -f \"$tmpGz\"");
    // Restore ke database
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'psb_online';
    $cmd = "mysql -h $dbHost -u $dbUser " . ($dbPass ? "-p$dbPass " : "") . "$dbName < \"$tmpSql\"";
    exec($cmd, $output, $result);
    unlink($tmpSql);
    $msg = $result === 0 ? 'Restore berhasil!' : 'Restore gagal!';
    file_put_contents($backupDir . 'backup.log', date('c') . " RESTORE $restoreFile $msg\n", FILE_APPEND);
    echo "<script>window.location='backup.php?msg=" . urlencode($msg) . "';</script>";
    exit;
}

// List backup files
$backups = listBackupFiles($backupDir);

?>
<div class="container mt-4">
    <h2>Backup & Restore Database</h2>
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>
    <form method="post" id="backupForm">
        <button type="submit" name="backup_now" class="btn btn-primary">Backup Sekarang</button>
        <span id="progress" style="margin-left:20px;"></span>
    </form>
    <hr>
    <h5>Daftar Backup</h5>
    <table class="table table-bordered">
        <thead><tr><th>File</th><th>Tanggal</th><th>Ukuran</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($backups as $b): ?>
            <tr>
                <td><?php echo htmlspecialchars($b['name']); ?></td>
                <td><?php echo $b['date']; ?></td>
                <td><?php echo number_format($b['size']/1024,1); ?> KB</td>
                <td>
                    <a href="../backup/<?php echo urlencode($b['name']); ?>" class="btn btn-success btn-sm" download>Download</a>
                    <form method="post" style="display:inline" onsubmit="return confirm('Restore database dari backup ini?')">
                        <input type="hidden" name="restore_file" value="<?php echo htmlspecialchars($b['name']); ?>">
                        <button type="submit" class="btn btn-warning btn-sm">Restore</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <hr>
    <h6>Log Backup</h6>
    <pre style="background:#f8f9fa;max-height:200px;overflow:auto"><?php echo @file_get_contents($backupDir.'backup.log'); ?></pre>
</div>
<script>
document.getElementById('backupForm').onsubmit = function(e) {
    document.getElementById('progress').innerHTML = 'Backup sedang diproses...';
};
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
