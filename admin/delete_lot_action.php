<?php
// Include the database configuration
include('../config/db.php');

// Check if the lot ID is passed through the GET request
if (isset($_GET['lot_id'])) {
    $lot_id = $_GET['lot_id'];

    // Prepare the SQL query to delete the lot
    $sql = "DELETE FROM lot WHERE lot_id = ?";
    $stmt = $conn->prepare($sql);

    // Bind the lot ID to the query
    $stmt->bind_param("i", $lot_id);

    // Execute the query
    if ($stmt->execute()) {
        // If successful, redirect with a success message
        header("Location: lots.php?success=Lot deleted successfully.");
    } else {
        // If there is an error, redirect with an error message
        header("Location: lots.php?error=Failed to delete lot.");
    }
} else {
    // If no lot ID is provided, redirect with an error message
    header("Location: lots.php?error=Invalid request.");
}
?>
