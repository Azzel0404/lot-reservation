<!-- admin/lots/view_lot_form.php -->

<?php
include('../../config/db.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM lot WHERE lot_id = $id";
    $result = mysqli_query($conn, $query);
    $lot = mysqli_fetch_assoc($result);
}
?>

<!-- Modal Close Button -->
<span class="close" onclick="closeLotDetailsModal()">&times;</span>

<h2 class="view-lot-header">Lot Details</h2>

<!-- Image Preview Container -->
<div class="image-preview-container">
    <div>
        <p><strong>Aerial Image:</strong></p>
        <?php if (!empty($lot['aerial_image'])): ?>
            <img src="uploads/<?php echo $lot['aerial_image']; ?>" alt="Aerial Image">
        <?php else: ?>
            <p>No image available</p>
        <?php endif; ?>
    </div>
    <div>
        <p><strong>Numbered Image:</strong></p>
        <?php if (!empty($lot['numbered_image'])): ?>
            <img src="uploads/<?php echo $lot['numbered_image']; ?>" alt="Numbered Image">
        <?php else: ?>
            <p>No image available</p>
        <?php endif; ?>
    </div>
</div>

<!-- Lot Edit Form -->
<form action="update_lot.php" method="POST" enctype="multipart/form-data" id="editLotForm">
    <input type="hidden" name="lot_id" value="<?php echo $lot['lot_id']; ?>">

    <label for="lot_number">Lot Number:</label>
    <input type="text" id="lot_number" name="lot_number" value="<?php echo $lot['lot_number']; ?>" required>

    <label for="location">Location:</label>
    <input type="text" id="location" name="location" value="<?php echo $lot['location']; ?>" required>

    <label for="size_meter_square">Size (m²):</label>
    <input type="number" id="size_meter_square" name="size_meter_square" step="0.01" value="<?php echo $lot['size_meter_square']; ?>" required>

    <label for="price">Price (₱):</label>
    <input type="number" id="price" name="price" step="0.01" value="<?php echo $lot['price']; ?>" required>

    <label for="status">Status:</label>
    <select id="status" name="status" required>
        <option value="Available" <?php if ($lot['status'] === 'Available') echo 'selected'; ?>>Available</option>
        <option value="Reserved" <?php if ($lot['status'] === 'Reserved') echo 'selected'; ?>>Reserved</option>
    </select>

    <label for="aerial_image">Change Aerial Image (optional):</label>
    <input type="file" id="aerial_image" name="aerial_image">

    <label for="numbered_image">Change Numbered Image (optional):</label>
    <input type="file" id="numbered_image" name="numbered_image">

    <button type="submit" id="editBtn" name="update_lot">Update Lot</button>
    <button type="button" style="background-color:#e74c3c; color:white;" onclick="deleteLot(<?php echo $lot['lot_id']; ?>)">Delete Lot</button>
</form>
