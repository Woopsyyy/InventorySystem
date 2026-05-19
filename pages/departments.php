<?php
// pages/departments.php
$pageTitle = 'Departments';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requirePermission([1]); // Admin only
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_department') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    if ($name) {
        $stmt = $db->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $desc);
        if ($stmt->execute()) {
            logActivity($db, $_SESSION['user_id'], 'Added Department', 'Department', $stmt->insert_id, "Added $name");
            setFlashMessage('success', 'Department added successfully.');
            header("Location: departments.php");
            exit();
        } else {
            setFlashMessage('danger', 'Failed to add department: ' . $db->error);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_department') {
    $delete_id = (int)$_POST['department_id'];
    $stmt = $db->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        logActivity($db, $_SESSION['user_id'], 'Deleted Department', 'Department', $delete_id, "Deleted department ID $delete_id");
        setFlashMessage('success', 'Department deleted successfully.');
    } else {
        setFlashMessage('danger', 'Cannot delete department in use.');
    }
    header("Location: departments.php");
    exit();
}

$departments = $db->query("
    SELECT d.*, COUNT(i.id) as item_count 
    FROM departments d 
    LEFT JOIN inventory_items i ON d.id = i.department_id 
    GROUP BY d.id 
    ORDER BY d.name ASC
");

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1>Departments</h1>
    <p>Manage school departments and locations</p>
  </div>
  <div class="page-header-actions">
    <button class="btn btn-primary" data-modal-target="modal-add-department"><?= icon_plus() ?> Add Department</button>
  </div>
</div>

<div class="table-wrap">
  <div class="table-overflow">
    <table>
      <thead>
        <tr>
          <th>Department Name</th>
          <th>Description</th>
          <th>Assigned Items</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($departments->num_rows > 0): while($d = $departments->fetch_assoc()): ?>
        <tr>
          <td class="font-semibold"><?= clean($d['name']) ?></td>
          <td class="text-muted text-sm"><?= clean($d['description']) ?></td>
          <td><span class="badge badge-secondary"><?= $d['item_count'] ?></span></td>
          <td>
            <div class="d-flex gap-2">
              <form method="POST" action="" style="margin:0;display:inline-block;">
                <input type="hidden" name="action" value="delete_department">
                <input type="hidden" name="department_id" value="<?= $d['id'] ?>">
                <button type="submit" class="btn btn-outline btn-xs text-danger" onclick="return confirm('Delete this department?')"><?= icon_trash() ?></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="4" class="table-empty">No departments found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Department Modal -->
<div class="modal-overlay" id="modal-add-department">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Add Department</div>
      <button class="modal-close" data-modal-close>&times;</button>
    </div>
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_department">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label required">Department Name</label>
          <input type="text" name="name" class="form-control" required autofocus>
        </div>
        <div class="form-group mb-0">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">Save Department</button>
      </div>
    </form>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
