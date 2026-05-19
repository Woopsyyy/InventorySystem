<?php
// pages/settings.php
$pageTitle = 'Settings';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requirePermission([1]); // Admin only
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    foreach ($_POST['settings'] as $key => $val) {
        $stmt->bind_param("ss", $val, $key);
        $stmt->execute();
    }
    setFlashMessage('success', 'Settings updated successfully.');
    header("Location: settings.php");
    exit();
}

$settingsQuery = $db->query("SELECT * FROM settings ORDER BY id ASC");
$settings = [];
while($row = $settingsQuery->fetch_assoc()) {
    $settings[$row['setting_key']] = $row;
}

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1>System Settings</h1>
    <p>Configure global application parameters</p>
  </div>
</div>

<div class="card" style="max-width: 600px;">
  <form method="POST" action="">
    
    <?php foreach($settings as $key => $s): ?>
    <div class="form-group">
      <label class="form-label"><?= clean(ucwords(str_replace('_', ' ', $key))) ?></label>
      <input type="text" name="settings[<?= $key ?>]" class="form-control" value="<?= clean($s['setting_value']) ?>">
      <div class="form-hint"><?= clean($s['description']) ?></div>
    </div>
    <?php endforeach; ?>
    
    <div class="divider"></div>
    
    <button type="submit" class="btn btn-primary"><?= icon_check() ?> Save Settings</button>
  </form>
</div>

<?php require_once '../includes/footer.php'; ?>
