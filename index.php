<?php
// index.php
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Please enter username and password.";
    } else {
        $stmt = $db->prepare("
            SELECT u.id, u.password, u.full_name, u.role_id, r.name as role_name, u.status 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.username = ?
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if ($user['status'] !== 'active') {
                $error = "Account is inactive. Please contact administrator.";
            } elseif (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['role_name'] = $user['role_name'];
                
                // Update last login
                $update_stmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                
                logActivity($db, $user['id'], 'Login', 'System');
                
                header("Location: " . BASE_URL . "dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - TCC Inventory</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="login-page">

<div class="login-box">
  <div class="login-logo">
    <div class="login-logo-icon">TI</div>
    <div class="login-logo-text">TCC Inventory</div>
  </div>
  
  <h1 class="login-title">Welcome back</h1>
  <p class="login-sub">Please enter your details to sign in.</p>
  
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= clean($error) ?></div>
  <?php endif; ?>
  
  <?php $flash = getFlashMessage(); if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= clean($flash['message']) ?></div>
  <?php endif; ?>
  
  <form method="POST" action="">
    <div class="form-group">
      <label class="form-label required">Username</label>
      <input type="text" name="username" class="form-control" placeholder="Enter your username" required autofocus>
    </div>
    
    <div class="form-group">
      <div style="display:flex; justify-content:space-between;">
        <label class="form-label required">Password</label>
      </div>
      <input type="password" name="password" class="form-control" placeholder="••••••••" required>
    </div>
    
    <button type="submit" class="btn btn-primary w-full mt-4" style="justify-content:center; padding:10px;">
      Sign In
    </button>
  </form>
  
  <div class="text-center mt-6 text-sm text-muted">
    Default credentials:<br>
    Admin: <b>admin</b> / <b>password</b><br>
    Manager: <b>manager</b> / <b>password</b><br>
    Staff: <b>staff</b> / <b>password</b>
  </div>
</div>

</body>
</html>
