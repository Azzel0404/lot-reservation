<?php
session_start();
include '../config/db.php';

// Get the map_id from the URL parameter
$map_id = isset($_GET['map_id']) ? $_GET['map_id'] : null;
if (!$map_id) {
    header("Location: lots.php");
    exit;
}

// Get map details
$map_query = mysqli_query($conn, "SELECT * FROM map WHERE map_id = '$map_id'");
$map = mysqli_fetch_assoc($map_query);

// Get lots for this map
$lots_query = mysqli_query($conn, "SELECT * FROM lot WHERE map_id = '$map_id' ORDER BY segment_number");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lot Details for Map: <?= htmlspecialchars($map['map_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="layout-wrapper">
    <?php include('sidebar.php'); ?>

    <div class="content-area">
        <div class="top-bar">
            <span>Admin</span>
        </div>

        <div class="container py-4">
            <h2 class="mb-4">Manage Lots for Map: <?= htmlspecialchars($map['map_number']) ?></h2>

            <!-- Button to add a new lot -->
            <a href="add_lot.php?map_id=<?= $map_id ?>" class="btn btn-add mb-4">+ Add Lot</a>

            <div class="row g-4">
                <?php while ($lot = mysqli_fetch_assoc($lots_query)): ?>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="card">
                        <img src="../admin/images/<?= htmlspecialchars($lot['lot_image']) ?>" class="card-img-top" alt="Lot Image">
                        <div class="card-body">
                            <h5 class="card-title">Lot #<?= htmlspecialchars($lot['segment_number']) ?></h5>
                            <p><strong>Size:</strong> <?= $lot['size_meter_square'] ?> sqm</p>
                            <p><strong>Price:</strong> $<?= number_format($lot['price'], 2) ?></p>
                            <p><strong>Status:</strong> <?= $lot['status'] ?></p>

                            <!-- Edit and Delete buttons for each lot -->
                            <a href="edit_lot.php?lot_id=<?= $lot['lot_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_lot.php?lot_id=<?= $lot['lot_id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
