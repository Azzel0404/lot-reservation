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

$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];

// Standard lot number format
$lotNumberFormat = "LOT-{YYMM}-{XXX}";
$currentExample = "LOT-" . date("ym") . "-001";

// Fetch existing lot numbers from DB and validate format
$lot_numbers = [];
$lotQuery = $conn->query("SELECT lot_number, status FROM lot ORDER BY lot_number");
if ($lotQuery && $lotQuery->num_rows > 0) {
    while ($row = $lotQuery->fetch_assoc()) {
        // Only include lot numbers that match our standard format
        if (preg_match('/^LOT-\d{4}-\d{3}$/', $row['lot_number'])) {
            $lot_numbers[$row['lot_number']] = $row['status'];
        }
    }
}

// Function to generate the next available lot number
function getNextLotNumber($conn) {
    $query = $conn->query("SELECT MAX(lot_number) as max_lot FROM lot WHERE lot_number REGEXP '^LOT-[0-9]{4}-[0-9]{3}$'");
    if ($query && $query->num_rows > 0) {
        $row = $query->fetch_assoc();
        if (!empty($row['max_lot'])) {
            $parts = explode('-', $row['max_lot']);
            $sequence = intval($parts[2]) + 1;
            return $parts[0] . '-' . $parts[1] . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        }
    }
    return "LOT-" . date("ym") . "-001";
}

$nextLotNumber = getNextLotNumber($conn);

