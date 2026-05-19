<?php
// logout.php
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

if (isset($_SESSION['user_id'])) {
    $db = getDB();
    logActivity($db, $_SESSION['user_id'], 'Logout', 'System');
}

$_SESSION = [];
session_destroy();

// Start a new session for flash message
session_start();
$_SESSION['flash'] = [
    'type' => 'success',
    'message' => 'You have been successfully logged out.'
];

header("Location: " . BASE_URL . "index.php");
exit();
