<!--admin/lots/get_lot_details.php-->
<?php
include('../../config/db.php');

// Set JSON header
header('Content-Type: application/json');

// Check if the 'id' parameter is passed
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $lotId = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM lot WHERE lot_id = ?");
    $stmt->bind_param("i", $lotId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Lot not found"]);
    }
} else {
    echo json_encode(["error" => "Invalid request"]);
}
?>
