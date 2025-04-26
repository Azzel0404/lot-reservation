<?php
session_start();
include('../config/db.php');

// Helper: redirect with message
function redirectWithMessage($type, $message) {
    header("Location: lots.php?$type=" . urlencode($message));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $lot_id = $_POST['lot_id'];
    $lot_number = $_POST['lot_number'];
    $location = $_POST['location'];
    $size = $_POST['size'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    // Prepare the SQL query for updating the lot
    $stmt = $conn->prepare("UPDATE lot SET lot_number = ?, location = ?, size_meter_square = ?, price = ?, status = ? WHERE lot_id = ?");
    $stmt->bind_param("ssddsi", $lot_number, $location, $size, $price, $status, $lot_id);

    if ($stmt->execute()) {
        redirectWithMessage('success', 'Lot updated successfully.');
    } else {
        redirectWithMessage('error', 'Failed to update the lot: ' . $stmt->error);
    }
} else {
    redirectWithMessage('error', 'Invalid request.');
}
?>
