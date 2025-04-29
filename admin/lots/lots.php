<!-- admin/lots/lots.php -->
<?php include '../sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lot Management</title>
    <link rel="stylesheet" href="lots.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
    <i class="fas fa-user-shield"></i>
    <span>Admin</span>
</div>

<!-- Main Content Area -->
<div class="main-content">
    <div class="container">
        <h1>Lot Management</h1>

        <!-- Add Lot Button -->
        <button class="btn btn-add" onclick="openModal()">+ Add New Lot</button>

        <!-- Lot Table -->
        <table class="table">
            <thead>
                <tr>
                    <th>Lot Name</th>
                    <th>Location</th>
                    <th>Size</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample Static Row -->
                <tr>
                    <td>Lot A</td>
                    <td>Phase 1</td>
                    <td>100 sqm</td>
                    <td>Available</td>
                    <td>
                        <button class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <!-- Repeat rows here -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="lotModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Add New Lot</h2>
        <form>
            <label for="lotName">Lot Name</label>
            <input type="text" id="lotName" name="lotName" required><br><br>

            <label for="location">Location</label>
            <input type="text" id="location" name="location" required><br><br>

            <label for="size">Size (sqm)</label>
            <input type="number" id="size" name="size" required><br><br>

            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="Available">Available</option>
                <option value="Reserved">Reserved</option>
                <option value="Sold">Sold</option>
            </select><br><br>

            <button type="submit" class="btn btn-primary">Save Lot</button>
        </form>
    </div>
</div>

<!-- Basic JS for Modal -->
<script>
function openModal() {
    document.getElementById("lotModal").style.display = "block";
}
function closeModal() {
    document.getElementById("lotModal").style.display = "none";
}
window.onclick = function(event) {
    if (event.target == document.getElementById("lotModal")) {
        closeModal();
    }
}
</script>

</body>
</html>
