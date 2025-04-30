<!--admin/lots/edit_lot_form.php-->

<link rel="stylesheet" href="lots.css">

<?php
include('../../config/db.php');

$lotId = $_GET['id'];
// Sanitize the input to prevent SQL injection
$lotId = mysqli_real_escape_string($conn, $lotId);
$sql = "SELECT * FROM lot WHERE lot_id = $lotId";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo "Error fetching lot details: " . mysqli_error($conn);
    exit();
}

$lot = mysqli_fetch_assoc($result);

if (!$lot) {
    echo "Lot not found.";
    exit();
}
?>

<span class="close" onclick="document.getElementById('lotDetailsModal').style.display='none'">&times;</span>

<div class="view-lot-header">
    <h2>View Lot</h2>
</div>

<div class="image-preview-container">
    <div>
        <p><strong>Aerial Image</strong></p>
        <img src="uploads/<?php echo htmlspecialchars($lot['aerial_image']); ?>" alt="Aerial Image" style="max-width: 200px; max-height: 200px;">
    </div>
    <div>
        <p><strong>Numbered Image</strong></p>
        <img src="uploads/<?php echo htmlspecialchars($lot['numbered_image']); ?>" alt="Numbered Image" style="max-width: 200px; max-height: 200px;">
    </div>
</div>

<!-- Start of edit form -->
<form id="editLotForm" action="update_lot.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="lot_id" value="<?php echo htmlspecialchars($lot['lot_id']); ?>">

    <label for="lot_number">Lot Number:</label>
    <input type="text" id="lot_number" name="lot_number" value="<?php echo htmlspecialchars($lot['lot_number']); ?>" required readonly>

    <label for="location">Location:</label>
    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($lot['location']); ?>" required readonly>

    <label for="size_meter_square">Size (sq m):</label>
    <input type="number" step="0.01" id="size_meter_square" name="size_meter_square" value="<?php echo htmlspecialchars($lot['size_meter_square']); ?>" required readonly>

    <label for="price">Price:</label>
    <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($lot['price']); ?>" required readonly>

    <label for="status">Status:</label>
    <select id="status" name="status" required disabled>
        <option value="Available" <?php if ($lot['status'] == 'Available') echo 'selected'; ?>>Available</option>
        <option value="Reserved" <?php if ($lot['status'] == 'Reserved') echo 'selected'; ?>>Reserved</option>
    </select>

    <label for="aerial_image">Change Aerial Image:</label>
    <input type="file" id="aerial_image" name="aerial_image" accept="image/*" disabled>

    <label for="numbered_image">Change Numbered Image:</label>
    <input type="file" id="numbered_image" name="numbered_image" accept="image/*" disabled>

    <div id="viewActions">
        <button type="button" id="editBtn">Update Lot</button>
    </div>

    <div id="editActions" style="display:none;">
        <button type="submit" id="confirmBtn" name="update">Submit</button>
        <button type="button" id="cancelBtn" style="background-color:#95a5a6;">Cancel</button>
    </div>
</form>

<!-- Separate delete form -->
<form action="delete_lot.php" method="POST" onsubmit="return confirm('Delete this lot?');" style="display:inline;">
    <input type="hidden" name="lot_id" value="<?php echo htmlspecialchars($lot['lot_id']); ?>">
    <button type="submit" name="delete" style="background-color: #e74c3c;">Delete</button>
</form>

<script>
const form = document.getElementById('editLotForm');
const inputs = form.querySelectorAll('input, select, textarea');
const editBtn = document.getElementById('editBtn');
const confirmBtn = document.getElementById('confirmBtn');
const cancelBtn = document.getElementById('cancelBtn');
const viewActions = document.getElementById('viewActions');
const editActions = document.getElementById('editActions');
const viewHeader = document.querySelector('.view-lot-header h2');

confirmBtn.addEventListener('click', () => {
    console.log("Submit clicked!");
});

// Save initial values (excluding file inputs)
const originalValues = {};
inputs.forEach(input => {
    if (input.type !== 'file') {
        originalValues[input.name] = input.value;
    }
});

editBtn.addEventListener('click', () => {
    inputs.forEach(input => {
        input.disabled = false;
        input.readOnly = false;
    });

    viewHeader.textContent = 'Edit Lot';
    viewActions.style.display = 'none';
    editActions.style.display = 'block';
});


cancelBtn.addEventListener('click', () => {
    // Restore original values (excluding file inputs)
    inputs.forEach(input => {
        if (input.type !== 'file') {
            input.value = originalValues[input.name];
        }
        input.disabled = true;
    });

    // Revert modal header
    viewHeader.textContent = 'View Lot';

    // Reset buttons
    viewActions.style.display = 'block';
    editActions.style.display = 'none';
});
</script>