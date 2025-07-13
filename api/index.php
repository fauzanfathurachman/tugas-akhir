<?php
// api/index.php - RESTful API entry point
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../admin/includes/validator.php';
require_once __DIR__ . '/../vendor/autoload.php'; // For JWT
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

// JWT secret
$jwt_secret = 'ganti_secret_api';

// Rate limiting (simple, per IP, per endpoint)
function rateLimit($endpoint, $limit = 100, $seconds = 3600) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'api_' . md5($endpoint . $ip);
    if (!isset($_SESSION)) session_start();
    if (!isset($_SESSION[$key])) $_SESSION[$key] = [];
    $_SESSION[$key] = array_filter($_SESSION[$key], function($t) use ($seconds) { return $t > time() - $seconds; });
    if (count($_SESSION[$key]) >= $limit) {
        http_response_code(429);
        echo json_encode(['success'=>false,'error'=>'Rate limit exceeded']);
        exit;
    }
    $_SESSION[$key][] = time();
}

// JWT auth middleware
function requireAuth() {
    global $jwt_secret;
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.*)/', $auth, $m)) {
        try {
            $decoded = JWT::decode($m[1], new Key($jwt_secret, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['success'=>false,'error'=>'Invalid token']);
            exit;
        }
    }
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'No token']);
    exit;
}

// Routing
$path = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// POST /api/register
if ($path === '/api/register' && $method === 'POST') {
    rateLimit('register', 20, 3600);
    $data = json_decode(file_get_contents('php://input'), true);
    // Validasi & simpan data siswa (See <attachments> above for file contents. You may not need to search or read the file again.)
    // ...
    echo json_encode(['success'=>true,'message'=>'Registrasi berhasil']);
    exit;
}
// GET /api/status/{nomor_daftar}
if (preg_match('#^/api/status/(.+)$#', $path, $m) && $method === 'GET') {
    rateLimit('status', 100, 3600);
    $nomor = $m[1];
    // Query status siswa (See <attachments> above for file contents. You may not need to search or read the file again.)
    // ...
    echo json_encode(['success'=>true,'status'=>'Diterima']);
    exit;
}
// POST /api/upload (auth)
if ($path === '/api/upload' && $method === 'POST') {
    rateLimit('upload', 20, 3600);
    $user = requireAuth();
    // Handle upload (See <attachments> above for file contents. You may not need to search or read the file again.)
    // ...
    echo json_encode(['success'=>true,'file'=>'uploaded.pdf']);
    exit;
}
// GET /api/pengumuman
if ($path === '/api/pengumuman' && $method === 'GET') {
    rateLimit('pengumuman', 100, 3600);
    // Query pengumuman (See <attachments> above for file contents. You may not need to search or read the file again.)
    // ...
    echo json_encode(['success'=>true,'data'=>[]]);
    exit;
}
// GET /api/notifications (auth)
if ($path === '/api/notifications' && $method === 'GET') {
    rateLimit('notifications', 100, 3600);
    $user = requireAuth();
    // Query notifikasi user (See <attachments> above for file contents. You may not need to search or read the file again.)
    // ...
    echo json_encode(['success'=>true,'data'=>[]]);
    exit;
}
// API docs (Swagger)
if ($path === '/api/docs') {
    header('Content-Type: text/html');
    readfile(__DIR__ . '/swagger.html');
    exit;
}
// Default 404
http_response_code(404);
echo json_encode(['success'=>false,'error'=>'Endpoint not found']);
