<?php
// pages/activity_logs.php
$pageTitle = 'Audit Logs';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requirePermission([1]); // Admin only
$db = getDB();

$logs = $db->query("
    SELECT a.*, u.full_name as user_name, u.username
    FROM activity_logs a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 200
");

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1>Audit Logs</h1>
    <p>Monitor system usage and user activities</p>
  </div>
</div>

<div class="table-wrap">
  <div class="table-overflow">
    <table>
      <thead>
        <tr>
          <th>Timestamp</th>
          <th>User</th>
          <th>Action</th>
          <th>Entity / Details</th>
          <th>IP Address</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($logs->num_rows > 0): while($log = $logs->fetch_assoc()): ?>
        <tr>
          <td class="text-sm text-muted" style="white-space:nowrap;"><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></td>
          <td>
            <div class="font-semibold"><?= clean($log['user_name'] ?: 'System') ?></div>
            <?php if($log['username']): ?><div class="text-xs text-muted">@<?= clean($log['username']) ?></div><?php endif; ?>
          </td>
          <td><span class="badge badge-secondary"><?= clean($log['action']) ?></span></td>
          <td class="text-sm">
            <b><?= clean($log['entity_type']) ?></b>
            <?php if($log['entity_id']): ?> (ID: <?= $log['entity_id'] ?>)<?php endif; ?>
            <?php if($log['details']): ?>
              <br><span class="text-muted"><?= clean($log['details']) ?></span>
            <?php endif; ?>
          </td>
          <td class="text-xs text-muted font-mono"><?= clean($log['ip_address']) ?></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="5" class="table-empty">No logs available.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
