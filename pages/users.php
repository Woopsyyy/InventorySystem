<?php
// pages/users.php
$pageTitle = 'Users';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requirePermission([1]); // Admin only
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role_id = (int)$_POST['role_id'];
    
    if ($full_name && $username && $password && $role_id) {
        // check unique
        $chk = $db->query("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
        if ($chk->num_rows > 0) {
            setFlashMessage('danger', 'Username or Email already exists.');
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (role_id, username, password, email, full_name, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->bind_param("issss", $role_id, $username, $hashed, $email, $full_name);
            
            if ($stmt->execute()) {
                logActivity($db, $_SESSION['user_id'], 'Added User', 'User', $stmt->insert_id, "Added $username");
                setFlashMessage('success', 'User created successfully.');
                header("Location: users.php");
                exit();
            } else {
                setFlashMessage('danger', 'Failed to create user: ' . $db->error);
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $delete_id = (int)$_POST['user_id'];
    if ($delete_id !== $_SESSION['user_id']) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            logActivity($db, $_SESSION['user_id'], 'Deleted User', 'User', $delete_id, "Deleted user ID $delete_id");
            setFlashMessage('success', 'User deleted successfully.');
        } else {
            setFlashMessage('danger', 'Cannot delete user. They may have associated records.');
        }
    }
    header("Location: users.php");
    exit();
}

$users = $db->query("
    SELECT u.*, r.name as role_name 
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    ORDER BY u.full_name ASC
");

require_once '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-info">
    <h1>Users</h1>
    <p>Manage system access and roles</p>
  </div>
  <div class="page-header-actions">
    <button class="btn btn-primary" data-modal-target="modal-add-user"><?= icon_plus() ?> Add User</button>
  </div>
</div>

<div class="table-wrap">
  <div class="table-overflow">
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Username</th>
          <th>Role</th>
          <th>Status</th>
          <th>Last Login</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($users->num_rows > 0): while($u = $users->fetch_assoc()): ?>
        <tr>
          <td>
            <div class="font-semibold"><?= clean($u['full_name']) ?></div>
            <div class="text-xs text-muted"><?= clean($u['email']) ?></div>
          </td>
          <td><?= clean($u['username']) ?></td>
          <td><span class="badge badge-secondary"><?= clean($u['role_name']) ?></span></td>
          <td><?= getStatusBadge($u['status']) ?></td>
          <td class="text-muted text-sm">
            <?= $u['last_login'] ? date('M d, Y H:i', strtotime($u['last_login'])) : 'Never' ?>
          </td>
          <td>
            <div class="d-flex gap-2">
              <?php if($u['id'] != $_SESSION['user_id']): ?>
              <form method="POST" action="" style="margin:0;display:inline-block;">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <button type="submit" class="btn btn-outline btn-xs text-danger" onclick="return confirm('Delete this user?')"><?= icon_trash() ?></button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6" class="table-empty">No users found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="modal-add-user">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Add New User</div>
      <button class="modal-close" data-modal-close>&times;</button>
    </div>
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_user">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label required">Full Name</label>
          <input type="text" name="full_name" class="form-control" required autofocus>
        </div>
        <div class="form-group">
          <label class="form-label required">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label required">Username</label>
            <input type="text" name="username" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label required">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
        </div>
        <div class="form-group mb-0">
          <label class="form-label required">Role</label>
          <select name="role_id" class="form-select" required>
            <?php
            $roles = $db->query("SELECT id, name FROM roles ORDER BY id ASC");
            while($r = $roles->fetch_assoc()) {
                echo "<option value='{$r['id']}'>".clean($r['name'])."</option>";
            }
            ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">Create User</button>
      </div>
    </form>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
