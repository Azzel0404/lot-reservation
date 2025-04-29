<!--admin/lots/edit_lot_form.php-->

<link rel="stylesheet" href="lots.css">

<div?php
include('../../config/db.php');

$lotId = $_GET['id'];
$sql = "SELECT * FROM lot WHERE lot_id = $lotId";
$result = mysqli_query($conn, $sql);
$lot = mysqli_fetch_assoc($result);
?>

<!-- ✨ Close Button -->
<span class="close" onclick="document.getElementById('lotDetailsModal').style.display='none'">&times;</span>

<div class="view-lot-header">
    <h2>View Lot</h2>
</div>

<!-- 🖼️ Image Preview at Top -->
<div class="image-preview-container">
    <div>
        <p><strong>Aerial Image</strong></p>
        <img src="uploads/<?php echo $lot['aerial_image']; ?>" alt="Aerial Image">
    </div>
    <div>
        <p><strong>Numbered Image</strong></p>
        <img src="uploads/<?php echo $lot['numbered_image']; ?>" alt="Numbered Image">
    </div>
</div>

<!-- 📝 Edit Form -->
<form id="editLotForm" action="update_lots.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="lot_id" value="<?php echo $lot['lot_id']; ?>">

    <input type="text" name="lot_number" value="<?php echo $lot['lot_number']; ?>" disabled required>
    <input type="text" name="location" value="<?php echo $lot['location']; ?>" disabled required>
    <input type="number" step="0.01" name="size_meter_square" value="<?php echo $lot['size_meter_square']; ?>" disabled required>
    <input type="number" step="0.01" name="price" value="<?php echo $lot['price']; ?>" disabled required>

    <select name="status" disabled required>
        <option value="Available" <?php if ($lot['status'] == 'Available') echo 'selected'; ?>>Available</option>
        <option value="Reserved" <?php if ($lot['status'] == 'Reserved') echo 'selected'; ?>>Reserved</option>
    </select>

    <label>Change Aerial Image:</label>
    <input type="file" name="aerial_image" accept="image/*" disabled>

    <label>Change Numbered Image:</label>
    <input type="file" name="numbered_image" accept="image/*" disabled>

    <!-- Button Area -->
    <button type="button" id="editBtn">Update Lot</button>
    <button type="submit" id="confirmBtn" name="update" style="display:none;">Confirm</button>
    <button type="button" id="cancelBtn" style="display:none; background-color:#95a5a6;">Cancel</button>
</form>

<!-- 🗑️ Delete Button -->
<form action="delete_lot.php" method="POST" onsubmit="return confirm('Delete this lot?');">
    <input type="hidden" name="lot_id" value="<?php echo $lot['lot_id']; ?>">
    <button type="submit" name="delete" style="background-color: #e74c3c;">Delete</button>
</form>

<!-- 🧠 JavaScript Logic -->
<script>
const form = document.getElementById('editLotForm');
const inputs = form.querySelectorAll('input, select');
const editBtn = document.getElementById('editBtn');
const confirmBtn = document.getElementById('confirmBtn');
const cancelBtn = document.getElementById('cancelBtn');

// Save initial values
const originalValues = {};
inputs.forEach(input => {
    if (input.type !== 'file') {
        originalValues[input.name] = input.value;
    }
});

editBtn.addEventListener('click', () => {
    // Enable form fields
    inputs.forEach(input => input.disabled = false);

    // Show confirm/cancel, hide edit
    editBtn.style.display = 'none';
    confirmBtn.style.display = 'inline-block';
    cancelBtn.style.display = 'inline-block';
});

cancelBtn.addEventListener('click', () => {
    // Restore original values
    inputs.forEach(input => {
        if (input.type !== 'file') {
            input.value = originalValues[input.name];
        }
        input.disabled = true;
    });

    // Reset buttons
    editBtn.style.display = 'inline-block';
    confirmBtn.style.display = 'none';
    cancelBtn.style.display = 'none';
});
</script>
