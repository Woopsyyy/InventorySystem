<?php
// pages/stock_out.php
$pageTitle = 'Stock Out';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requirePermission([1, 2]); // Admin & Manager only
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = (int)$_POST['item_id'];
    $quantity = (int)$_POST['quantity'];
    $ref_no = trim($_POST['reference_number']);
    $remarks = trim($_POST['remarks']);
    $userId = $_SESSION['user_id'];

    if ($item_id && $quantity > 0) {
        // Check current qty
        $check = $db->query("SELECT quantity FROM inventory_items WHERE id = $item_id")->fetch_assoc();
        
        if ($check['quantity'] >= $quantity) {
            $db->begin_transaction();
            try {
                // Update inventory
                $stmt = $db->prepare("UPDATE inventory_items SET quantity = quantity - ? WHERE id = ?");
                $stmt->bind_param("ii", $quantity, $item_id);
                $stmt->execute();
                
                // Log movement
                $stmt = $db->prepare("INSERT INTO stock_movements (item_id, movement_type, quantity, reference_number, remarks, performed_by) VALUES (?, 'out', ?, ?, ?, ?)");
                $stmt->bind_param("iissi", $item_id, $quantity, $ref_no, $remarks, $userId);
                $stmt->execute();
                
                $db->commit();
                logActivity($db, $userId, 'Stock Out', 'InventoryItem', $item_id, "Removed $quantity units. Ref: $ref_no");
                setFlashMessage('success', 'Stock removed successfully.');
                header("Location: stock_movements.php");
                exit();
            } catch (Exception $e) {
                $db->rollback();
                setFlashMessage('danger', 'Error updating stock: ' . $e->getMessage());
            }
        } else {
            setFlashMessage('danger', 'Insufficient stock available.');
        }
    } else {
        setFlashMessage('warning', 'Please provide a valid item and quantity.');
    }
}

$items = $db->query("SELECT id, name, asset_tag, quantity, unit FROM inventory_items WHERE quantity > 0 ORDER BY name ASC");

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1 class="text-danger">Stock Out</h1>
    <p>Remove stock quantities (usage, loss, etc.)</p>
  </div>
  <div class="page-header-actions">
    <a href="stock_movements.php" class="btn btn-outline">Cancel</a>
  </div>
</div>

<div class="card" style="max-width: 600px;">
  <form method="POST" action="">
    <div class="form-group">
      <label class="form-label required">Select Item</label>
      <select name="item_id" class="form-select" required autofocus>
        <option value="">Select Item (Only items with stock shown)...</option>
        <?php while($i = $items->fetch_assoc()): ?>
        <option value="<?= $i['id'] ?>">
          <?= clean($i['name']) ?> (<?= clean($i['asset_tag']) ?>) - <?= $i['quantity'] ?> <?= clean($i['unit']) ?> avail
        </option>
        <?php endwhile; ?>
      </select>
    </div>
    
    <div class="form-row">
      <div class="form-group">
        <label class="form-label required">Quantity to Remove</label>
        <input type="number" name="quantity" class="form-control" min="1" required>
      </div>
      <div class="form-group">
        <label class="form-label">Reference / Ticket Number</label>
        <input type="text" name="reference_number" class="form-control">
      </div>
    </div>
    
    <div class="form-group">
      <label class="form-label required">Remarks / Reason</label>
      <textarea name="remarks" class="form-control" rows="2" required></textarea>
    </div>
    
    <button type="submit" class="btn btn-danger w-full" style="justify-content:center;">Submit Stock Out</button>
  </form>
</div>

<?php require_once '../includes/footer.php'; ?>
