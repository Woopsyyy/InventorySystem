<?php
// pages/categories.php
$pageTitle = 'Categories';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requireLogin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    if (hasPermission([1, 2])) {
        $name = trim($_POST['name']);
        $desc = trim($_POST['description']);
        if ($name) {
            $stmt = $db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $desc);
            if ($stmt->execute()) {
                logActivity($db, $_SESSION['user_id'], 'Added Category', 'Category', $stmt->insert_id, "Added $name");
                setFlashMessage('success', 'Category added successfully.');
                header("Location: categories.php");
                exit();
            } else {
                setFlashMessage('danger', 'Failed to add category: ' . $db->error);
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    if (hasPermission([1, 2])) {
        $delete_id = (int)$_POST['category_id'];
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            logActivity($db, $_SESSION['user_id'], 'Deleted Category', 'Category', $delete_id, "Deleted category ID $delete_id");
            setFlashMessage('success', 'Category deleted successfully.');
        } else {
            setFlashMessage('danger', 'Cannot delete category in use.');
        }
        header("Location: categories.php");
        exit();
    }
}

$categories = $db->query("
    SELECT c.*, COUNT(i.id) as item_count 
    FROM categories c 
    LEFT JOIN inventory_items i ON c.id = i.category_id 
    GROUP BY c.id 
    ORDER BY c.name ASC
");

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1>Categories</h1>
    <p>Manage inventory item classifications</p>
  </div>
  <div class="page-header-actions">
    <?php if(hasPermission([1,2])): ?>
    <button class="btn btn-primary" data-modal-target="modal-add-category"><?= icon_plus() ?> Add Category</button>
    <?php endif; ?>
  </div>
</div>

<div class="table-wrap">
  <div class="table-overflow">
    <table>
      <thead>
        <tr>
          <th>Category Name</th>
          <th>Description</th>
          <th>Items Count</th>
          <?php if(hasPermission([1,2])): ?><th>Actions</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if ($categories->num_rows > 0): while($c = $categories->fetch_assoc()): ?>
        <tr>
          <td class="font-semibold"><?= clean($c['name']) ?></td>
          <td class="text-muted text-sm"><?= clean($c['description']) ?></td>
          <td>
            <span class="badge badge-secondary"><?= $c['item_count'] ?> items</span>
          </td>
          <?php if(hasPermission([1,2])): ?>
          <td>
            <div class="d-flex gap-2">
            <form method="POST" action="" style="margin:0;display:inline-block;">
              <input type="hidden" name="action" value="delete_category">
              <input type="hidden" name="category_id" value="<?= $c['id'] ?>">
              <button type="submit" class="btn btn-outline btn-xs text-danger" onclick="return confirm('Delete this category?')"><?= icon_trash() ?></button>
            </form>
            </div>
          </td>
          <?php endif; ?>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="4" class="table-empty">No categories found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Category Modal -->
<div class="modal-overlay" id="modal-add-category">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Add Category</div>
      <button class="modal-close" data-modal-close>&times;</button>
    </div>
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_category">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label required">Category Name</label>
          <input type="text" name="name" class="form-control" required autofocus>
        </div>
        <div class="form-group mb-0">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">Save Category</button>
      </div>
    </form>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
