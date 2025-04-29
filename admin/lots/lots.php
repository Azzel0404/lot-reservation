<!-- admin/lots/lots.php -->
<?php
include('../../config/db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lot Management</title>
    <link rel="stylesheet" href="lots.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    <?php include('../sidebar.php'); ?>

    <main class="main-content">
        <!-- Top Bar -->
        <header class="top-bar">
            <span>Admin</span>
            <i class="fas fa-user-cog"></i>
        </header>

        <div class="content-wrapper">
            <div class="container">
                <h1>Lot Management</h1>

                <button class="btn-add" onclick="openAddModal()">Add New Lot</button>

                <div class="lot-grid">
                    <?php
                    $sql = "SELECT * FROM lot";
                    $result = mysqli_query($conn, $sql);

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div class='lot-card' onclick='openLotDetails({$row['lot_id']})'>";
                        echo "<img src='uploads/{$row['aerial_image']}' alt='Lot Image' class='lot-image'>";
                        echo "<div class='lot-info'>";
                        echo "<h3>{$row['lot_number']}</h3>";
                        echo "<p>{$row['location']}</p>";
                        echo "<p>₱" . number_format($row['price'], 2) . "</p>";
                        echo "</div>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add Lot Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddModal()">&times;</span>
        <?php include('create_lot.php'); ?>
    </div>
</div>

<!-- Lot Details Modal -->
<div id="lotDetailsModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeLotDetailsModal()">&times;</span>
        <div id="lotDetailsContent">
            <!-- Lot details will be loaded dynamically -->
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}
function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}
function openLotDetails(lotId) {
    // Fetch lot details from the server using AJAX
    fetch('get_lot_details.php?id=' + lotId)
        .then(response => response.json())
        .then(data => {
            document.getElementById('lotDetailsContent').innerHTML = `
                <h2>Lot Details</h2>
                <p><strong>Lot Number:</strong> ${data.lot_number}</p>
                <p><strong>Location:</strong> ${data.location}</p>
                <p><strong>Size:</strong> ${data.size_meter_square} m²</p>
                <p><strong>Price:</strong> ₱${data.price}</p>
                <p><strong>Status:</strong> ${data.status}</p>
                <p><strong>Aerial Image:</strong><br><img src="uploads/${data.aerial_image}" width="200" /></p>
                <p><strong>Numbered Image:</strong><br><img src="uploads/${data.numbered_image}" width="200" /></p>
            `;
            document.getElementById('lotDetailsModal').style.display = 'block';
        });
}
function closeLotDetailsModal() {
    document.getElementById('lotDetailsModal').style.display = 'none';
}
</script>

</body>
</html>
