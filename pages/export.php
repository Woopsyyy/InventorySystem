<?php
// pages/export.php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requirePermission([1, 2]); // Admin & Manager only
$db = getDB();

$type = $_GET['type'] ?? 'inventory';
$filename = $type . "_export_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

if ($type === 'inventory') {
    fputcsv($output, ['ID', 'Asset Tag', 'Name', 'Category', 'Quantity', 'Unit', 'Unit Price', 'Total Value', 'Condition', 'Status']);
    $res = $db->query("SELECT i.id, i.asset_tag, i.name, c.name as category, i.quantity, i.unit, i.unit_price, (i.quantity * i.unit_price) as total_value, i.condition_status, i.status FROM inventory_items i LEFT JOIN categories c ON i.category_id = c.id");
    while($row = $res->fetch_assoc()) fputcsv($output, $row);

} elseif ($type === 'movements') {
    fputcsv($output, ['ID', 'Item Name', 'Type', 'Quantity', 'Ref No', 'Date', 'Performed By']);
    $res = $db->query("SELECT s.id, i.name, s.movement_type, s.quantity, s.reference_number, s.movement_date, u.full_name FROM stock_movements s JOIN inventory_items i ON s.item_id = i.id JOIN users u ON s.performed_by = u.id");
    while($row = $res->fetch_assoc()) fputcsv($output, $row);

} elseif ($type === 'borrowing') {
    fputcsv($output, ['Request ID', 'Item', 'Quantity', 'Requested By', 'Status', 'Borrow Date', 'Return Date']);
    $res = $db->query("SELECT br.id, i.name, br.quantity, u.full_name, br.status, bh.borrow_date, bh.return_date FROM borrow_requests br JOIN inventory_items i ON br.item_id = i.id JOIN users u ON br.user_id = u.id LEFT JOIN borrow_history bh ON br.id = bh.request_id");
    while($row = $res->fetch_assoc()) fputcsv($output, $row);

} elseif ($type === 'damaged') {
    fputcsv($output, ['Report ID', 'Item Name', 'Asset Tag', 'Reported By', 'Report Date', 'Status', 'Repair Cost', 'Description']);
    $res = $db->query("SELECT d.id, i.name, i.asset_tag, u.full_name, d.report_date, d.status, d.repair_cost, d.description FROM damaged_items d JOIN inventory_items i ON d.item_id = i.id JOIN users u ON d.reported_by = u.id");
    while($row = $res->fetch_assoc()) fputcsv($output, $row);
}

fclose($output);
exit();
