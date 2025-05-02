<!--lot-reservation/client/lot_details.php-->

<?php
session_start();
include('../config/db.php');

if (!isset($_GET['lot_id'])) {
    header("Location: available_lots.php");
    exit();
}

$lot_id = intval($_GET['lot_id']);
$stmt = $conn->prepare("SELECT * FROM lot WHERE lot_id = ?");
$stmt->bind_param("i", $lot_id);
$stmt->execute();
$result = $stmt->get_result();
$lot = $result->fetch_assoc();
$stmt->close();

if (!$lot) {
    echo "<p>Lot not found.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lot Details</title>
    <link rel="stylesheet" href="client.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="body">
<div class="container my-5">
    <a href="available_lots.php" class="btn btn-secondary mb-4">← Back to Lots</a>
    <div class="row">
        <div class="col-md-6">
            <img src="../admin/lots/uploads/<?= htmlspecialchars($lot['aerial_image']) ?>" class="img-fluid mb-3" alt="Aerial Image">
            <img src="../admin/lots/uploads/<?= htmlspecialchars($lot['numbered_image']) ?>" class="img-fluid" alt="Numbered Image">
        </div>
        <div class="col-md-6">
            <h3>Lot <?= htmlspecialchars($lot['lot_number']) ?></h3>
            <p><strong>Location:</strong> <?= htmlspecialchars($lot['location']) ?></p>
            <p><strong>Size:</strong> <?= $lot['size_meter_square'] ?> sqm</p>
            <p><strong>Price:</strong> ₱<?= number_format($lot['price'], 2) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($lot['status']) ?></p>
            <?php if (!empty($lot['pdf_file'])): ?>
                <p><strong>PDF Details:</strong> 
                    <a href="../admin/lots/uploads/<?= htmlspecialchars($lot['pdf_file']) ?>" target="_blank">View File</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
