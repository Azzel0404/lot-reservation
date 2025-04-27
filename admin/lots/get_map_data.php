<!-- admin/lots/get_map_data.php -->
<?php
session_start();
include '../../config/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['map_id'])) {
    $map_id = mysqli_real_escape_string($conn, $_GET['map_id']);
    
    // Execute query to get map details
    $result = mysqli_query($conn, "SELECT * FROM map WHERE map_id = '$map_id'");

    if ($result) {
        if ($map = mysqli_fetch_assoc($result)) {
            // Return map data as JSON
            echo json_encode($map);
        } else {
            // Map not found
            echo json_encode(['error' => 'Map not found']);
        }
    } else {
        // Query failed
        echo json_encode(['error' => 'Database query failed: ' . mysqli_error($conn)]);
    }
} else {
    // No map ID provided
    echo json_encode(['error' => 'No map ID provided']);
}

mysqli_close($conn); // Close database connection
?>
