<?php
session_start();
include('../config/db.php');

// Include the navbar
include('navbar.php');

// Fetch all lots from database
$query = "SELECT * FROM lot";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Lots</title>

    <!-- Link to Client CSS -->
    <link rel="stylesheet" href="../client/client.css">

    <!-- Link to Bootstrap (if needed) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="body">
    <!-- Main Content -->
    <div class="container my-5">
        <h2 class="text-center section-title">All Available Lots</h2>
        <div class="row g-4 justify-content-center">
            <?php while ($lot = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-3">
                    <div class="card h-100 shadow">
                        <img src="../admin/lots/uploads/<?php echo htmlspecialchars($lot['aerial_image']); ?>" 
                             class="card-img-top" alt="Lot Image" 
                             style="height: 180px; object-fit: cover;">
                        <div class="card-body">
                            <p class="mb-1"><strong>Lot Number:</strong> <?= htmlspecialchars($lot['lot_number']) ?></p>
                            <p class="mb-1"><strong>Size:</strong> <?= htmlspecialchars($lot['size_meter_square']) ?> sqm</p>
                            <p class="mb-1"><strong>Location:</strong> <?= htmlspecialchars($lot['location']) ?></p>
                            <p class="mb-1">
                                <strong>Status:</strong>
                                <span class="<?= $lot['status'] === 'Available' ? 'status-available' : 'status-reserved' ?>">
                                    <?= htmlspecialchars($lot['status']) ?>
                                </span>
                            </p>
                            <p class="mb-0"><strong>Price:</strong> ₱<?= number_format($lot['price'], 2) ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
