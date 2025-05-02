<!--lot-reservation/client/available_lots.php-->

<?php
session_start();
include('../config/db.php');
include('navbar.php');

// Fetch all available lots
$query = "SELECT * FROM lot WHERE status = 'Available'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Lots</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../client/client.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="body">

<div class="container my-5">
    <h2 class="text-center section-title">Available Lots</h2>
    <div class="row g-4 justify-content-center">
        <?php while ($lot = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-3">
                <div class="card h-100 shadow lot-card lot-clickable"
                     data-lot-id="<?= $lot['lot_id'] ?>"
                     style="cursor: pointer;">
                    <img src="../admin/lots/uploads/<?= htmlspecialchars($lot['aerial_image']) ?>"
                         class="card-img-top" alt="Lot Image"
                         style="height: 180px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">Lot <?= htmlspecialchars($lot['lot_number']) ?></h5>
                        <p class="card-text mb-1"><strong>Size:</strong> <?= $lot['size_meter_square'] ?> sqm</p>
                        <p class="card-text mb-0"><strong>Location:</strong> <?= htmlspecialchars($lot['location']) ?></p>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="lotDetailsModal" tabindex="-1" aria-labelledby="lotDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lot Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="lotDetailsContent">
                <p class="text-center">Loading...</p>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.lot-clickable').forEach(card => {
        card.addEventListener('click', () => {
            const lotId = card.getAttribute('data-lot-id');
            fetch(`fetch_lot_details.php?lot_id=${lotId}`)
                .then(res => res.text())
                .then(data => {
                    document.getElementById('lotDetailsContent').innerHTML = data;
                    const modal = new bootstrap.Modal(document.getElementById('lotDetailsModal'));
                    modal.show();
                });
        });
    });
</script>

</body>
</html>
