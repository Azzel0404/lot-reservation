<?php
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch_number = mysqli_real_escape_string($conn, $_POST['batch_number']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);

    // Upload images
    $aerialImageName = $_FILES['aerial_image']['name'];
    $aerialImageTmp = $_FILES['aerial_image']['tmp_name'];
    $numberedImageName = $_FILES['numbered_image']['name'];
    $numberedImageTmp = $_FILES['numbered_image']['tmp_name'];

    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    move_uploaded_file($aerialImageTmp, $uploadDir . $aerialImageName);
    move_uploaded_file($numberedImageTmp, $uploadDir . $numberedImageName);

    $query = "INSERT INTO lot_batch (batch_number, location, aerial_image, numbered_image)
              VALUES ('$batch_number', '$location', '$aerialImageName', '$numberedImageName')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Lot Batch added successfully!'); window.location.href='lots.php';</script>";
    } else {
        echo "<script>alert('Error adding Lot Batch: " . mysqli_error($conn) . "'); window.history.back();</script>";
    }
}
?>
