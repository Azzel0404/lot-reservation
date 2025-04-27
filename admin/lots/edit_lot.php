<!--admin/lots/edit_lot.php-->

<?php
session_start();
include '../../config/db.php';

// Fetch the lot_id from the URL parameter
$lot_id = isset($_GET['lot_id']) ? $_GET['lot_id'] : null;
if (!$lot_id) {
    header("Location: lots.php");
    exit;
}

// Fetch the lot details
$lot_query = mysqli_query($conn, "SELECT * FROM lot WHERE lot_id = '$lot_id'");
$lot = mysqli_fetch_assoc($lot_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $segment_number = mysqli_real_escape_string($conn, $_POST['segment_number']);
    $size_meter_square = mysqli_real_escape_string($conn, $_POST['size_meter_square']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $lot_image = $_FILES['lot_image']['name'];

    // Handle image upload if a new one is selected
    if ($lot_image) {
        $lot_image_path = '../admin/images/' . time() . '-' . basename($lot_image);
        if (move_uploaded_file($_FILES['lot_image']['tmp_name'], $lot_image_path)) {
            // Delete the old lot image if it exists
            if ($lot['lot_image']) {
                unlink('../admin/images/' . $lot['lot_image']);
            }

            $query = "UPDATE lot SET segment_number = '$segment_number', size_meter_square = '$size_meter_square', 
                      price = '$price', status = '$status', lot_image = '$lot_image' WHERE lot_id = '$lot_id'";
        } else {
            $error = "Failed to upload the new image.";
        }
    } else {
        // If no new image, just update the lot details
        $query = "UPDATE lot SET segment_number = '$segment_number', size_meter_square = '$size_meter_square', 
                  price = '$price', status = '$status' WHERE lot_id = '$lot_id'";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: lot_details.php?map_id=" . $lot['map_id']);
        exit;
    } else {
        $error = "Database Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Lot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="layout-wrapper">
    <?php include('sidebar.php'); ?>

    <div class="content-area">
        <div class="top-bar">
            <span>Admin</span>
        </div>

        <div class="container py-4">
            <h2 class="mb-4">Edit Lot #<?= $lot['segment_number'] ?> for Map</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form action="edit_lot.php?lot_id=<?= $lot_id ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="segment_number" class="form-label">Segment Number:</label>
                    <input type="number" class="form-control" id="segment_number" name="segment_number" value="<?= $lot['segment_number'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="size_meter_square" class="form-label">Size (sqm):</label>
                    <input type="number" class="form-control" id="size_meter_square" name="size_meter_square" value="<?= $lot['size_meter_square'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price:</label>
                    <input type="number" class="form-control" id="price" name="price" value="<?= $lot['price'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status:</label>
                    <select class="form-control" id="status" name="status">
                        <option value="Available" <?= $lot['status'] == 'Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Reserved" <?= $lot['status'] == 'Reserved' ? 'selected' : '' ?>>Reserved</option>
                        <option value="Sold" <?= $lot['status'] == 'Sold' ? 'selected' : '' ?>>Sold</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="lot_image" class="form-label">Lot Image:</label>
                    <input type="file" class="form-control" id="lot_image" name="lot_image">
                    <?php if ($lot['lot_image']): ?>
                        <img src="../admin/images/<?= $lot['lot_image'] ?>" alt="Current Image" class="mt-2" width="200">
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-warning">Update Lot</button>
                <a href="lot_details.php?map_id=<?= $lot['map_id'] ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
