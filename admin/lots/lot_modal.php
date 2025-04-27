<!-- admin/lot_map_modal.php -->
<?php
// Assuming you are passing the $map data as a variable to this file
?>

<!-- Add Lot Modal -->
<div id="addLotModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddModal()">✖</span>
        <h2>Add Lot Map</h2>
        <form action="add_map.php" method="POST" enctype="multipart/form-data">
            <label for="map_number">Map Number:</label><br>
            <input type="text" id="map_number" name="map_number" required><br><br>

            <label for="location">Location:</label><br>
            <input type="text" id="location" name="location" required><br><br>

            <label for="map_layout">Map Layout:</label><br>
            <input type="file" id="map_layout" name="map_layout" accept="image/*" required><br><br>

            <button type="submit" class="btn btn-success">Save</button>
            <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
        </form>
    </div>
</div>

<!-- Edit Lot Modal -->
<div id="editLotModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">✖</span>
        <h2>Edit Lot Map</h2>
        <form id="editLotForm" action="edit_map.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="edit_map_id" name="map_id">
            <label for="edit_map_number">Map Number:</label><br>
            <input type="text" id="edit_map_number" name="map_number" required><br><br>

            <label for="edit_location">Location:</label><br>
            <input type="text" id="edit_location" name="location" required><br><br>

            <label for="edit_map_layout">Map Layout:</label><br>
            <input type="file" id="edit_map_layout" name="map_layout" accept="image/*"><br><br>

            <button type="submit" class="btn btn-success">Save Changes</button>
            <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
        </form>
    </div>
</div>

<!-- View Map Modal (for each map) -->
<?php foreach ($maps as $map): ?>
<div id="mapModal<?= $map['map_id'] ?>" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeMapModal(<?= $map['map_id'] ?>)">✖</span>
        <img src="../admin/images/<?= htmlspecialchars(basename($map['map_layout'])) ?>" alt="Map Image">
        <div class="details">
            <h2><?= htmlspecialchars($map['map_number']) ?></h2>
            <p><strong>Location:</strong> <?= htmlspecialchars($map['location']) ?></p>
            <p><strong>Map ID:</strong> <?= $map['map_id'] ?></p>
        </div>
    </div>
</div>
<?php endforeach; ?>
