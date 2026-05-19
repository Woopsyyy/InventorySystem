<?php
// pages/notifications.php
$pageTitle = 'Notifications';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requireLogin();
$db = getDB();
$userId = $_SESSION['user_id'];

// Mark all as read when visiting
$db->query("UPDATE notifications SET is_read = 1 WHERE user_id = $userId");

$notifs = $db->query("SELECT * FROM notifications WHERE user_id = $userId ORDER BY created_at DESC LIMIT 50");

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1>Notifications</h1>
    <p>System alerts, approvals, and reminders</p>
  </div>
</div>

<div class="card" style="max-width: 800px;">
  <?php if ($notifs && $notifs->num_rows > 0): while($n = $notifs->fetch_assoc()): ?>
    <div class="alert alert-<?= clean($n['type']) ?> mb-3 d-flex align-center gap-3">
      <div style="flex:1;">
        <div class="font-semibold text-primary"><?= clean($n['title']) ?></div>
        <div class="text-sm"><?= clean($n['message']) ?></div>
      </div>
      <div class="text-xs text-muted" style="white-space:nowrap;">
        <?= date('M d, H:i', strtotime($n['created_at'])) ?>
      </div>
    </div>
  <?php endwhile; else: ?>
    <div class="empty-state">
      <?= icon_alert() ?>
      <p class="mt-2">You don't have any notifications right now.</p>
    </div>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
