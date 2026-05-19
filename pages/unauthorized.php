<?php
// pages/unauthorized.php
$pageTitle = 'Access Denied';
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

// Safe to include header if logged in, otherwise redirect to index
requireLogin();

require_once '../includes/header.php';
?>

<div class="empty-state" style="margin-top: 10vh;">
  <div style="font-size:48px; color:var(--danger); opacity:1;">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
      <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
    </svg>
  </div>
  <h1 class="font-bold text-primary mt-4" style="font-size:24px;">Access Denied</h1>
  <p class="text-muted mt-2">You do not have the required permissions to view this page or perform this action.</p>
  <a href="<?= BASE_URL ?>dashboard.php" class="btn btn-primary mt-6" style="display:inline-flex;">Return to Dashboard</a>
</div>

<?php require_once '../includes/footer.php'; ?>
