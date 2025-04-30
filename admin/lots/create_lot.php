<!--admin/lots/create_lot.php-->
<?php
include('../../config/db.php');
session_start();

// Enable error reporting during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Make sure uploads folder exists
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Allowed image types
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];

if (isset($_POST['submit'])) {
    $lot_number = trim($_POST['lot_number']);
    $location = trim($_POST['location']);
    $size = floatval($_POST['size_meter_square']);
    $price = floatval($_POST['price']);
    $status = $_POST['status'];

    // Validate files
    $aerial_image = $_FILES['aerial_image'];
    $numbered_image = $_FILES['numbered_image'];

    // Check image types
    if (!in_array($aerial_image['type'], $allowed_types) || !in_array($numbered_image['type'], $allowed_types)) {
        $_SESSION['error'] = "Invalid image type.";
        header("Location: lots.php");
        exit();
    }

    // Move image files
    $aerial_image_name = time() . '_' . basename($aerial_image['name']);
    $aerial_path = $uploadDir . $aerial_image_name;
    move_uploaded_file($aerial_image['tmp_name'], $aerial_path);

    $numbered_image_name = time() . '_' . basename($numbered_image['name']);
    $numbered_path = $uploadDir . $numbered_image_name;
    move_uploaded_file($numbered_image['tmp_name'], $numbered_path);

    // Handle optional PDF file
    $pdf_file_name = null;
    if (!empty($_FILES['pdf_file']['name'])) {
        $pdf_file = $_FILES['pdf_file'];
        if ($pdf_file['type'] === 'application/pdf') {
            $pdf_file_name = time() . '_' . basename($pdf_file['name']);
            $pdf_path = $uploadDir . $pdf_file_name;
            move_uploaded_file($pdf_file['tmp_name'], $pdf_path);
        } else {
            $_SESSION['error'] = "Uploaded file must be a PDF.";
            header("Location: lots.php");
            exit();
        }
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO lot (lot_number, location, size_meter_square, price, status, aerial_image, numbered_image, pdf_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddssss", $lot_number, $location, $size, $price, $status, $aerial_image_name, $numbered_image_name, $pdf_file_name);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Lot created successfully.";
        header("Location: lots.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
        header("Location: lots.php");
        exit();
    }
}
?>

<!-- HTML Form -->
<form action="" method="POST" enctype="multipart/form-data">
    <h2>Add New Lot</h2>
    <input type="text" name="lot_number" placeholder="Lot Number" required>
    <input type="text" name="location" placeholder="Location" required>
    <input type="number" step="0.01" name="size_meter_square" placeholder="Size in m²" required>
    <input type="number" step="0.01" name="price" placeholder="Price" required>
    
    <select name="status" required>
        <option value="Available">Available</option>
        <option value="Reserved">Reserved</option>
    </select>

    <label>Aerial Image</label>
    <input type="file" name="aerial_image" accept="image/*" required>

    <label>Numbered Image</label>
    <input type="file" name="numbered_image" accept="image/*" required>

    <label>PDF File (optional)</label>
    <input type="file" name="pdf_file" accept="application/pdf">

    <button type="submit" name="submit">Create Lot</button>
</form>
