<?php
// pages/item_edit.php
$pageTitle = 'Edit Item';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requirePermission([1, 2]); // Admin & Manager only
$db = getDB();

if (!isset($_GET['id'])) {
    header("Location: items.php");
    exit();
}

$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $supplier_id = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
    $department_id = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
    $asset_tag = trim($_POST['asset_tag']);
    $serial_number = trim($_POST['serial_number']);
    $unit = trim($_POST['unit']);
    $reorder_level = (int)$_POST['reorder_level'];
    $unit_price = (float)$_POST['unit_price'];
    $condition = $_POST['condition_status'];
    $location = trim($_POST['location']);
    
    $stmt = $db->prepare("UPDATE inventory_items SET name=?, category_id=?, supplier_id=?, department_id=?, asset_tag=?, serial_number=?, unit=?, reorder_level=?, unit_price=?, condition_status=?, location=? WHERE id=?");
    $stmt->bind_param("siiisssidssi", $name, $category_id, $supplier_id, $department_id, $asset_tag, $serial_number, $unit, $reorder_level, $unit_price, $condition, $location, $id);
    
    if ($stmt->execute()) {
        logActivity($db, $_SESSION['user_id'], 'Updated Item', 'InventoryItem', $id, "Updated $name");
        setFlashMessage('success', 'Item updated successfully.');
        header("Location: items.php");
        exit();
    } else {
        setFlashMessage('danger', 'Error updating item: ' . $db->error);
    }
}

$item = $db->query("SELECT * FROM inventory_items WHERE id = $id")->fetch_assoc();
if (!$item) {
    header("Location: items.php");
    exit();
}

$categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC");
$suppliers = $db->query("SELECT id, name FROM suppliers ORDER BY name ASC");
$departments = $db->query("SELECT id, name FROM departments ORDER BY name ASC");

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1>Edit Item</h1>
    <p>Modify asset details.</p>
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
        <input type="text" name="name" class="form-control" value="<?= clean($item['name']) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label required">Category</label>
        <select name="category_id" class="form-select" required>
          <?php while($c = $categories->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>" <?= $item['category_id'] == $c['id'] ? 'selected' : '' ?>><?= clean($c['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>
    
    <div class="form-row-3">
      <div class="form-group">
        <label class="form-label">Asset Tag</label>
        <input type="text" name="asset_tag" class="form-control" value="<?= clean($item['asset_tag']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Serial Number</label>
        <input type="text" name="serial_number" class="form-control" value="<?= clean($item['serial_number']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label required">Condition</label>
        <select name="condition_status" class="form-select" required>
          <option value="new" <?= $item['condition_status'] == 'new' ? 'selected' : '' ?>>New</option>
          <option value="good" <?= $item['condition_status'] == 'good' ? 'selected' : '' ?>>Good</option>
          <option value="fair" <?= $item['condition_status'] == 'fair' ? 'selected' : '' ?>>Fair</option>
          <option value="poor" <?= $item['condition_status'] == 'poor' ? 'selected' : '' ?>>Poor</option>
          <option value="damaged" <?= $item['condition_status'] == 'damaged' ? 'selected' : '' ?>>Damaged</option>
        </select>
      </div>
    </div>

    <div class="form-row-3">
      <div class="form-group">
        <label class="form-label">Quantity</label>
        <input type="text" class="form-control" value="<?= $item['quantity'] ?>" disabled>
        <div class="form-hint">Use Stock In/Out to modify quantity.</div>
      </div>
      <div class="form-group">
        <label class="form-label required">Unit</label>
        <input type="text" name="unit" class="form-control" value="<?= clean($item['unit']) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label required">Reorder Level</label>
        <input type="number" name="reorder_level" class="form-control" value="<?= $item['reorder_level'] ?>" min="0" required>
      </div>
    </div>
    
    <div class="form-row-3">
      <div class="form-group">
        <label class="form-label">Unit Price (₱)</label>
        <input type="number" step="0.01" name="unit_price" class="form-control" value="<?= $item['unit_price'] ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Supplier</label>
        <select name="supplier_id" class="form-select">
          <option value="">None / Unknown</option>
          <?php while($s = $suppliers->fetch_assoc()): ?>
            <option value="<?= $s['id'] ?>" <?= $item['supplier_id'] == $s['id'] ? 'selected' : '' ?>><?= clean($s['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Department Assignment</label>
        <select name="department_id" class="form-select">
          <option value="">Unassigned</option>
          <?php while($d = $departments->fetch_assoc()): ?>
            <option value="<?= $d['id'] ?>" <?= $item['department_id'] == $d['id'] ? 'selected' : '' ?>><?= clean($d['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>
    
    <div class="form-group">
      <label class="form-label">Physical Location</label>
      <input type="text" name="location" class="form-control" value="<?= clean($item['location']) ?>">
    </div>
    
    <div class="divider"></div>
    <button type="submit" class="btn btn-primary"><?= icon_check() ?> Update Item</button>
  </form>
</div>

<?php require_once '../includes/footer.php'; ?>
