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
    <div class="stat-icon-wrapper">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
    </div>
    <div class="stat-content">
      <div class="stat-label">Total Assets</div>
      <div class="stat-value"><?= number_format($totalItems) ?></div>
      <div class="stat-sub">Unique items in inventory</div>
    </div>
  </div>
  <div class="stat-card success">
    <div class="stat-icon-wrapper">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
    </div>
    <div class="stat-content">
      <div class="stat-label">Inventory Value</div>
      <div class="stat-value"><?= formatCurrency($totalValue) ?></div>
      <div class="stat-sub">Total estimated value</div>
    </div>
  </div>
  <div class="stat-card <?= $lowStock > 0 ? 'danger' : 'success' ?>">
    <div class="stat-icon-wrapper">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <div class="stat-content">
      <div class="stat-label">Low Stock Alerts</div>
      <div class="stat-value"><?= number_format($lowStock) ?></div>
      <div class="stat-sub">Items below reorder level</div>
    </div>
  </div>
  <div class="stat-card warning">
    <div class="stat-icon-wrapper">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
    </div>
    <div class="stat-content">
      <div class="stat-label">Pending Requests</div>
      <div class="stat-value"><?= number_format($pendingRequests) ?></div>
      <div class="stat-sub">Awaiting approval</div>
    </div>
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
