<?php
// pages/suppliers.php
$pageTitle = 'Suppliers';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requireLogin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_supplier') {
    if (hasPermission([1, 2])) {
        $name = trim($_POST['name']);
        $contact = trim($_POST['contact_person']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        
        if ($name) {
            $stmt = $db->prepare("INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $contact, $email, $phone, $address);
            if ($stmt->execute()) {
                logActivity($db, $_SESSION['user_id'], 'Added Supplier', 'Supplier', $stmt->insert_id, "Added $name");
                setFlashMessage('success', 'Supplier added successfully.');
                header("Location: suppliers.php");
                exit();
            } else {
                setFlashMessage('danger', 'Failed to add supplier: ' . $db->error);
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_supplier') {
    if (hasPermission([1, 2])) {
        $delete_id = (int)$_POST['supplier_id'];
        $stmt = $db->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            logActivity($db, $_SESSION['user_id'], 'Deleted Supplier', 'Supplier', $delete_id, "Deleted supplier ID $delete_id");
            setFlashMessage('success', 'Supplier deleted successfully.');
        } else {
            setFlashMessage('danger', 'Cannot delete supplier in use.');
        }
        header("Location: suppliers.php");
        exit();
    }
}

$suppliers = $db->query("
    SELECT s.*, COUNT(i.id) as item_count 
    FROM suppliers s 
    LEFT JOIN inventory_items i ON s.id = i.supplier_id 
    GROUP BY s.id 
    ORDER BY s.name ASC
");

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1>Suppliers</h1>
    <p>Manage vendors and suppliers for inventory items</p>
  </div>
  <div class="page-header-actions">
    <?php if(hasPermission([1,2])): ?>
    <button class="btn btn-primary" data-modal-target="modal-add-supplier"><?= icon_plus() ?> Add Supplier</button>
    <?php endif; ?>
  </div>
</div>

<div class="table-wrap">
  <div class="table-overflow">
    <table>
      <thead>
        <tr>
          <th>Supplier Name</th>
          <th>Contact Info</th>
          <th>Address</th>
          <th>Items Count</th>
          <th>Status</th>
          <?php if(hasPermission([1,2])): ?><th>Actions</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if ($suppliers->num_rows > 0): while($s = $suppliers->fetch_assoc()): ?>
        <tr>
          <td class="font-semibold"><?= clean($s['name']) ?></td>
          <td>
            <div class="text-sm"><?= clean($s['contact_person']) ?></div>
            <div class="text-xs text-muted"><?= clean($s['email']) ?> | <?= clean($s['phone']) ?></div>
          </td>
          <td class="text-sm text-muted"><?= clean($s['address']) ?></td>
          <td><span class="badge badge-secondary"><?= $s['item_count'] ?></span></td>
          <td><?= getStatusBadge($s['status']) ?></td>
          <?php if(hasPermission([1,2])): ?>
          <td>
            <form method="POST" action="" style="margin:0;display:inline-block;">
              <input type="hidden" name="action" value="delete_supplier">
              <input type="hidden" name="supplier_id" value="<?= $s['id'] ?>">
              <button type="submit" class="btn btn-outline btn-xs text-danger" onclick="return confirm('Delete this supplier?')"><?= icon_trash() ?></button>
            </form>
          </td>
          <?php endif; ?>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6" class="table-empty">No suppliers found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal-overlay" id="modal-add-supplier">
  <div class="modal" style="max-width: 500px;">
    <div class="modal-header">
      <div class="modal-title">Add Supplier</div>
      <button class="modal-close" data-modal-close>&times;</button>
    </div>
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_supplier">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label required">Supplier Name</label>
          <input type="text" name="name" class="form-control" required autofocus>
        </div>
        <div class="form-group">
          <label class="form-label">Contact Person</label>
          <input type="text" name="contact_person" class="form-control">
        </div>
        <div class="form-row">
          <div class="form-group mb-0">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control">
          </div>
          <div class="form-group mb-0">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control">
          </div>
        </div>
        <div class="form-group mt-3 mb-0">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">Save Supplier</button>
      </div>
    </form>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
