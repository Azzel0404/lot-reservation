<?php
include('../config/db.php');

if (!isset($_GET['lot_id'])) {
    echo "<p>Invalid request.</p>";
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

<div class="row">
    <div class="col-md-6">
        <?php if (!empty($lot['aerial_image'])): ?>
            <img src="../admin/lots/uploads/<?= htmlspecialchars($lot['aerial_image']) ?>" class="img-fluid mb-2" alt="Aerial Image">
        <?php endif; ?>
        <?php if (!empty($lot['numbered_image'])): ?>
            <img src="../admin/lots/uploads/<?= htmlspecialchars($lot['numbered_image']) ?>" class="img-fluid" alt="Numbered Image">
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <p><strong>Lot Number:</strong> <?= htmlspecialchars($lot['lot_number']) ?></p>
        <p><strong>Location:</strong> <?= htmlspecialchars($lot['location']) ?></p>
        <p><strong>Size:</strong> <?= $lot['size_meter_square'] ?> sqm</p>
        <p><strong>Price:</strong> ₱<?= number_format($lot['price'], 2) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($lot['status']) ?></p>
        <?php if (!empty($lot['pdf_file'])): ?>
            <p><strong>Download the form for reservation:</strong> <a href="../admin/lots/uploads/<?= htmlspecialchars($lot['pdf_file']) ?>" download>Download PDF</a></p>
        <?php endif; ?>
    </div>
</div>
