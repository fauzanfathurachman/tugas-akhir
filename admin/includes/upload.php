<?php
// admin/includes/upload.php
// Sistem upload file aman dengan validasi, hash, scan, watermark, versioning

require_once __DIR__ . '/../../config/database.php';

$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'application/pdf' => 'pdf'
];
$maxSize = 2 * 1024 * 1024; // 2MB

function getMagicNumber($file) {
    $f = fopen($file, 'rb');
    $bytes = fread($f, 8);
    fclose($f);
    return bin2hex($bytes);
}

function isMalware($file) {
    $content = file_get_contents($file, false, null, 0, 2048);
    $patterns = ['<script', 'eval(', 'base64_decode', '<?php', 'system(', 'shell_exec'];
    foreach ($patterns as $p) {
        if (stripos($content, $p) !== false) return true;
    }
    return false;
}

function resizeImage($src, $dest, $maxW = 600, $maxH = 600) {
    $info = getimagesize($src);
    if (!$info) return false;
    list($w, $h) = $info;
    $ratio = min($maxW/$w, $maxH/$h, 1);
    $nw = (int)($w * $ratio); $nh = (int)($h * $ratio);
    $img = imagecreatetruecolor($nw, $nh);
    if ($info[2] == IMAGETYPE_JPEG) {
        $srcImg = imagecreatefromjpeg($src);
    } else {
        $srcImg = imagecreatefrompng($src);
    }
    imagecopyresampled($img, $srcImg, 0,0,0,0, $nw,$nh, $w,$h);
    imagejpeg($img, $dest, 90);
    imagedestroy($img); imagedestroy($srcImg);
    return true;
}

function addWatermark($file, $text = 'PSB') {
    $img = imagecreatefromjpeg($file);
    $color = imagecolorallocatealpha($img, 255,255,255, 80);
    $font = __DIR__ . '/arial.ttf';
    imagettftext($img, 18, 0, 10, 30, $color, $font, $text);
    imagejpeg($img, $file, 90);
    imagedestroy($img);
}

function saveUpload($file, $nomor_daftar) {
    global $allowedTypes, $maxSize;
    if ($file['error'] !== UPLOAD_ERR_OK) return 'Upload error';
    if ($file['size'] > $maxSize) return 'File terlalu besar';
    if (!isset($allowedTypes[$file['type']])) return 'Tipe file tidak diizinkan';
    // Magic number check
    $magic = getMagicNumber($file['tmp_name']);
    $validMagic = [
        'jpg' => ['ffd8ffe0','ffd8ffe1','ffd8ffe2'],
        'png' => ['89504e47'],
        'pdf' => ['25504446']
    ];
    $ext = $allowedTypes[$file['type']];
    $ok = false;
    foreach ($validMagic[$ext] as $m) {
        if (stripos($magic, $m) === 0) $ok = true;
    }
    if (!$ok) return 'File header tidak valid';
    if (isMalware($file['tmp_name'])) return 'File terdeteksi malware';
    $hash = sha1_file($file['tmp_name']) . '_' . time();
    $dir = __DIR__ . "/../../uploads/siswa/$nomor_daftar/";
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fname = $hash . '.' . $ext;
    $target = $dir . $fname;
    // Versioning: jika file sama, tambahkan _vN
    $i = 1;
    while (file_exists($target)) {
        $fname = $hash . "_v$i." . $ext;
        $target = $dir . $fname;
        $i++;
    }
    move_uploaded_file($file['tmp_name'], $target);
    if ($ext === 'jpg' || $ext === 'png') {
        resizeImage($target, $target);
        addWatermark($target);
    }
    return $fname;
}

// AJAX handler untuk upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $nomor_daftar = $_POST['nomor_daftar'] ?? 'unknown';
    $result = saveUpload($_FILES['file'], $nomor_daftar);
    echo json_encode(['result' => $result]);
    exit;
}
