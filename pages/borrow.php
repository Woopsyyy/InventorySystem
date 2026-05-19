<?php
// pages/borrow.php
$pageTitle = 'Borrow Requests';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requireLogin();
$db = getDB();
$role = getUserRole();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (hasPermission([1, 2])) {
        $request_id = (int)$_POST['request_id'];
        $action = $_POST['action'];
        
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        
        $db->begin_transaction();
        try {
            $stmt = $db->prepare("UPDATE borrow_requests SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $request_id);
            
            if ($stmt->execute()) {
                if ($action === 'approve') {
                    // Update inventory quantity and insert into history
                    $req = $db->query("SELECT item_id, quantity FROM borrow_requests WHERE id = $request_id")->fetch_assoc();
                    
                    // Deduct from inventory
                    $stmt2 = $db->prepare("UPDATE inventory_items SET quantity = quantity - ? WHERE id = ?");
                    $stmt2->bind_param("ii", $req['quantity'], $req['item_id']);
                    $stmt2->execute();
                    
                    // Insert into borrow history
                    $stmt3 = $db->prepare("INSERT INTO borrow_history (request_id, borrow_date) VALUES (?, CURRENT_TIMESTAMP)");
                    $stmt3->bind_param("i", $request_id);
                    $stmt3->execute();
                }
                
                $db->commit();
                logActivity($db, $userId, ucfirst($action) . ' Borrow Request', 'BorrowRequest', $request_id, "Status changed to $status");
                setFlashMessage('success', "Request $status successfully.");
            } else {
                throw new Exception($db->error);
            }
        } catch (Exception $e) {
            $db->rollback();
            setFlashMessage('danger', 'Failed to process request: ' . $e->getMessage());
        }
        
        header("Location: borrow.php");
        exit();
    }
}

// Fetch requests
$sql = "
    SELECT br.*, i.name as item_name, i.asset_tag, u.full_name as requested_by 
    FROM borrow_requests br
    JOIN inventory_items i ON br.item_id = i.id
    JOIN users u ON br.user_id = u.id
";
if ($role == 3) {
    // Staff only sees their own requests
    $sql .= " WHERE br.user_id = $userId";
}
$sql .= " ORDER BY br.request_date DESC";
$requests = $db->query($sql);

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1>Borrow Requests</h1>
    <p>Manage equipment borrowing workflows</p>
  </div>
  <div class="page-header-actions">
    <a href="#" class="btn btn-primary" data-modal-target="modal-request"><?= icon_plus() ?> New Request</a>
  </div>
</div>

<div class="table-wrap">
  <div class="table-toolbar">
    <span class="section-title">All Requests</span>
  </div>
  <div class="table-overflow">
    <table>
      <thead>
        <tr>
          <th>Item</th>
          <?php if($role != 3): ?><th>Requested By</th><?php endif; ?>
          <th>Qty</th>
          <th>Needed Until</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($requests && $requests->num_rows > 0): while($r = $requests->fetch_assoc()): ?>
        <tr>
          <td>
            <div class="font-semibold"><?= clean($r['item_name']) ?></div>
            <div class="text-xs text-muted"><?= clean($r['asset_tag']) ?></div>
          </td>
          <?php if($role != 3): ?><td><?= clean($r['requested_by']) ?></td><?php endif; ?>
          <td><?= $r['quantity'] ?></td>
          <td><?= date('M d, Y', strtotime($r['expected_return_date'])) ?></td>
          <td><?= getStatusBadge($r['status']) ?></td>
          <td>
            <?php if ($role != 3 && $r['status'] == 'pending'): ?>
            <form method="POST" action="" class="d-flex gap-2" style="margin:0;">
              <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
              <button type="submit" name="action" value="approve" class="btn btn-outline btn-xs text-success" title="Approve" onclick="return confirm('Approve this request?')"><?= icon_check() ?> Approve</button>
              <button type="submit" name="action" value="reject" class="btn btn-outline btn-xs text-danger" title="Reject" onclick="return confirm('Reject this request?')"><?= icon_x() ?> Reject</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="<?= $role!=3 ? 6 : 5 ?>" class="table-empty">No borrow requests found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: New Request -->
<div class="modal-overlay" id="modal-request">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">New Borrow Request</div>
      <button class="modal-close" data-modal-close>&times;</button>
    </div>
    <form method="POST" action="process_borrow.php">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label required">Item</label>
          <select name="item_id" class="form-select" required>
            <option value="">Select Item...</option>
            <?php
            $avail = $db->query("SELECT id, name, asset_tag FROM inventory_items WHERE status='available' AND quantity > 0");
            while($i = $avail->fetch_assoc()) {
                echo "<option value='{$i['id']}'>".clean($i['name'])." (".clean($i['asset_tag']).")</option>";
            }
            ?>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label required">Quantity</label>
            <input type="number" name="quantity" class="form-control" min="1" value="1" required>
          </div>
          <div class="form-group">
            <label class="form-label required">Expected Return Date</label>
            <input type="date" name="return_date" class="form-control" required min="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="form-group mb-0">
          <label class="form-label required">Purpose</label>
          <textarea name="purpose" class="form-control" rows="3" required placeholder="Reason for borrowing..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">Submit Request</button>
      </div>
    </form>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