if (isset($_POST['submit'])) {
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

    // Check if lot number already exists
    $check_query = $conn->prepare("SELECT lot_id FROM lot WHERE lot_number = ?");
    $check_query->bind_param("s", $lot_number);
    $check_query->execute();
    $check_result = $check_query->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "This lot number already exists. Please choose a different one.";
        header("Location: lots.php");
        exit();
    }

    // Validate price is not negative
    if ($price <= 0 ) {
        $_SESSION['error'] = "Price cannot be zero or negative.";
        header("Location: lots.php");
        exit();
    }

    // Validate size is positive
    if ($size <= 0) {
        $_SESSION['error'] = "Size must be greater than zero.";
        header("Location: lots.php");
        exit();
    }

    // Validate files were uploaded
    if (!isset($_FILES['aerial_image']) || !isset($_FILES['numbered_image'])) {
        $_SESSION['error'] = "Both image files are required.";
        header("Location: lots.php");
        exit();
    }

    $aerial_image = $_FILES['aerial_image'];
    $numbered_image = $_FILES['numbered_image'];

    // Check image types
    if (!in_array($aerial_image['type'], $allowed_types) || !in_array($numbered_image['type'], $allowed_types)) {
        $_SESSION['error'] = "Only JPG, JPEG, and PNG images are allowed.";
        header("Location: lots.php");
        exit();
    }

    // Check image size (max 5MB)
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    if ($aerial_image['size'] > $maxFileSize || $numbered_image['size'] > $maxFileSize) {
        $_SESSION['error'] = "Image files must be less than 5MB.";
        header("Location: lots.php");
        exit();
    }

    // Move image files
    $aerial_image_name = time() . '_' . basename($aerial_image['name']);
    $aerial_path = $uploadDir . $aerial_image_name;
    if (!move_uploaded_file($aerial_image['tmp_name'], $aerial_path)) {
        $_SESSION['error'] = "Failed to upload aerial image.";
        header("Location: lots.php");
        exit();
    }

    $numbered_image_name = time() . '_' . basename($numbered_image['name']);
    $numbered_path = $uploadDir . $numbered_image_name;
    if (!move_uploaded_file($numbered_image['tmp_name'], $numbered_path)) {
        unlink($aerial_path);
        $_SESSION['error'] = "Failed to upload numbered image.";
        header("Location: lots.php");
        exit();
    }

    // Handle optional PDF file
    $pdf_file_name = null;
    if (!empty($_FILES['pdf_file']['name'])) {
        $pdf_file = $_FILES['pdf_file'];
        if ($pdf_file['type'] === 'application/pdf') {
            if ($pdf_file['size'] > 10 * 1024 * 1024) {
                $_SESSION['error'] = "PDF file must be less than 10MB.";
                header("Location: lots.php");
                exit();
            }
            
            $pdf_file_name = time() . '_' . basename($pdf_file['name']);
            $pdf_path = $uploadDir . $pdf_file_name;
            if (!move_uploaded_file($pdf_file['tmp_name'], $pdf_path)) {
                $_SESSION['error'] = "Failed to upload PDF file.";
                header("Location: lots.php");
                exit();
            }
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
        unlink($aerial_path);
        unlink($numbered_path);
        if ($pdf_file_name) {
            unlink($uploadDir . $pdf_file_name);
        }
        $_SESSION['error'] = "Error creating lot: " . $stmt->error;
        header("Location: lots.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Lot</title>
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            padding: 10px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #2980b9;
        }
        small {
            color: #666;
            font-size: 0.9em;
        }
        .lot-number-section {
            background: #eaf2f8;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error {
            color: #e74c3c;
            margin-top: 5px;
        }
        .success {
            color: #27ae60;
            margin-top: 5px;
        }
        .lot-number-select {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .format-display {
            background: #fff;
            padding: 10px;
            border-radius: 4px;
            border: 1px dashed #ccc;
            margin-top: 10px;
            font-family: monospace;
        }
        .lot-status {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 0.8em;
            margin-left: 5px;
        }
        .status-available {
            background-color: #2ecc71;
            color: white;
        }
        .status-reserved {
            background-color: #f39c12;
            color: white;
        }
        .status-sold {
            background-color: #e74c3c;
            color: white;
        }

        .message-container {
            padding: 10px;
            text-align: center;
            margin-bottom: 20px; /* Add some space below the message */
        }

        .message-container .error {
            color: #e74c3c;
            background-color: #fdecea;
            border: 1px solid #e74c3c;
            padding: 8px;
            border-radius: 4px;
        }

        .message-container .success {
            color: #27ae60;
            background-color: #e6f9e8;
            border: 1px solid #27ae60;
            padding: 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add New Lot</h2>
        
        <div class="message-container">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
        </div>

<div class="container">
    <h2>Add New Lot</h2>
    </div>
        
        <form action="" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <div class="lot-number-section">
                <div class="form-group">
                    <label for="lot_number">Lot Number</label>
                    <select name="lot_number" id="lot_number" class="lot-number-select" required>
                        <option value="">Select Lot Number</option>
                        <?php foreach ($lot_numbers as $number => $status): ?>
                            <option value="<?= htmlspecialchars($number) ?>" disabled>
                                <?= htmlspecialchars($number) ?>
                                <span class="lot-status status-<?= strtolower($status) ?>"><?= $status ?></span>
                            </option>
                        <?php endforeach; ?>
                        <option value="<?= htmlspecialchars($nextLotNumber) ?>">+ Add New Lot (<?= htmlspecialchars($nextLotNumber) ?>)</option>
                    </select>
                    <div class="format-display">
                        Standard Format: LOT-YYMM-XXX (e.g. <?= htmlspecialchars($currentExample) ?>)
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" name="location" id="location" placeholder="Location" required>
            </div>
            
            <div class="form-group">
                <label for="size_meter_square">Size (m²)</label>
                <input type="number" step="0.01" name="size_meter_square" id="size_meter_square" placeholder="Size in m²" required min="0.01">
            </div>
            
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" step="0.01" name="price" id="price" placeholder="Price" required min="0">
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" required>
                    <option value="Available">Available</option>
                    <option value="Reserved">Reserved</option>
                </select>
            </div>

            <div class="form-group">
                <label for="aerial_image">Aerial Image (JPEG/PNG, max 5MB)</label>
                <input type="file" name="aerial_image" id="aerial_image" accept="image/jpeg,image/png" required>
            </div>

            <div class="form-group">
                <label for="numbered_image">Numbered Image (JPEG/PNG, max 5MB)</label>
                <input type="file" name="numbered_image" id="numbered_image" accept="image/jpeg,image/png" required>
            </div>

            <div class="form-group">
                <label for="pdf_file">PDF File (optional, max 10MB)</label>
                <input type="file" name="pdf_file" id="pdf_file" accept="application/pdf">
            </div>

            <button type="submit" name="submit">Create Lot</button>
        </form>
    </div>

    <script>
        function validateForm() {
            const price = document.getElementById('price');
            const size = document.getElementById('size_meter_square');
            const aerialImage = document.getElementById('aerial_image');
            const numberedImage = document.getElementById('numbered_image');
            const lotNumber = document.getElementById('lot_number').value;
            
            // Validate lot number format
            if (!lotNumber.match(/^LOT-\d{4}-\d{3}$/)) {
                alert('Lot number must be in format LOT-YYMM-XXX (e.g. LOT-<?= date("ym") ?>-001)');
                return false;
            }
            
            if (price.value < 0) {
                alert('Price cannot be negative.');
                return false;
            }
            
            if (size.value <= 0) {
                alert('Size must be greater than zero.');
                return false;
            }
            
            if (!aerialImage.files.length || !numberedImage.files.length) {
                alert('Both image files are required.');
                return false;
            }
            
            const allowedImageTypes = ['image/jpeg', 'image/png'];
            if (!allowedImageTypes.includes(aerialImage.files[0].type) || 
                !allowedImageTypes.includes(numberedImage.files[0].type)) {
                alert('Only JPEG and PNG images are allowed.');
                return false;
            }
            
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (aerialImage.files[0].size > maxSize || numberedImage.files[0].size > maxSize) {
                alert('Image files must be smaller than 5MB.');
                return false;
            }
            
            const pdfFile = document.getElementById('pdf_file');
            if (pdfFile.files.length) {
                if (pdfFile.files[0].type !== 'application/pdf') {
                    alert('Uploaded file must be a PDF.');
                    return false;
                }
                if (pdfFile.files[0].size > 10 * 1024 * 1024) {
                    alert('PDF file must be smaller than 10MB.');
                    return false;
                }
            }
            
            return true;
        }
    </script>
</body>
</html>