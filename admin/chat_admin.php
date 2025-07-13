<?php
// admin/chat_admin.php - Admin chat panel
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../config/database.php';
// List active chat sessions
$sessions = $db->query("SELECT * FROM chat_sessions ORDER BY last_active DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container mt-4">
  <h2>Live Chat Admin Panel</h2>
  <div class="row">
    <div class="col-md-3">
      <h5>Sessions</h5>
      <ul id="sessionList">
        <?php foreach($sessions as $s): ?>
        <li data-user="<?php echo htmlspecialchars($s['user_id']); ?>">
          <?php echo htmlspecialchars($s['user_id']); ?>
          <span class="badge <?php echo $s['online'] ? 'bg-success':'bg-secondary'; ?>">●</span>
        </li>
        <?php endforeach; ?>
      </ul>
      <h6>Canned Responses</h6>
      <ul id="cannedList">
        <li>Kami akan segera membalas pesan Anda.</li>
        <li>Silakan upload dokumen di menu pendaftaran.</li>
        <li>Terima kasih sudah menghubungi admin.</li>
      </ul>
    </div>
    <div class="col-md-9">
      <div id="adminChatBox"></div>
    </div>
  </div>
</div>
<script src="/assets/js/chat_admin.js"></script>
