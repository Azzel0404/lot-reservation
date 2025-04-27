<!-- admin/lots.php -->
<?php
session_start();
include '../config/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lot Map Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="layout-wrapper">
    <?php include('sidebar.php'); ?>

    <div class="content-area">
        <div class="top-bar">
            <span>Admin</span>
            <i class="fas fa-user-cog"></i>
        </div>

        <div class="container py-4">
            <h2 class="mb-4">Lot Map Management</h2>

            <button class="btn btn-add mb-4" onclick="openAddModal()">+ Add Lot Map</button>

            <div class="row g-4">
                <?php
                $maps = mysqli_query($conn, "SELECT * FROM map ORDER BY created_at DESC");
                while ($map = mysqli_fetch_assoc($maps)):
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card-hover h-100">
                        <div class="map-img">
                            <img src="../admin/images/<?= htmlspecialchars(basename($map['map_layout'])) ?>" alt="Map Image">
                        </div>
                        <div class="map-info">
                            <div class="map-title"><?= htmlspecialchars($map['map_number']) ?></div>
                            <div class="map-subtext"><?= htmlspecialchars($map['location']) ?></div>
                            <a href="#" class="btn btn-primary btn-sm mt-3" onclick="openMapModal(<?= $map['map_id'] ?>)">View Details</a>
                        </div>
                    </div>
                </div>

                <!-- Modal -->
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
                <?php endwhile; ?>
            </div>
        </div>

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
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openMapModal(id) {
    document.getElementById('mapModal' + id).style.display = 'flex';
}
function closeMapModal(id) {
    document.getElementById('mapModal' + id).style.display = 'none';
}
function openAddModal() {
    document.getElementById('addLotModal').style.display = 'flex';
}
function closeAddModal() {
    document.getElementById('addLotModal').style.display = 'none';
}
</script>

</body>
</html>

<?php $conn->close(); ?>
