<!-- admin/lots/add_map.php -->
<?php
session_start();
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $map_number = mysqli_real_escape_string($conn, $_POST['map_number']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $map_layout = $_FILES['map_layout']['name'];

    if ($map_layout) {
        $map_layout = time() . '-' . basename($map_layout);
        $map_layout_path = '../admin/images/' . $map_layout;

        // Ensure the upload directory exists
        if (!file_exists('../admin/images/')) {
            mkdir('../admin/images/', 0777, true);
        }

        if (move_uploaded_file($_FILES['map_layout']['tmp_name'], $map_layout_path)) {
            $query = "INSERT INTO map (map_number, location, map_layout) VALUES ('$map_number', '$location', '$map_layout')";
            if (mysqli_query($conn, $query)) {
                header("Location: lots.php?success=1");
                exit;
            } else {
                $error = "Database Error: " . mysqli_error($conn);
            }
        } else {
            $error = "Failed to upload the map layout image.";
        }
    } else {
        $error = "Please upload a map layout image.";
    }
}
?>


