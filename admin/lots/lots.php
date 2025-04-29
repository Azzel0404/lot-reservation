<!-- admin/lots/lots.php -->
<?php include('../../config/db.php'); ?>
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
                        echo "<img src='uploads/{$row['aerial_image']}' class='lot-image'>";
                        echo "<div class='lot-info'>";
                        echo "<h3>{$row['lot_number']}</h3>";
                        echo "<p>{$row['location']}</p>";
                        echo "<p>₱" . number_format($row['price'], 2) . "</p>";
                        echo "</div></div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddModal()">&times;</span>
        <?php include('create_lot.php'); ?>
    </div>
</div>

<!-- Details Modal -->
<div id="lotDetailsModal" class="modal">
    <div class="modal-content" id="lotDetailsContent">
        <span class="close" onclick="closeLotDetailsModal()">&times;</span>
        <!-- Content will be loaded via JS -->
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}
function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}
function closeLotDetailsModal() {
    document.getElementById('lotDetailsModal').style.display = 'none';
}

function openLotDetails(id) {
    fetch('edit_lot_form.php?id=' + id)
        .then(response => response.text())
        .then(html => {
            document.getElementById('lotDetailsContent').innerHTML = html;
            document.getElementById('lotDetailsModal').style.display = 'block';
        });
}
</script>

</body>
</html>
