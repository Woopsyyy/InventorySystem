<?php
// pages/stock_movements.php
$pageTitle = 'Stock Movements';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requirePermission([1, 2]); // Admin & Manager only
$db = getDB();

$sql = "
    SELECT sm.*, i.name as item_name, u.full_name as performed_by_name
    FROM stock_movements sm
    JOIN inventory_items i ON sm.item_id = i.id
    JOIN users u ON sm.performed_by = u.id
    ORDER BY sm.movement_date DESC
";
$movements = $db->query($sql);

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1>Stock Movements</h1>
    <p>Track all inventory inbound and outbound history</p>
  </div>
  <div class="page-header-actions">
    <a href="stock_in.php" class="btn btn-primary"><?= icon_plus() ?> Stock In</a>
    <a href="stock_out.php" class="btn btn-danger"><?= icon_arrows() ?> Stock Out</a>
  </div>
</div>

<div class="table-wrap">
  <div class="table-overflow">
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Item</th>
          <th>Type</th>
          <th>Quantity</th>
          <th>Reference No</th>
          <th>Performed By</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($movements && $movements->num_rows > 0): while($m = $movements->fetch_assoc()): ?>
        <tr>
          <td class="text-muted text-sm"><?= date('M d, Y H:i', strtotime($m['movement_date'])) ?></td>
          <td class="font-semibold"><?= clean($m['item_name']) ?></td>
          <td>
            <?php if($m['movement_type'] == 'in'): ?>
              <span class="badge badge-success">Stock In</span>
            <?php elseif($m['movement_type'] == 'out'): ?>
              <span class="badge badge-danger">Stock Out</span>
            <?php else: ?>
              <span class="badge badge-secondary">Adjustment</span>
            <?php endif; ?>
          </td>
          <td class="<?= $m['movement_type'] == 'out' ? 'text-danger' : 'text-success' ?> font-semibold">
            <?= $m['movement_type'] == 'out' ? '-' : '+' ?><?= $m['quantity'] ?>
          </td>
          <td><?= clean($m['reference_number'] ?: '-') ?></td>
          <td><?= clean($m['performed_by_name']) ?></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6" class="table-empty">No stock movements recorded yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
