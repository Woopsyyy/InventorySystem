<?php
// pages/reports.php
$pageTitle = 'Reports';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requirePermission([1, 2]); // Admin & Manager only
$db = getDB();

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1>System Reports</h1>
    <p>Generate and export analytical data for your school's inventory</p>
  </div>
</div>

<div class="stats-grid">
  <!-- Report Card 1 -->
  <div class="card">
    <div class="card-header border-0 mb-0 pb-0">
      <div class="font-semibold text-primary">Inventory Status</div>
    </div>
    <div class="card-body mt-2">
      <p class="text-sm text-muted mb-4">Current snapshot of all items, quantities, and their estimated values.</p>
      <div class="d-flex gap-2">
        <a href="items.php" class="btn btn-outline btn-sm w-full" style="justify-content:center;"><?= icon_eye() ?> View</a>
        <a href="export.php?type=inventory" class="btn btn-primary btn-sm w-full" style="justify-content:center;"><?= icon_download() ?> CSV</a>
      </div>
    </div>
  </div>

  <!-- Report Card 2 -->
  <div class="card">
    <div class="card-header border-0 mb-0 pb-0">
      <div class="font-semibold text-primary">Stock Movements</div>
    </div>
    <div class="card-body mt-2">
      <p class="text-sm text-muted mb-4">Historical record of all items checking in and out of the warehouse.</p>
      <div class="d-flex gap-2">
        <a href="stock_movements.php" class="btn btn-outline btn-sm w-full" style="justify-content:center;"><?= icon_eye() ?> View</a>
        <a href="export.php?type=movements" class="btn btn-primary btn-sm w-full" style="justify-content:center;"><?= icon_download() ?> CSV</a>
      </div>
    </div>
  </div>

  <!-- Report Card 3 -->
  <div class="card">
    <div class="card-header border-0 mb-0 pb-0">
      <div class="font-semibold text-primary">Borrowing History</div>
    </div>
    <div class="card-body mt-2">
      <p class="text-sm text-muted mb-4">Logs of all approved, returned, and overdue borrowing requests.</p>
      <div class="d-flex gap-2">
        <a href="borrow.php" class="btn btn-outline btn-sm w-full" style="justify-content:center;"><?= icon_eye() ?> View</a>
        <a href="export.php?type=borrowing" class="btn btn-primary btn-sm w-full" style="justify-content:center;"><?= icon_download() ?> CSV</a>
      </div>
    </div>
  </div>

  <!-- Report Card 4 -->
  <div class="card">
    <div class="card-header border-0 mb-0 pb-0">
      <div class="font-semibold text-primary">Damaged & Repairs</div>
    </div>
    <div class="card-body mt-2">
      <p class="text-sm text-muted mb-4">Details of items reported damaged, their repair costs and statuses.</p>
      <div class="d-flex gap-2">
        <a href="damaged.php" class="btn btn-outline btn-sm w-full" style="justify-content:center;"><?= icon_eye() ?> View</a>
        <a href="export.php?type=damaged" class="btn btn-primary btn-sm w-full" style="justify-content:center;"><?= icon_download() ?> CSV</a>
      </div>
    </div>
  </div>
</div>

<div class="card mt-4">
  <div class="card-header">
    <span class="card-title">Custom Report Generator</span>
  </div>
  <form action="#" method="GET">
    <div class="form-row-3">
      <div class="form-group">
        <label class="form-label">Report Type</label>
        <select class="form-select" name="type">
          <option>Inventory Valuation</option>
          <option>Low Stock Items</option>
          <option>Audit Logs</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Date From</label>
        <input type="date" class="form-control" name="date_from">
      </div>
      <div class="form-group">
        <label class="form-label">Date To</label>
        <input type="date" class="form-control" name="date_to">
      </div>
    </div>
    <div class="mt-2 text-right">
      <button type="submit" class="btn btn-primary"><?= icon_chart() ?> Generate Report</button>
    </div>
  </form>
</div>

<?php require_once '../includes/footer.php'; ?>
