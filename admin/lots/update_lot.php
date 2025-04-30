<?php
include('../../config/db.php');
session_start();

$uploadDir = 'uploads/';
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];

if (isset($_POST['update']) && is_numeric($_POST['lot_id'])) {
    $lot_id = $_POST['lot_id'];
    $lot_number = trim($_POST['lot_number']);
    $location = trim($_POST['location']);
    $size = floatval($_POST['size_meter_square']);
    $price = floatval($_POST['price']);
    $status = $_POST['status'];

    // Fetch existing lot to get old image names
    $stmt = $conn->prepare("SELECT aerial_image, numbered_image FROM lot WHERE lot_id = ?");
    $stmt->bind_param("i", $lot_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();

    $aerial_image = $existing['aerial_image'];
    $numbered_image = $existing['numbered_image'];

    // Aerial image replacement
    if (!empty($_FILES['aerial_image']['name']) && in_array($_FILES['aerial_image']['type'], $allowed_types)) {
        $aerial_image = time() . '_' . basename($_FILES['aerial_image']['name']);
        move_uploaded_file($_FILES['aerial_image']['tmp_name'], $uploadDir . $aerial_image);
    }

    // Numbered image replacement
    if (!empty($_FILES['numbered_image']['name']) && in_array($_FILES['numbered_image']['type'], $allowed_types)) {
        $numbered_image = time() . '_' . basename($_FILES['numbered_image']['name']);
        move_uploaded_file($_FILES['numbered_image']['tmp_name'], $uploadDir . $numbered_image);
    }

    // Update the lot
    $stmt = $conn->prepare("UPDATE lot SET lot_number = ?, location = ?, size_meter_square = ?, price = ?, status = ?, aerial_image = ?, numbered_image = ? WHERE lot_id = ?");
    $stmt->bind_param("ssddsssi", $lot_number, $location, $size, $price, $status, $aerial_image, $numbered_image, $lot_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Lot updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update lot: " . $stmt->error;
    }

    header("Location: lots.php");
    exit();
}
?>
