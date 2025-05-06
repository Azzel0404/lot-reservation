<?php
include('../../config/db.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (isset($_POST['update_lot'])) {
    $lot_id = $_POST['lot_id'];
    $lot_number = trim($_POST['lot_number']);
    $location = trim($_POST['location']);
    $size = floatval($_POST['size_meter_square']);
    $price = floatval($_POST['price']);
    $status = $_POST['status'];

    // Validate lot number format
    if (!preg_match('/^LOT-\d{4}-\d{3}$/', $lot_number)) {
        $_SESSION['error'] = "Lot number must be in format LOT-YYMM-XXX (e.g. LOT-" . date("ym") . "-001)";
        header("Location: lots.php");
        exit();
    }

    // Validate price is not negative
    if ($price < 0) {
        $_SESSION['error'] = "Price cannot be negative.";
        header("Location: lots.php");
        exit();
    }

    // Validate size is positive
    if ($size <= 0) {
        $_SESSION['error'] = "Size must be greater than zero.";
        header("Location: lots.php");
        exit();
    }

    // Check if lot number already exists (excluding current lot)
    $check_query = $conn->prepare("SELECT lot_id FROM lot WHERE lot_number = ? AND lot_id != ?");
    $check_query->bind_param("si", $lot_number, $lot_id);
    $check_query->execute();
    $check_result = $check_query->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "This lot number already exists. Please choose a different one.";
        header("Location: lots.php");
        exit();
    }

    $allowed_image_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    $maxPdfSize = 10 * 1024 * 1024; // 10MB

    $sql_parts = [
        "lot_number = '" . mysqli_real_escape_string($conn, $lot_number) . "'",
        "location = '" . mysqli_real_escape_string($conn, $location) . "'",
        "size_meter_square = $size",
        "price = $price",
        "status = '" . mysqli_real_escape_string($conn, $status) . "'"
    ];

    // Handle aerial image upload
    if (!empty($_FILES['aerial_image']['name'])) {
        $aerial_image = $_FILES['aerial_image'];
        
        // Validate image type
        if (!in_array($aerial_image['type'], $allowed_image_types)) {
            $_SESSION['error'] = "Only JPG, JPEG, and PNG images are allowed for aerial image.";
            header("Location: lots.php");
            exit();
        }
        
        // Validate image size
        if ($aerial_image['size'] > $maxFileSize) {
            $_SESSION['error'] = "Aerial image must be less than 5MB.";
            header("Location: lots.php");
            exit();
        }

        $aerial_image_name = time() . '_' . basename($aerial_image['name']);
        $aerial_target = $uploadDir . $aerial_image_name;
        
        if (move_uploaded_file($aerial_image['tmp_name'], $aerial_target)) {
            $sql_parts[] = "aerial_image = '" . mysqli_real_escape_string($conn, $aerial_image_name) . "'";
            
            // Delete old aerial image if it exists
            $old_image = $conn->query("SELECT aerial_image FROM lot WHERE lot_id = $lot_id")->fetch_assoc()['aerial_image'];
            if ($old_image && file_exists($uploadDir . $old_image)) {
                unlink($uploadDir . $old_image);
            }
        } else {
            $_SESSION['error'] = "Failed to upload aerial image.";
            header("Location: lots.php");
            exit();
        }
    }

    // Handle numbered image upload
    if (!empty($_FILES['numbered_image']['name'])) {
        $numbered_image = $_FILES['numbered_image'];
        
        // Validate image type
        if (!in_array($numbered_image['type'], $allowed_image_types)) {
            $_SESSION['error'] = "Only JPG, JPEG, and PNG images are allowed for numbered image.";
            header("Location: lots.php");
            exit();
        }
        
        // Validate image size
        if ($numbered_image['size'] > $maxFileSize) {
            $_SESSION['error'] = "Numbered image must be less than 5MB.";
            header("Location: lots.php");
            exit();
        }

        $numbered_image_name = time() . '_' . basename($numbered_image['name']);
        $numbered_target = $uploadDir . $numbered_image_name;
        
        if (move_uploaded_file($numbered_image['tmp_name'], $numbered_target)) {
            $sql_parts[] = "numbered_image = '" . mysqli_real_escape_string($conn, $numbered_image_name) . "'";
            
            // Delete old numbered image if it exists
            $old_image = $conn->query("SELECT numbered_image FROM lot WHERE lot_id = $lot_id")->fetch_assoc()['numbered_image'];
            if ($old_image && file_exists($uploadDir . $old_image)) {
                unlink($uploadDir . $old_image);
            }
        } else {
            $_SESSION['error'] = "Failed to upload numbered image.";
            header("Location: lots.php");
            exit();
        }
    }

    // Handle PDF file upload
    if (!empty($_FILES['pdf_file']['name'])) {
        $pdf_file = $_FILES['pdf_file'];
        
        // Validate PDF type
        if ($pdf_file['type'] !== 'application/pdf') {
            $_SESSION['error'] = "Uploaded file must be a PDF.";
            header("Location: lots.php");
            exit();
        }
        
        // Validate PDF size
        if ($pdf_file['size'] > $maxPdfSize) {
            $_SESSION['error'] = "PDF file must be less than 10MB.";
            header("Location: lots.php");
            exit();
        }

        $pdf_file_name = time() . '_' . basename($pdf_file['name']);
        $pdf_target = $uploadDir . $pdf_file_name;
        
        if (move_uploaded_file($pdf_file['tmp_name'], $pdf_target)) {
            $sql_parts[] = "pdf_file = '" . mysqli_real_escape_string($conn, $pdf_file_name) . "'";
            
            // Delete old PDF file if it exists
            $old_pdf = $conn->query("SELECT pdf_file FROM lot WHERE lot_id = $lot_id")->fetch_assoc()['pdf_file'];
            if ($old_pdf && file_exists($uploadDir . $old_pdf)) {
                unlink($uploadDir . $old_pdf);
            }
        } else {
            $_SESSION['error'] = "Failed to upload PDF file.";
            header("Location: lots.php");
            exit();
        }
    }

    // Combine all parts and run query
    $sql = "UPDATE lot SET " . implode(', ', $sql_parts) . " WHERE lot_id = $lot_id";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Lot updated successfully.";
    } else {
        $_SESSION['error'] = "Error updating lot: " . mysqli_error($conn);
    }

    header("Location: lots.php");
    exit();
}
?>