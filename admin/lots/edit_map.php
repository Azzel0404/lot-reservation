<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $map_id = mysqli_real_escape_string($conn, $_POST['map_id']);
    $map_number = mysqli_real_escape_string($conn, $_POST['map_number']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $map_layout = $_FILES['map_layout']['name'];

    $query = "UPDATE map SET map_number = '$map_number', location = '$location' WHERE map_id = '$map_id'";

    if ($map_layout) {
        $map_layout = time() . '-' . basename($map_layout);
        $map_layout_path = '../admin/images/' . $map_layout;

        if (move_uploaded_file($_FILES['map_layout']['tmp_name'], $map_layout_path)) {
            $query = "UPDATE map SET map_number = '$map_number', location = '$location', map_layout = '$map_layout' WHERE map_id = '$map_id'";
        } else {
            $error = "Failed to upload the new map layout image.";
        }
    }

    if (mysqli_query($conn, $query)) {
        header("Location: lots.php?success=1");
        exit;
    } else {
        $error = "Database Error: " . mysqli_error($conn);
    }
}
?>
