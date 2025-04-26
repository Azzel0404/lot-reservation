<!--admin/add_lot_action.php-->

<?php
session_start();
include('../config/db.php');

// Helper: redirect with message
function redirectWithMessage($type, $message) {
    header("Location: lots.php?$type=" . urlencode($message));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $lot_number = $_POST['lot_number'];
    $location = $_POST['location'];
    $size = $_POST['size'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $aerial_path = '';
    $numbered_path = '';

    // Aerial image
    if (!empty($_FILES['aerial_image']['name'])) {
        $aerial_filename = time() . '_aerial_' . basename($_FILES['aerial_image']['name']);
        $aerial_path = '../admin/images/' . $aerial_filename;
        if (!move_uploaded_file($_FILES['aerial_image']['tmp_name'], $aerial_path)) {
            redirectWithMessage('error', 'Failed to upload aerial image');
        }
    }

    // Numbered image
    if (!empty($_FILES['numbered_image']['name'])) {
        $numbered_filename = time() . '_numbered_' . basename($_FILES['numbered_image']['name']);
        $numbered_path = '../admin/images/' . $numbered_filename;
        if (!move_uploaded_file($_FILES['numbered_image']['tmp_name'], $numbered_path)) {
            redirectWithMessage('error', 'Failed to upload numbered image');
        }
    }

    if (empty($aerial_path) || empty($numbered_path)) {
        redirectWithMessage('error', 'One or both images were not uploaded.');
    }

    $aerial_db_path = 'admin/images/' . basename($aerial_path);
    $numbered_db_path = 'admin/images/' . basename($numbered_path);

    $stmt = $conn->prepare("INSERT INTO lot (lot_number, location, size_meter_square, price, status, aerial_image, numbered_image)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddsss", $lot_number, $location, $size, $price, $status, $aerial_db_path, $numbered_db_path);

    if ($stmt->execute()) {
        redirectWithMessage('success', 'Lot added successfully.');
    } else {
        redirectWithMessage('error', 'Database error: ' . $stmt->error);
    }
} else {
    redirectWithMessage('error', 'Invalid request');
}
 