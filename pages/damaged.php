<?php
// pages/damaged.php
$pageTitle = 'Damaged Items';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requireLogin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (hasPermission([1, 2, 3])) {
        $item_id = (int)$_POST['item_id'];
        $desc = trim($_POST['description']);
        $userId = $_SESSION['user_id'];
        
        if ($item_id && $desc) {
            $db->begin_transaction();
            try {
                $stmt = $db->prepare("INSERT INTO damaged_items (item_id, reported_by, description) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $item_id, $userId, $desc);
                $stmt->execute();
                
                $stmt2 = $db->prepare("UPDATE inventory_items SET condition_status = 'damaged' WHERE id = ?");
                $stmt2->bind_param("i", $item_id);
                $stmt2->execute();
                
                $db->commit();
                logActivity($db, $userId, 'Reported Damage', 'DamagedItem', $item_id, "Reported damage");
                setFlashMessage('success', 'Damage report submitted successfully.');
                header("Location: damaged.php");
                exit();
            } catch (Exception $e) {
                $db->rollback();
                setFlashMessage('danger', 'Failed to report damage: ' . $e->getMessage());
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_fixed') {
    if (hasPermission([1, 2])) {
        $report_id = (int)$_POST['report_id'];
        $item_id = (int)$_POST['item_id'];
        
        $db->begin_transaction();
        try {
            $stmt = $db->prepare("UPDATE damaged_items SET status = 'fixed' WHERE id = ?");
            $stmt->bind_param("i", $report_id);
            $stmt->execute();
            
            $stmt2 = $db->prepare("UPDATE inventory_items SET condition_status = 'good' WHERE id = ?");
            $stmt2->bind_param("i", $item_id);
            $stmt2->execute();
            
            $db->commit();
            logActivity($db, $_SESSION['user_id'], 'Fixed Item', 'DamagedItem', $report_id, "Marked damaged item as fixed");
            setFlashMessage('success', 'Item marked as fixed.');
        } catch (Exception $e) {
            $db->rollback();
            setFlashMessage('danger', 'Failed to update item: ' . $e->getMessage());
        }
        header("Location: damaged.php");
        exit();
    }
}

$damaged = $db->query("
    SELECT d.*, i.name as item_name, i.asset_tag, u.full_name as reported_by_name
    FROM damaged_items d
    JOIN inventory_items i ON d.item_id = i.id
    JOIN users u ON d.reported_by = u.id
    ORDER BY d.report_date DESC
");

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1 class="text-danger">Damaged Items</h1>
    <p>Track repairs, damages, and disposals</p>
  </div>
  <div class="page-header-actions">
    <button class="btn btn-outline" data-modal-target="modal-report-damage"><?= icon_alert() ?> Report Damage</button>
  </div>
</div>

<div class="table-wrap">
  <div class="table-overflow">
    <table>
      <thead>
        <tr>
          <th>Item</th>
          <th>Report Date</th>
          <th>Reported By</th>
          <th>Status</th>
          <th>Repair Cost</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($damaged && $damaged->num_rows > 0): while($d = $damaged->fetch_assoc()): ?>
        <tr>
          <td>
            <div class="font-semibold"><?= clean($d['item_name']) ?></div>
            <div class="text-xs text-muted"><?= clean($d['asset_tag']) ?></div>
          </td>
          <td class="text-sm text-muted"><?= date('M d, Y', strtotime($d['report_date'])) ?></td>
          <td><?= clean($d['reported_by_name']) ?></td>
          <td>
            <?php
              if($d['status'] == 'reported') echo '<span class="badge badge-warning">Reported</span>';
              if($d['status'] == 'repairing') echo '<span class="badge badge-info">Repairing</span>';
              if($d['status'] == 'fixed') echo '<span class="badge badge-success">Fixed</span>';
              if($d['status'] == 'disposed') echo '<span class="badge badge-dark">Disposed</span>';
            ?>
          </td>
          <td class="text-sm"><?= formatCurrency($d['repair_cost']) ?></td>
          <td>
            <?php if($d['status'] != 'fixed'): ?>
            <form method="POST" action="" style="margin:0;display:inline-block;">
              <input type="hidden" name="action" value="mark_fixed">
              <input type="hidden" name="report_id" value="<?= $d['id'] ?>">
              <input type="hidden" name="item_id" value="<?= $d['item_id'] ?>">
              <button type="submit" class="btn btn-outline btn-xs" title="Mark Fixed" onclick="return confirm('Mark this item as fixed and return it to good condition?')"><?= icon_check() ?> Fixed</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6" class="table-empty">No damaged items reported.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Report Damage -->
<div class="modal-overlay" id="modal-report-damage">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Report Damaged Item</div>
      <button class="modal-close" data-modal-close>&times;</button>
    </div>
    <form method="POST" action="">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label required">Select Item</label>
          <select name="item_id" class="form-select" required>
            <option value="">Select Item...</option>
            <?php
            $items = $db->query("SELECT id, name, asset_tag FROM inventory_items WHERE condition_status != 'damaged'");
            while($i = $items->fetch_assoc()) {
                echo "<option value='{$i['id']}'>".clean($i['name'])." (".clean($i['asset_tag']).")</option>";
            }
            ?>
          </select>
        </div>
        <div class="form-group mb-0">
          <label class="form-label required">Damage Description</label>
          <textarea name="description" class="form-control" rows="3" required placeholder="Describe what happened to the item..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-danger">Submit Report</button>
      </div>
    </form>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
