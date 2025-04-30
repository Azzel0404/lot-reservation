<!-- admin/lots/view_lot_form.php -->

<?php
include('../../config/db.php');

$lotId = mysqli_real_escape_string($conn, $_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM lot WHERE lot_id = $lotId");
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
        <img src="uploads/<?php echo htmlspecialchars($lot['aerial_image']); ?>" alt="Aerial Image">
    </div>
    <div>
        <p><strong>Numbered Image</strong></p>
        <img src="uploads/<?php echo htmlspecialchars($lot['numbered_image']); ?>" alt="Numbered Image">
    </div>
</div>

<form>
    <label>Lot Number:</label>
    <input type="text" value="<?php echo htmlspecialchars($lot['lot_number']); ?>" readonly>
    <label>Location:</label>
    <input type="text" value="<?php echo htmlspecialchars($lot['location']); ?>" readonly>
    <label>Size (sq m):</label>
    <input type="number" value="<?php echo htmlspecialchars($lot['size_meter_square']); ?>" readonly>
    <label>Price:</label>
    <input type="number" value="<?php echo htmlspecialchars($lot['price']); ?>" readonly>
    <label>Status:</label>
    <input type="text" value="<?php echo htmlspecialchars($lot['status']); ?>" readonly>
</form>

<!-- Buttons -->
<div style="display: flex; gap: 10px; margin-top: 20px;">
    <form action="delete_lot.php" method="POST" onsubmit="return confirm('Delete this lot?');">
        <input type="hidden" name="lot_id" value="<?php echo htmlspecialchars($lot['lot_id']); ?>">
        <button type="submit" name="delete" style="background-color: #e74c3c;">Delete</button>
    </form>
    <button id="editBtn" onclick="openEditModal(<?php echo $lot['lot_id']; ?>)">Edit</button>
</div>

<!-- Add/Edit Modal -->
<div id="lotDetailsModal" class="modal">
    <div class="modal-content" id="lotDetailsContent">
        <!-- Content will be loaded via JS -->
    </div>
</div>

<script>
function openEditModal(id) {
    fetch('edit_lot_modal.php?id=' + id)
        .then(response => response.text())
        .then(html => {
            document.getElementById('lotDetailsContent').innerHTML = html;
            document.getElementById('lotDetailsModal').style.display = 'block';
        });
}
</script>

