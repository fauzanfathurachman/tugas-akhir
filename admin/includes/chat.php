<?php
// admin/includes/chat.php - Live chat user interface (AJAX polling)
require_once __DIR__ . '/../../config/database.php';
session_start();
$user_id = $_SESSION['user_id'] ?? 'guest_' . session_id();

// Handle AJAX polling for messages
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $last_id = (int)($_GET['last_id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM chat_messages WHERE (from_user=? OR to_user=?) AND id>? ORDER BY id ASC LIMIT 50");
    $stmt->execute([$user_id, $user_id, $last_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['messages' => $messages]);
    exit;
}
// Handle send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    $to = $_POST['to_user'] ?? 'admin';
    $stmt = $db->prepare("INSERT INTO chat_messages (from_user, to_user, message, timestamp, is_read) VALUES (?, ?, ?, NOW(), 0)");
    $stmt->execute([$user_id, $to, $msg]);
    echo json_encode(['success' => true]);
    exit;
}
?>
<!-- Simple chat UI -->
<div id="chatBox" style="max-width:400px;margin:auto;background:#fff;border-radius:8px;box-shadow:0 2px 8px #0001;padding:1rem;">
  <div id="chatMessages" style="height:300px;overflow-y:auto;"></div>
  <form id="chatForm" autocomplete="off" style="display:flex;gap:0.5rem;">
    <input type="text" id="chatInput" name="message" placeholder="Ketik pesan..." style="flex:1;">
    <button type="submit">Kirim</button>
  </form>
  <button id="attachFileBtn">📎</button>
  <input type="file" id="chatFile" style="display:none;">
</div>
<audio id="chatSound" src="/assets/sound/notify.mp3" preload="auto"></audio>
<script src="/assets/js/chat.js"></script>
