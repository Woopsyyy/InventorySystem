<?php
// includes/helpers.php

function clean($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function getConditionBadge($condition) {
    switch ($condition) {
        case 'new': return '<span class="badge badge-success">New</span>';
        case 'good': return '<span class="badge badge-info">Good</span>';
        case 'fair': return '<span class="badge badge-warning">Fair</span>';
        case 'poor': return '<span class="badge badge-danger">Poor</span>';
        case 'damaged': return '<span class="badge badge-dark">Damaged</span>';
        default: return '<span class="badge badge-secondary">Unknown</span>';
    }
}

function getStatusBadge($status) {
    switch ($status) {
        case 'available': return '<span class="badge badge-success">Available</span>';
        case 'in_use': return '<span class="badge badge-primary">In Use</span>';
        case 'maintenance': return '<span class="badge badge-warning">Maintenance</span>';
        case 'retired': return '<span class="badge badge-dark">Retired</span>';
        case 'pending': return '<span class="badge badge-warning">Pending</span>';
        case 'approved': return '<span class="badge badge-success">Approved</span>';
        case 'rejected': return '<span class="badge badge-danger">Rejected</span>';
        case 'returned': return '<span class="badge badge-info">Returned</span>';
        case 'overdue': return '<span class="badge badge-danger">Overdue</span>';
        case 'active': return '<span class="badge badge-success">Active</span>';
        case 'inactive': return '<span class="badge badge-secondary">Inactive</span>';
        default: return '<span class="badge badge-secondary">' . ucfirst(clean($status)) . '</span>';
    }
}

function logActivity($db, $user_id, $action, $entity_type, $entity_id = null, $details = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ississ", $user_id, $action, $entity_type, $entity_id, $details, $ip);
        $stmt->execute();
        $stmt->close();
    }
}
