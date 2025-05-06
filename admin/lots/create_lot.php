<!--admin/lots/create_lot.php-->
<?php
include('../../config/db.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


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
<style>
    /* Form Container */
    
    /* Form Header */
    .form-header {
        font-size: 1.5rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid green;
    }
    
    /* Form Grid Layout */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    /* Form Group Styling */
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-group.full-width {
        grid-column: span 2;
    }
    
    /* Label Styling */
    label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #34495e;
        font-size: 0.9rem;
    }
    
    /* Input Styling */
    input[type="text"],
    input[type="number"],
    select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.9rem;
        transition: border-color 0.2s;
    }
    
    input[type="text"]:focus,
    input[type="number"]:focus,
    select:focus {
        border-color: #3498db;
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }
    
    /* File Input Styling */
    input[type="file"] {
        width: 100%;
        padding: 0.5rem;
        border: 1px dashed #ddd;
        border-radius: 6px;
        background: #f8f9fa;
    }
    
    /* Button Styling */
    .submit-btn {
        background-color: #2ecc71;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
        width: 100%;
        margin-top: 0.5rem;
    }
    
    .submit-btn:hover {
        background-color: #27ae60;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .form-group.full-width {
            grid-column: span 1;
        }
    }
</style>

<div class="create-lot-form">
    <h2 class="form-header">Add New Lot</h2>
    
    <form action="" method="POST" enctype="multipart/form-data" class="form-grid">
        <div class="form-group">
            <label for="lot_number">Lot Number</label>
            <input type="text" id="lot_number" name="lot_number" placeholder="Enter lot number" required>
        </div>
        
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" placeholder="Enter location" required>
        </div>
        
        <div class="form-group">
            <label for="size_meter_square">Size (m²)</label>
            <input type="number" id="size_meter_square" name="size_meter_square" step="0.01" placeholder="Enter size" required>
        </div>
        
        <div class="form-group">
            <label for="price">Price (₱)</label>
            <input type="number" id="price" name="price" step="0.01" placeholder="Enter price" required>
        </div>
        
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="Available">Available</option>
                <option value="Reserved">Reserved</option>
            </select>
        </div>
        
        <div class="form-group full-width">
            <label for="aerial_image">Aerial Image</label>
            <input type="file" id="aerial_image" name="aerial_image" accept="image/*" required>
        </div>
        
        <div class="form-group full-width">
            <label for="numbered_image">Numbered Image</label>
            <input type="file" id="numbered_image" name="numbered_image" accept="image/*" required>
        </div>
        
        <div class="form-group full-width">
            <label for="pdf_file">PDF File (optional)</label>
            <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf">
        </div>
        
        <div class="form-group full-width">
            <button type="submit" name="submit" class="submit-btn">Create Lot</button>
        </div>
    </form>
</div>