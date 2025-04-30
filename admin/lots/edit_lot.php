<?php
include('../../config/db.php');
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid lot ID.";
    header("Location: lots.php");
    exit();
}

$lot_id = $_GET['id'];

// Fetch lot data
$stmt = $conn->prepare("SELECT * FROM lot WHERE lot_id = ?");
$stmt->bind_param("i", $lot_id);
$stmt->execute();
$result = $stmt->get_result();
$lot = $result->fetch_assoc();

if (!$lot) {
    $_SESSION['error'] = "Lot not found.";
    header("Location: lots.php");
    exit();
}
?>

<h2>Edit Lot</h2>
<form action="update_lot.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="lot_id" value="<?= htmlspecialchars($lot['lot_id']) ?>">

    <input type="text" name="lot_number" placeholder="Lot Number" required value="<?= htmlspecialchars($lot['lot_number']) ?>">
    <input type="text" name="location" placeholder="Location" required value="<?= htmlspecialchars($lot['location']) ?>">
    <input type="number" step="0.01" name="size_meter_square" placeholder="Size in m²" required value="<?= $lot['size_meter_square'] ?>">
    <input type="number" step="0.01" name="price" placeholder="Price" required value="<?= $lot['price'] ?>">
    
    <select name="status" required>
        <option value="Available" <?= $lot['status'] == 'Available' ? 'selected' : '' ?>>Available</option>
        <option value="Reserved" <?= $lot['status'] == 'Reserved' ? 'selected' : '' ?>>Reserved</option>
    </select>

    <label>Current Aerial Image:</label><br>
    <img src="uploads/<?= htmlspecialchars($lot['aerial_image']) ?>" width="150"><br>
    <label>Upload New Aerial Image (optional)</label>
    <input type="file" name="aerial_image" accept="image/*">

    <label>Current Numbered Image:</label><br>
    <img src="uploads/<?= htmlspecialchars($lot['numbered_image']) ?>" width="150"><br>
    <label>Upload New Numbered Image (optional)</label>
    <input type="file" name="numbered_image" accept="image/*">

    <button type="submit" name="update">Update Lot</button>
</form>
