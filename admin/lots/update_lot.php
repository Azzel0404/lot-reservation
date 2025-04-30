<!--admin/lots/update_lot.php-->
<?php
include('../../config/db.php');

if (isset($_POST['update_lot'])) {
    $lot_id = $_POST['lot_id'];
    $lot_number = $_POST['lot_number'];
    $location = $_POST['location'];
    $size = $_POST['size_meter_square'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $sql_parts = [
        "lot_number = '$lot_number'",
        "location = '$location'",
        "size_meter_square = $size",
        "price = $price",
        "status = '$status'"
    ];

    // Handle aerial image upload
    if (!empty($_FILES['aerial_image']['name'])) {
        $aerial_image_name = time() . '_' . basename($_FILES['aerial_image']['name']);
        $aerial_target = "uploads/" . $aerial_image_name;
        move_uploaded_file($_FILES['aerial_image']['tmp_name'], $aerial_target);
        $sql_parts[] = "aerial_image = '$aerial_image_name'";
    }

    // Handle numbered image upload
    if (!empty($_FILES['numbered_image']['name'])) {
        $numbered_image_name = time() . '_' . basename($_FILES['numbered_image']['name']);
        $numbered_target = "uploads/" . $numbered_image_name;
        move_uploaded_file($_FILES['numbered_image']['tmp_name'], $numbered_target);
        $sql_parts[] = "numbered_image = '$numbered_image_name'";
    }

    // Handle PDF file upload
    if (!empty($_FILES['pdf_file']['name'])) {
        $pdf_file_name = time() . '_' . basename($_FILES['pdf_file']['name']);
        $pdf_target = "uploads/" . $pdf_file_name;

        // Validate PDF MIME type
        if ($_FILES['pdf_file']['type'] !== 'application/pdf') {
            $_SESSION['error'] = "Invalid PDF file.";
            header("Location: lots.php");
            exit();
        }

        move_uploaded_file($_FILES['pdf_file']['tmp_name'], $pdf_target);
        $sql_parts[] = "pdf_file = '$pdf_file_name'";
    }

    // Combine all parts and run query
    $sql = "UPDATE lot SET " . implode(', ', $sql_parts) . " WHERE lot_id = $lot_id";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Lot updated successfully.";
    } else {
        $_SESSION['error'] = "Update failed: " . mysqli_error($conn);
    }

    header("Location: lots.php");
    exit();
}
?>
