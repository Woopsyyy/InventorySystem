<?php
// dashboard.php
$pageTitle = 'Dashboard';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';

requireLogin();
$db = getDB();
$roleId = getUserRole();

// Gather stats
$totalItems = $db->query("SELECT COUNT(*) FROM inventory_items")->fetch_row()[0];
$totalValue = $db->query("SELECT COALESCE(SUM(quantity * unit_price), 0) FROM inventory_items")->fetch_row()[0];
$lowStock = $db->query("SELECT COUNT(*) FROM inventory_items WHERE quantity <= reorder_level")->fetch_row()[0];
$pendingRequests = $db->query("SELECT COUNT(*) FROM borrow_requests WHERE status = 'pending'")->fetch_row()[0];

// Recent Movements
$recentMovements = $db->query("
    SELECT sm.*, i.name as item_name, u.full_name 
    FROM stock_movements sm
    JOIN inventory_items i ON sm.item_id = i.id
    JOIN users u ON sm.performed_by = u.id
    ORDER BY sm.movement_date DESC LIMIT 5
");

// Pending Borrow Requests
$recentRequests = $db->query("
    SELECT br.*, i.name as item_name, u.full_name as requested_by
    FROM borrow_requests br
    JOIN inventory_items i ON br.item_id = i.id
    JOIN users u ON br.user_id = u.id
    WHERE br.status = 'pending'
    ORDER BY br.request_date DESC LIMIT 5
");

require_once 'includes/header.php';
?>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card info">
    <div class="stat-label">Total Assets</div>
    <div class="stat-value"><?= number_format($totalItems) ?></div>
    <div class="stat-sub">Unique items in inventory</div>
  </div>
  <div class="stat-card success">
    <div class="stat-label">Inventory Value</div>
    <div class="stat-value"><?= formatCurrency($totalValue) ?></div>
    <div class="stat-sub">Total estimated value</div>
  </div>
  <div class="stat-card <?= $lowStock > 0 ? 'danger' : 'success' ?>">
    <div class="stat-label">Low Stock Alerts</div>
    <div class="stat-value"><?= number_format($lowStock) ?></div>
    <div class="stat-sub">Items below reorder level</div>
  </div>
  <div class="stat-card warning">
    <div class="stat-label">Pending Requests</div>
    <div class="stat-value"><?= number_format($pendingRequests) ?></div>
    <div class="stat-sub">Awaiting approval</div>
  </div>
</div>

<div class="two-col">
  <!-- Recent Movements -->
  <div class="table-wrap">
    <div class="table-toolbar">
      <span class="section-title">Recent Stock Movements</span>
      <a href="<?= BASE_URL ?>pages/stock_movements.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="table-overflow">
      <table>
        <thead>
          <tr>
            <th>Item</th>
            <th>Type</th>
            <th>Qty</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($recentMovements->num_rows > 0): while($m = $recentMovements->fetch_assoc()): ?>
          <tr>
            <td><?= clean($m['item_name']) ?></td>
            <td>
              <?php if($m['movement_type'] == 'in'): ?>
                <span class="badge badge-success">IN</span>
              <?php elseif($m['movement_type'] == 'out'): ?>
                <span class="badge badge-danger">OUT</span>
              <?php else: ?>
                <span class="badge badge-secondary">ADJ</span>
              <?php endif; ?>
            </td>
            <td><?= $m['quantity'] ?></td>
            <td class="text-muted text-sm"><?= date('M d, Y', strtotime($m['movement_date'])) ?></td>
          </tr>
          <?php endwhile; else: ?>
          <tr><td colspan="4" class="table-empty">No recent movements found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pending Requests -->
  <div class="table-wrap">
    <div class="table-toolbar">
      <span class="section-title">Pending Borrow Requests</span>
      <a href="<?= BASE_URL ?>pages/borrow.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="table-overflow">
      <table>
        <thead>
          <tr>
            <th>Item</th>
            <th>Requested By</th>
            <th>Date Needed</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($recentRequests->num_rows > 0): while($r = $recentRequests->fetch_assoc()): ?>
          <tr>
            <td>
              <div class="font-semibold"><?= clean($r['item_name']) ?></div>
              <div class="text-xs text-muted">Qty: <?= $r['quantity'] ?></div>
            </td>
            <td><?= clean($r['requested_by']) ?></td>
            <td class="text-muted text-sm"><?= date('M d, Y', strtotime($r['expected_return_date'])) ?></td>
            <td>
              <a href="<?= BASE_URL ?>pages/borrow.php" class="btn btn-outline btn-xs">Review</a>
            </td>
          </tr>
          <?php endwhile; else: ?>
          <tr><td colspan="4" class="table-empty">No pending requests.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
