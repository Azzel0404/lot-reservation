<!--admin/lots/delete_map.php-->

<?php
session_start();
include '../../config/db.php';

if (isset($_GET['map_id'])) {
    $map_id = mysqli_real_escape_string($conn, $_GET['map_id']);
    
    // Delete the map layout image from the server
    $result = mysqli_query($conn, "SELECT map_layout FROM map WHERE map_id = '$map_id'");
    if ($map = mysqli_fetch_assoc($result)) {
        $map_layout_path = '../admin/images/' . $map['map_layout'];
        
        // Check if the file exists before deleting
        if (file_exists($map_layout_path)) {
            unlink($map_layout_path);  // Delete the image
        }
    }

    // Delete the map entry from the database
    $query = "DELETE FROM map WHERE map_id = '$map_id'";
    if (mysqli_query($conn, $query)) {
        header("Location: lots.php?success=1");
        exit;
    } else {
        echo "Error deleting map: " . mysqli_error($conn);
    }
}
?>

