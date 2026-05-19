<?php
// pages/item_add.php
$pageTitle = 'Add Item';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requirePermission([1, 2]); // Admin & Manager only
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $supplier_id = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
    $department_id = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
    $asset_tag = trim($_POST['asset_tag']);
    $serial_number = trim($_POST['serial_number']);
    $quantity = (int)$_POST['quantity'];
    $unit = trim($_POST['unit']);
    $reorder_level = (int)$_POST['reorder_level'];
    $unit_price = (float)$_POST['unit_price'];
    $condition = $_POST['condition_status'];
    $location = trim($_POST['location']);
    
    $stmt = $db->prepare("INSERT INTO inventory_items (name, category_id, supplier_id, department_id, asset_tag, serial_number, quantity, unit, reorder_level, unit_price, condition_status, location, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $userId = $_SESSION['user_id'];
    
    $stmt->bind_param("siiissisidssi", $name, $category_id, $supplier_id, $department_id, $asset_tag, $serial_number, $quantity, $unit, $reorder_level, $unit_price, $condition, $location, $userId);
    
    if ($stmt->execute()) {
        logActivity($db, $userId, 'Added Item', 'InventoryItem', $stmt->insert_id, "Added $name");
        setFlashMessage('success', 'Item added successfully.');
        header("Location: items.php");
        exit();
    } else {
        setFlashMessage('danger', 'Error adding item: ' . $db->error);
    }
}

$categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC");
$suppliers = $db->query("SELECT id, name FROM suppliers ORDER BY name ASC");
$departments = $db->query("SELECT id, name FROM departments ORDER BY name ASC");

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1>Add New Item</h1>
    <p>Register a new asset or consumable to the inventory.</p>
  </div>
  <div class="page-header-actions">
    <a href="items.php" class="btn btn-outline">Cancel</a>
  </div>
</div>

<div class="card">
  <form method="POST" action="">
    <div class="form-row">
      <div class="form-group">
        <label class="form-label required">Item Name</label>
        <input type="text" name="name" class="form-control" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label required">Category</label>
        <select name="category_id" class="form-select" required>
          <option value="">Select Category...</option>
          <?php while($c = $categories->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"><?= clean($c['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>
    
    <div class="form-row-3">
      <div class="form-group">
        <label class="form-label">Asset Tag</label>
        <input type="text" name="asset_tag" class="form-control">
      </div>
      <div class="form-group">
        <label class="form-label">Serial Number</label>
        <input type="text" name="serial_number" class="form-control">
      </div>
      <div class="form-group">
        <label class="form-label required">Condition</label>
        <select name="condition_status" class="form-select" required>
          <option value="new">New</option>
          <option value="good">Good</option>
          <option value="fair">Fair</option>
          <option value="poor">Poor</option>
        </select>
      </div>
    </div>

    <div class="form-row-3">
      <div class="form-group">
        <label class="form-label required">Initial Quantity</label>
        <input type="number" name="quantity" class="form-control" value="0" min="0" required>
      </div>
      <div class="form-group">
        <label class="form-label required">Unit (e.g., pcs, ream, box)</label>
        <input type="text" name="unit" class="form-control" value="pcs" required>
      </div>
      <div class="form-group">
        <label class="form-label required">Reorder Level</label>
        <input type="number" name="reorder_level" class="form-control" value="5" min="0" required>
      </div>
    </div>
    
    <div class="form-row-3">
      <div class="form-group">
        <label class="form-label">Unit Price (₱)</label>
        <input type="number" step="0.01" name="unit_price" class="form-control" value="0.00">
      </div>
      <div class="form-group">
        <label class="form-label">Supplier</label>
        <select name="supplier_id" class="form-select">
          <option value="">None / Unknown</option>
          <?php while($s = $suppliers->fetch_assoc()): ?>
            <option value="<?= $s['id'] ?>"><?= clean($s['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Department Assignment</label>
        <select name="department_id" class="form-select">
          <option value="">Unassigned</option>
          <?php while($d = $departments->fetch_assoc()): ?>
            <option value="<?= $d['id'] ?>"><?= clean($d['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>
    
    <div class="form-group">
      <label class="form-label">Physical Location</label>
      <input type="text" name="location" class="form-control" placeholder="e.g. Storage Room B, Shelf 3">
    </div>
    
    <div class="divider"></div>
    <button type="submit" class="btn btn-primary"><?= icon_check() ?> Save Item</button>
  </form>
</div>

<?php require_once '../includes/footer.php'; ?>
