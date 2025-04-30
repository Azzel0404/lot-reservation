<!--admin/lots/get_lot_details.php-->
<?php
include('../../config/db.php');

// Check if the 'id' parameter is passed
if (isset($_GET['id'])) {
    $lotId = $_GET['id'];

    // Fetch the lot details from the database
    $sql = "SELECT * FROM lot WHERE lot_id = $lotId";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        // Return the lot details as JSON
        echo json_encode($row);
    } else {
        // No lot found, return an error message
        echo json_encode(["error" => "Lot not found"]);
    }
} else {
    // No ID passed, return an error message
    echo json_encode(["error" => "Invalid request"]);
}
?>
