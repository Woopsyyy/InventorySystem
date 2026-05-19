<?php
// includes/auth.php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
}

function getUserRole() {
    return $_SESSION['role_id'] ?? null;
}

function hasPermission($allowed_roles = []) {
    $role = getUserRole();
    return in_array($role, $allowed_roles);
}

function requirePermission($allowed_roles = []) {
    requireLogin();
    if (!hasPermission($allowed_roles)) {
        header("Location: " . BASE_URL . "pages/unauthorized.php");
        exit();
    }
}
