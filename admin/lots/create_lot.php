<!--admin/lots/create_lot.php-->
<?php
include('../../config/db.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form is submitted
if (isset($_POST['submit'])) {
    // Collect data from the form
    $lot_number = mysqli_real_escape_string($conn, $_POST['lot_number']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $size = $_POST['size_meter_square'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    // Handle file uploads
    $aerial_image = $_FILES['aerial_image']['name'];
    $numbered_image = $_FILES['numbered_image']['name'];

    // Check for upload errors and move files
    if ($_FILES['aerial_image']['error'] == 0) {
        $aerial_image_temp = $_FILES['aerial_image']['tmp_name'];
        $aerial_image_destination = 'uploads/' . $aerial_image;
        move_uploaded_file($aerial_image_temp, $aerial_image_destination);
    } else {
        echo "Error uploading aerial image: " . $_FILES['aerial_image']['error'];
    }

    if ($_FILES['numbered_image']['error'] == 0) {
        $numbered_image_temp = $_FILES['numbered_image']['tmp_name'];
        $numbered_image_destination = 'uploads/' . $numbered_image;
        move_uploaded_file($numbered_image_temp, $numbered_image_destination);
    } else {
        echo "Error uploading numbered image: " . $_FILES['numbered_image']['error'];
    }

    // Insert lot data into the database using a prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO lot (lot_number, location, size_meter_square, price, status, aerial_image, numbered_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddsss", $lot_number, $location, $size, $price, $status, $aerial_image, $numbered_image);

    if ($stmt->execute()) {
        header("Location: lots.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!-- HTML form to add a new lot -->
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
    <input type="file" name="aerial_image" accept="image/*">
    <label>Numbered Image</label>
    <input type="file" name="numbered_image" accept="image/*">
    <button type="submit" name="submit">Create Lot</button>
</form>
