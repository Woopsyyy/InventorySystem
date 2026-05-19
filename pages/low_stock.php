<?php
// pages/low_stock.php
$pageTitle = 'Low Stock Alerts';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requireLogin();
$db = getDB();

// Items where quantity <= reorder_level
$lowStock = $db->query("
    SELECT i.*, c.name as category_name, s.name as supplier_name
    FROM inventory_items i
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN suppliers s ON i.supplier_id = s.id
    WHERE i.quantity <= i.reorder_level
    ORDER BY i.quantity ASC
");

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1 class="text-danger">Low Stock Alerts</h1>
    <p>Items that have fallen below their specified reorder levels.</p>
  </div>
  <div class="page-header-actions">
    <a href="reports.php" class="btn btn-outline"><?= icon_download() ?> Export</a>
  </div>
</div>

<div class="table-wrap">
  <div class="table-overflow">
    <table>
      <thead>
        <tr>
          <th>Item Name</th>
          <th>Category</th>
          <th>Current Stock</th>
          <th>Reorder Level</th>
          <th>Supplier</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($lowStock->num_rows > 0): while($i = $lowStock->fetch_assoc()): ?>
        <tr>
          <td>
            <div class="font-semibold"><?= clean($i['name']) ?></div>
            <div class="text-xs text-muted"><?= clean($i['asset_tag']) ?></div>
          </td>
          <td><?= clean($i['category_name']) ?></td>
          <td class="qty-low"><?= $i['quantity'] ?> <?= clean($i['unit']) ?></td>
          <td><?= $i['reorder_level'] ?></td>
          <td class="text-sm text-muted"><?= clean($i['supplier_name'] ?: 'No Supplier') ?></td>
          <td>
            <?php if(hasPermission([1,2])): ?>
            <a href="stock_in.php?item_id=<?= $i['id'] ?>" class="btn btn-primary btn-xs text-xs">Restock</a>
            <?php else: ?>
            <span class="text-muted text-xs">Notify Manager</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6" class="table-empty">All items are sufficiently stocked.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
