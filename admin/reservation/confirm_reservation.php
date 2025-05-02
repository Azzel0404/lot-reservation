<?php
session_start();
include('../config/db.php');

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lot_id'])) {
    $lot_id = intval($_POST['lot_id']);

    // Update the lot status to 'Reserved'
    $stmt = $conn->prepare("UPDATE lot SET status = 'Reserved' WHERE lot_id = ?");
    $stmt->bind_param("i", $lot_id);
    $success = $stmt->execute();
    $stmt->close();

    // Optional: Log to reservations table
    // $user_id = $_SESSION['user_id']; // If you have user logins
    // $stmt = $conn->prepare("INSERT INTO reservations (lot_id, user_id, reserved_at) VALUES (?, ?, NOW())");
    // $stmt->bind_param("ii", $lot_id, $user_id);
    // $stmt->execute();
    // $stmt->close();

    // Return JSON response
    echo json_encode(['success' => $success]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
