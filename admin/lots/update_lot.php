<?php
session_start();
include('../../config/db.php');

if (isset($_POST['update_lot'])) {
    $lot_id = $_POST['lot_id'];
    $lot_number = $_POST['lot_number'];
    $location = $_POST['location'];
    $size = $_POST['size_meter_square'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    // File handling
    $aerial_image = $_FILES['aerial_image']['name'];
    $numbered_image = $_FILES['numbered_image']['name'];

    $sql_parts = [
        "lot_number = '$lot_number'",
        "location = '$location'",
        "size_meter_square = $size",
        "price = $price",
        "status = '$status'"
    ];

    if ($aerial_image) {
        $target = "uploads/" . basename($aerial_image);
        move_uploaded_file($_FILES['aerial_image']['tmp_name'], $target);
        $sql_parts[] = "aerial_image = '$aerial_image'";
    }

    if ($numbered_image) {
        $target = "uploads/" . basename($numbered_image);
        move_uploaded_file($_FILES['numbered_image']['tmp_name'], $target);
        $sql_parts[] = "numbered_image = '$numbered_image'";
    }

    $sql = "UPDATE lot SET " . implode(', ', $sql_parts) . " WHERE lot_id = $lot_id";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Lot updated successfully.";
    } else {
        $_SESSION['error'] = "Update failed: " . mysqli_error($conn);
    }

    header("Location: lots.php");
    exit;
}
?>
