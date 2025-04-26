<!--admin/add_lot_action.php-->

<?php
ob_clean();  // Clear any prior output to prevent issues

session_start();
include('../config/db.php');  // Include database connection

header('Content-Type: application/json');  // Send response as JSON

$response = ['status' => 'error', 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $lot_number = $_POST['lot_number'];
    $location = $_POST['location'];
    $size = $_POST['size'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $aerial_path = '';
    $numbered_path = '';

    // Upload aerial image
    if (!empty($_FILES['aerial_image']['name'])) {
        $aerial_filename = time() . '_aerial_' . basename($_FILES['aerial_image']['name']);
        $aerial_path = '../admin/images/' . $aerial_filename;

        if ($_FILES['aerial_image']['error'] !== 0 || !move_uploaded_file($_FILES['aerial_image']['tmp_name'], $aerial_path)) {
            $response['message'] = "Failed to upload aerial image";
            echo json_encode($response);  // Return the response as JSON
            exit;
        }
    }

    // Upload numbered image
    if (!empty($_FILES['numbered_image']['name'])) {
        $numbered_filename = time() . '_numbered_' . basename($_FILES['numbered_image']['name']);
        $numbered_path = '../admin/images/' . $numbered_filename;

        if ($_FILES['numbered_image']['error'] !== 0 || !move_uploaded_file($_FILES['numbered_image']['tmp_name'], $numbered_path)) {
            $response['message'] = "Failed to upload numbered image";
            echo json_encode($response);  // Return the response as JSON
            exit;
        }
    }

    if (empty($aerial_path) || empty($numbered_path)) {
        $response['message'] = "One or both images were not uploaded";
        echo json_encode($response);  // Return the response as JSON
        exit;
    }

    $aerial_db_path = 'admin/images/' . basename($aerial_path);
    $numbered_db_path = 'admin/images/' . basename($numbered_path);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO lot (lot_number, location, size_meter_square, price, status, aerial_image, numbered_image)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddsss", $lot_number, $location, $size, $price, $status, $aerial_db_path, $numbered_db_path);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Lot added successfully';
    } else {
        $response['status'] = 'error';
        $response['message'] = "Database error - " . $stmt->error;
    }

    echo json_encode($response); // This should always send {"status": "success", "message": "Lot added successfully"} or an error message.
    exit;
}
?>
