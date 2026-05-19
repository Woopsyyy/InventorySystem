<?php
// includes/header.php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

requireLogin();

$db = getDB();
$userId = $_SESSION['user_id'];
$fullName = clean($_SESSION['full_name'] ?? 'User');
$roleId = $_SESSION['role_id'] ?? 3;
$roleName = $_SESSION['role_name'] ?? 'Staff';
$initials = strtoupper(substr($fullName, 0, 1));

// Unread notifications count
$notifResult = $db->query("SELECT COUNT(*) FROM notifications WHERE user_id = $userId AND is_read = 0");
$notifCount = $notifResult ? $notifResult->fetch_row()[0] : 0;

// Flash message
$flash = getFlashMessage();

// Determine current page for active nav state
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

function navLink($href, $label, $icon, $currentPage, $currentDir, $page, $dir = '') {
    $active = ($currentPage === $page || (!empty($dir) && $currentDir === $dir)) ? 'active' : '';
    echo "<a href=\"$href\" class=\"$active\">$icon $label</a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="TCC Inventory System - Manage and track school assets">
  <title><?= clean($pageTitle ?? 'Dashboard') ?> &mdash; TCC Inventory</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=1.0.2">
</head>
<body>
<div class="app-layout">

  <!-- ===== SIDEBAR ===== -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">TI</div>
      TCC Inventory
    </div>

    <nav class="sidebar-nav">
      <div class="sidebar-section">Main</div>
      <a href="<?= BASE_URL ?>dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
        <?= icon_grid() ?> Dashboard
      </a>

      <div class="sidebar-section">Inventory</div>
      <a href="<?= BASE_URL ?>pages/items.php" class="<?= $currentPage === 'items.php' ? 'active' : '' ?>">
        <?= icon_box() ?> Inventory Items
      </a>
      <a href="<?= BASE_URL ?>pages/categories.php" class="<?= $currentPage === 'categories.php' ? 'active' : '' ?>">
        <?= icon_tag() ?> Categories
      </a>
      <a href="<?= BASE_URL ?>pages/suppliers.php" class="<?= $currentPage === 'suppliers.php' ? 'active' : '' ?>">
        <?= icon_truck() ?> Suppliers
      </a>
      <a href="<?= BASE_URL ?>pages/stock_movements.php" class="<?= $currentPage === 'stock_movements.php' ? 'active' : '' ?>">
        <?= icon_arrows() ?> Stock Movements
      </a>
      <a href="<?= BASE_URL ?>pages/low_stock.php" class="<?= $currentPage === 'low_stock.php' ? 'active' : '' ?>">
        <?= icon_alert() ?> Low Stock
      </a>

      <div class="sidebar-section">Borrowing</div>
      <a href="<?= BASE_URL ?>pages/borrow.php" class="<?= $currentPage === 'borrow.php' ? 'active' : '' ?>">
        <?= icon_clipboard() ?> Borrow Requests
      </a>
      <a href="<?= BASE_URL ?>pages/returns.php" class="<?= $currentPage === 'returns.php' ? 'active' : '' ?>">
        <?= icon_return() ?> Returns
      </a>

      <div class="sidebar-section">Reports</div>
      <a href="<?= BASE_URL ?>pages/reports.php" class="<?= $currentPage === 'reports.php' ? 'active' : '' ?>">
        <?= icon_chart() ?> Reports
      </a>
      <a href="<?= BASE_URL ?>pages/damaged.php" class="<?= $currentPage === 'damaged.php' ? 'active' : '' ?>">
        <?= icon_warning() ?> Damaged Items
      </a>
      <a href="<?= BASE_URL ?>pages/activity_logs.php" class="<?= $currentPage === 'activity_logs.php' ? 'active' : '' ?>">
        <?= icon_log() ?> Audit Logs
      </a>

      <?php if ($roleId == 1): ?>
      <div class="sidebar-section">Admin</div>
      <a href="<?= BASE_URL ?>pages/users.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">
        <?= icon_users() ?> Users
      </a>
      <a href="<?= BASE_URL ?>pages/departments.php" class="<?= $currentPage === 'departments.php' ? 'active' : '' ?>">
        <?= icon_office() ?> Departments
      </a>
      <a href="<?= BASE_URL ?>pages/settings.php" class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">
        <?= icon_cog() ?> Settings
      </a>
      <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user-name"><?= $fullName ?></div>
      <div class="sidebar-user-role"><?= clean($roleName) ?></div>
      <a href="<?= BASE_URL ?>logout.php" class="btn btn-outline btn-sm mt-2" style="justify-content:center;">
        <?= icon_logout() ?> Sign Out
      </a>
    </div>
  </aside>

  <!-- ===== MAIN WRAPPER ===== -->
  <div class="main-wrapper">

    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
        <button id="sidebarToggle" style="display:none; background:none; border:none; cursor:pointer; padding:4px;" aria-label="Toggle menu">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <span class="topbar-title"><?= clean($pageTitle ?? 'Dashboard') ?></span>
      </div>
      <div class="topbar-right">
        <div class="topbar-notif" title="Notifications" onclick="window.location='<?= BASE_URL ?>pages/notifications.php'">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <?php if ($notifCount > 0): ?>
          <span class="notif-dot"></span>
          <?php endif; ?>
        </div>
        <div class="topbar-avatar" title="<?= $fullName ?>"><?= $initials ?></div>
      </div>
    </header>

    <!-- Page Content -->
    <main class="page-content">

      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>" id="flash-msg">
        <?= clean($flash['message']) ?>
      </div>
      <?php endif; ?>
<?php

// Inline SVG icon helpers — tiny single-file icons
function icon_grid()     { return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>'; }
function icon_box()      { return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>'; }
function icon_tag()      { return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>'; }
function icon_truck()    { return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>'; }
function icon_arrows()   { return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>'; }
function icon_alert()    { return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'; }
function icon_clipboard(){ return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>'; }
function icon_return()   { return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.36"/></svg>'; }
function icon_chart()    { return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>'; }
function icon_warning()  { return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'; }
function icon_log()      { return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>'; }
function icon_users()    { return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>'; }
function icon_office()   { return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>'; }
function icon_cog()      { return '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>'; }
function icon_logout()   { return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>'; }
function icon_plus()     { return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>'; }
function icon_edit()     { return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>'; }
function icon_trash()    { return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>'; }
function icon_eye()      { return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>'; }
function icon_check()    { return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>'; }
function icon_x()        { return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'; }
function icon_download() { return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>'; }
function icon_search()   { return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>'; }
function icon_filter()   { return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>'; }
