<?php
session_start();
include('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$lot_id = intval($_POST['lot_id']);
$payment_method = $_POST['payment_method'];
$reservation_fee = floatval($_POST['reservation_fee']);
$client_id = $_SESSION['client_id'] ?? null; // Assuming you have client login system

if (!$client_id) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to make a reservation']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if lot is still available
    $lot_check = $conn->prepare("SELECT status FROM lot WHERE lot_id = ? FOR UPDATE");
    $lot_check->bind_param("i", $lot_id);
    $lot_check->execute();
    $lot_result = $lot_check->get_result();
    $lot = $lot_result->fetch_assoc();
    
    if (!$lot || $lot['status'] !== 'Available') {
        throw new Exception("This lot is no longer available for reservation.");
    }

    // Get or create payment method
    $payment_query = $conn->prepare("SELECT payment_id FROM payment WHERE payment_method = ?");
    $payment_query->bind_param("s", $payment_method);
    $payment_query->execute();
    $payment_result = $payment_query->get_result();
    
    if ($payment_result->num_rows === 0) {
        // Insert new payment method if not exists
        $insert_payment = $conn->prepare("INSERT INTO payment (payment_method) VALUES (?)");
        $insert_payment->bind_param("s", $payment_method);
        $insert_payment->execute();
        $payment_id = $conn->insert_id;
    } else {
        $payment_row = $payment_result->fetch_assoc();
        $payment_id = $payment_row['payment_id'];
    }

    // Create reservation
    $reservation_date = date('Y-m-d H:i:s');
    $status = 'Pending'; // Initial status
    
    $stmt = $conn->prepare("INSERT INTO reservation (client_id, lot_id, payment_id, reservation_fee, status, reservation_date) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiidss", $client_id, $lot_id, $payment_id, $reservation_fee, $status, $reservation_date);
    $stmt->execute();
    
    // Update lot status to Reserved
    $update_lot = $conn->prepare("UPDATE lot SET status = 'Reserved' WHERE lot_id = ?");
    $update_lot->bind_param("i", $lot_id);
    $update_lot->execute();
    
    $conn->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>