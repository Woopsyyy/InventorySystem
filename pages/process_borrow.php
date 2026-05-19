<?php
// pages/process_borrow.php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $userId = $_SESSION['user_id'];
    
    $item_id = (int)$_POST['item_id'];
    $quantity = (int)$_POST['quantity'];
    $return_date = $_POST['return_date'];
    $purpose = trim($_POST['purpose']);
    
    if ($item_id && $quantity > 0 && $return_date && $purpose) {
        // Verify item availability
        $stmt = $db->prepare("SELECT quantity FROM inventory_items WHERE id = ? AND status = 'available'");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($row = $res->fetch_assoc()) {
            if ($row['quantity'] >= $quantity) {
                // Insert request
                $insert = $db->prepare("INSERT INTO borrow_requests (user_id, item_id, quantity, purpose, expected_return_date) VALUES (?, ?, ?, ?, ?)");
                $insert->bind_param("iiiss", $userId, $item_id, $quantity, $purpose, $return_date);
                if ($insert->execute()) {
                    logActivity($db, $userId, 'Created Borrow Request', 'BorrowRequest', $insert->insert_id);
                    setFlashMessage('success', 'Borrow request submitted successfully. Awaiting approval.');
                } else {
                    setFlashMessage('danger', 'Failed to submit request. Please try again.');
                }
            } else {
                setFlashMessage('warning', 'Requested quantity exceeds available stock.');
            }
        } else {
            setFlashMessage('danger', 'Item is not available.');
        }
    } else {
        setFlashMessage('danger', 'Please fill in all required fields.');
    }
}

header("Location: borrow.php");
exit();
