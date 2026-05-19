<?php
// pages/returns.php
$pageTitle = 'Returns';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requireLogin();
$db = getDB();
$role = getUserRole();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_return') {
    if (hasPermission([1, 2])) {
        $history_id = (int)$_POST['history_id'];
        $condition_returned = trim($_POST['condition_returned']);
        
        $db->begin_transaction();
        try {
            // Get borrow details
            $borrow = $db->query("SELECT br.item_id, br.quantity FROM borrow_history bh JOIN borrow_requests br ON bh.request_id = br.id WHERE bh.id = $history_id")->fetch_assoc();
            
            if ($borrow) {
                // Update return date and condition
                $stmt = $db->prepare("UPDATE borrow_history SET return_date = CURRENT_TIMESTAMP, condition_returned = ? WHERE id = ?");
                $stmt->bind_param("si", $condition_returned, $history_id);
                $stmt->execute();
                
                // Return stock to inventory
                $stmt2 = $db->prepare("UPDATE inventory_items SET quantity = quantity + ? WHERE id = ?");
                $stmt2->bind_param("ii", $borrow['quantity'], $borrow['item_id']);
                $stmt2->execute();
                
                // If condition is damaged/poor, optionally log it or update item status
                if ($condition_returned == 'damaged') {
                    $db->query("UPDATE inventory_items SET condition_status = 'damaged' WHERE id = " . $borrow['item_id']);
                }
                
                $db->commit();
                logActivity($db, $userId, 'Processed Return', 'BorrowHistory', $history_id, "Returned item in $condition_returned condition");
                setFlashMessage('success', 'Return processed successfully.');
                header("Location: returns.php");
                exit();
            }
        } catch (Exception $e) {
            $db->rollback();
            setFlashMessage('danger', 'Failed to process return: ' . $e->getMessage());
        }
    }
}

// Approved requests that have been borrowed but not returned yet.
$sql = "
    SELECT bh.id as history_id, br.id as request_id, br.quantity, br.expected_return_date,
           i.name as item_name, u.full_name as borrowed_by, bh.borrow_date
    FROM borrow_history bh
    JOIN borrow_requests br ON bh.request_id = br.id
    JOIN inventory_items i ON br.item_id = i.id
    JOIN users u ON br.user_id = u.id
    WHERE bh.return_date IS NULL
";

if ($role == 3) {
    $sql .= " AND br.user_id = $userId";
}

$sql .= " ORDER BY br.expected_return_date ASC";
$borrowed = $db->query($sql);

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1>Process Returns</h1>
    <p>Manage items that are currently checked out</p>
  </div>
</div>

<div class="table-wrap">
  <div class="table-overflow">
    <table>
      <thead>
        <tr>
          <th>Item</th>
          <?php if($role != 3): ?><th>Borrowed By</th><?php endif; ?>
          <th>Qty</th>
          <th>Borrow Date</th>
          <th>Due Date</th>
          <th>Status</th>
          <?php if($role != 3): ?><th>Actions</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if ($borrowed && $borrowed->num_rows > 0): while($b = $borrowed->fetch_assoc()): 
          $isOverdue = strtotime($b['expected_return_date']) < strtotime(date('Y-m-d'));
        ?>
        <tr>
          <td class="font-semibold"><?= clean($b['item_name']) ?></td>
          <?php if($role != 3): ?><td><?= clean($b['borrowed_by']) ?></td><?php endif; ?>
          <td><?= $b['quantity'] ?></td>
          <td class="text-sm text-muted"><?= date('M d, Y', strtotime($b['borrow_date'])) ?></td>
          <td class="text-sm <?= $isOverdue ? 'text-danger font-semibold' : 'text-muted' ?>">
            <?= date('M d, Y', strtotime($b['expected_return_date'])) ?>
          </td>
          <td>
            <?php if($isOverdue): ?>
              <span class="badge badge-danger">Overdue</span>
            <?php else: ?>
              <span class="badge badge-primary">Checked Out</span>
            <?php endif; ?>
          </td>
          <?php if($role != 3): ?>
          <td>
            <button class="btn btn-outline btn-xs" onclick="openReturnModal(<?= $b['history_id'] ?>, '<?= addslashes($b['item_name']) ?>')">Process Return</button>
          </td>
          <?php endif; ?>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="<?= $role!=3 ? 7 : 5 ?>" class="table-empty">No items currently checked out.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Process Return Modal -->
<div class="modal-overlay" id="modal-process-return">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Process Item Return</div>
      <button class="modal-close" data-modal-close>&times;</button>
    </div>
    <form method="POST" action="">
      <input type="hidden" name="action" value="process_return">
      <input type="hidden" name="history_id" id="return_history_id" value="">
      <div class="modal-body">
        <p class="mb-3">Returning: <strong id="return_item_name"></strong></p>
        <div class="form-group mb-0">
          <label class="form-label required">Condition Returned</label>
          <select name="condition_returned" class="form-select" required>
            <option value="good">Good (No new damage)</option>
            <option value="fair">Fair (Normal wear and tear)</option>
            <option value="poor">Poor (Requires maintenance)</option>
            <option value="damaged">Damaged (Broken/Unusable)</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">Complete Return</button>
      </div>
    </form>
  </div>
</div>

<script>
function openReturnModal(id, name) {
    document.getElementById('return_history_id').value = id;
    document.getElementById('return_item_name').innerText = name;
    
    const overlay = document.getElementById('modal-process-return');
    overlay.classList.add('active');
}
</script>

<?php require_once '../includes/footer.php'; ?>
