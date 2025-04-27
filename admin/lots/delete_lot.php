<!--admin/lots/delete_lot.php-->
<?php
session_start();
include '../../config/db.php';

if (isset($_GET['lot_id'])) {
    $lot_id = $_GET['lot_id'];

    // Get lot details for image deletion
    $lot_query = mysqli_query($conn, "SELECT * FROM lot WHERE lot_id = '$lot_id'");
    $lot = mysqli_fetch_assoc($lot_query);

    // Delete the lot image from server
    if ($lot['lot_image']) {
        unlink('../admin/lots/images/' . $lot['lot_image']);
    }

    // Delete the lot from the database
    $query = "DELETE FROM lot WHERE lot_id = '$lot_id'";
    if (mysqli_query($conn, $query)) {
        header("Location: lot_details.php?map_id=" . $lot['map_id']);
        exit;
    } else {
        echo "Error deleting lot: " . mysqli_error($conn);
    }
}
?>
