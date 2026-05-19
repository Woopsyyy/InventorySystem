<?php
// pages/items.php
$pageTitle = 'Inventory Items';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requireLogin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_item') {
    if (hasPermission([1, 2])) {
        $delete_id = (int)$_POST['item_id'];
        // Note: foreign keys (e.g., borrow history) might prevent deletion if constrained without CASCADE or SET NULL
        $stmt = $db->prepare("DELETE FROM inventory_items WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            logActivity($db, $_SESSION['user_id'], 'Deleted Item', 'InventoryItem', $delete_id, "Deleted item ID: $delete_id");
            setFlashMessage('success', 'Item deleted successfully.');
        } else {
            setFlashMessage('danger', 'Failed to delete item. It may be referenced by existing transactions.');
        }
        header("Location: items.php");
        exit();
    }
}

// Handle search & filter
$search = $_GET['search'] ?? '';
$cat_filter = $_GET['category'] ?? '';

$where = ["1=1"];
$params = [];
$types = "";

if ($search) {
    $where[] = "(i.name LIKE ? OR i.asset_tag LIKE ? OR i.serial_number LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sss";
}
if ($cat_filter) {
    $where[] = "i.category_id = ?";
    $params[] = $cat_filter;
    $types .= "i";
}

$where_clause = implode(" AND ", $where);
$sql = "
    SELECT i.*, c.name as category_name, d.name as department_name
    FROM inventory_items i
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN departments d ON i.department_id = d.id
    WHERE $where_clause
    ORDER BY i.name ASC
";

$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$items = $stmt->get_result();

// Categories for filter
$categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC");

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1>Inventory Items</h1>
    <p>Manage all assets and consumables</p>
  </div>
  <div class="page-header-actions">
    <?php if (hasPermission([1, 2])): // Admin, Manager ?>
    <a href="reports.php" class="btn btn-outline"><?= icon_download() ?> Export</a>
    <a href="item_add.php" class="btn btn-primary"><?= icon_plus() ?> Add Item</a>
    <?php endif; ?>
  </div>
</div>

<div class="table-wrap">
  <div class="table-toolbar">
    <form class="search-wrap" method="GET" action="">
      <div class="input-icon-wrap">
        <?= icon_search() ?>
        <input type="text" name="search" class="form-control" placeholder="Search items, tags..." value="<?= clean($search) ?>">
      </div>
      <select name="category" class="form-select" style="width:180px;" onchange="this.form.submit()">
        <option value="">All Categories</option>
        <?php while($c = $categories->fetch_assoc()): ?>
        <option value="<?= $c['id'] ?>" <?= $cat_filter == $c['id'] ? 'selected' : '' ?>><?= clean($c['name']) ?></option>
        <?php endwhile; ?>
      </select>
      <?php if($search || $cat_filter): ?>
        <a href="items.php" class="btn btn-outline btn-sm">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="table-overflow">
    <table>
      <thead>
        <tr>
          <th>Asset Details</th>
          <th>Category</th>
          <th>Location</th>
          <th>Quantity</th>
          <th>Status</th>
          <?php if (hasPermission([1, 2])): ?>
          <th>Actions</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if ($items->num_rows > 0): while($i = $items->fetch_assoc()): ?>
        <tr>
          <td>
            <div class="font-semibold"><?= clean($i['name']) ?></div>
            <?php if($i['asset_tag']): ?>
              <div class="text-xs text-muted">Tag: <?= clean($i['asset_tag']) ?></div>
            <?php endif; ?>
          </td>
          <td><?= clean($i['category_name']) ?></td>
          <td>
            <div><?= clean($i['location'] ?: 'Unassigned') ?></div>
            <div class="text-xs text-muted"><?= clean($i['department_name']) ?></div>
          </td>
          <td>
            <?php if($i['quantity'] <= $i['reorder_level']): ?>
              <span class="qty-low"><?= $i['quantity'] ?> <?= clean($i['unit']) ?></span>
            <?php else: ?>
              <span><?= $i['quantity'] ?> <?= clean($i['unit']) ?></span>
            <?php endif; ?>
          </td>
          <td>
            <?= getStatusBadge($i['status']) ?><br>
            <?= getConditionBadge($i['condition_status']) ?>
          </td>
          <?php if (hasPermission([1, 2])): ?>
          <td>
            <div class="d-flex gap-2">
              <a href="item_edit.php?id=<?= $i['id'] ?>" class="btn btn-outline btn-xs" title="Edit"><?= icon_edit() ?></a>
              <form method="POST" action="" style="margin:0;display:inline-block;">
                <input type="hidden" name="action" value="delete_item">
                <input type="hidden" name="item_id" value="<?= $i['id'] ?>">
                <button type="submit" class="btn btn-outline btn-xs text-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this item? This action cannot be undone.')"><?= icon_trash() ?></button>
              </form>
            </div>
          </td>
          <?php endif; ?>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6" class="table-empty">No items found matching your criteria.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
