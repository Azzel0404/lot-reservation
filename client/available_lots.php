<?php
session_start();
include('../config/db.php');

// Initialize filter variables
$location_filter = $_GET['location'] ?? '';
$size_min = $_GET['size_min'] ?? '';
$size_max = $_GET['size_max'] ?? '';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';

// Build the base query
$query = "SELECT * FROM lot WHERE status = 'Available'";

// Add filters if they exist
$params = [];
if (!empty($location_filter)) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location_filter%";
}
if (!empty($size_min)) {
    $query .= " AND size_meter_square >= ?";
    $params[] = $size_min;
}
if (!empty($size_max)) {
    $query .= " AND size_meter_square <= ?";    
    $params[] = $size_max;
}
if (!empty($price_min)) {
    $query .= " AND price >= ?";
    $params[] = $price_min;
}
if (!empty($price_max)) {
    $query .= " AND price <= ?";    
    $params[] = $price_max;
}   

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ReserveIt - Available Lots</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../client/available_lots.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid px-4">
        <!-- Profile on the left -->
        <div class="d-flex align-items-center profile-left">
            <i class="fas fa-user me-2"></i>
            <span class="profile-text">User</span>
        </div>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Navigation links on the right -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item mx-2">
                    <a class="nav-link" href="../client/index.php">
                        <i class="fas fa-home me-1"></i> <span>Home</span>
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="available_lots.php">
                        <i class="fas fa-th me-1"></i> <span>Lots</span>
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="../client/reservations.php">
                        <i class="fas fa-calendar-check me-1"></i> <span>Reservations</span>
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="../client/profile/profile.php">
                        <i class="fas fa-user-circle"></i> <span>Profile</span>
                    </a>
                </li>
                <li class="nav-item ms-2">
                    <a href="../logout.php" class="btn btn-sm btn-outline-light logout-btn">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </li>
                
            </ul>
        </div>
    </div>
</nav>

<div class="container my-4 pt-3">
    <h2 class="text-center section-title mb-4">Available Lots</h2>
    
<!-- Compact Filter Section with Reset -->
<div class="filter-container mb-4">
    <form method="GET" action="" class="bg-light p-3 rounded">
        <div class="row g-2 align-items-center">
            <!-- Location Filter -->
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-map-marker-alt text-muted"></i></span>
                    <input type="text" class="form-control form-control-sm" name="location" 
                           placeholder="Location" value="<?= htmlspecialchars($location_filter ?? '') ?>">
                </div>
            </div>
            
            <!-- Size Range Filter -->
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-ruler-combined text-muted"></i></span>
                    <input type="number" class="form-control form-control-sm" name="size_min" 
                           placeholder="Min size" min="0" value="<?= htmlspecialchars($size_min ?? '') ?>">
                    <span class="input-group-text bg-white px-1">-</span>
                    <input type="number" class="form-control form-control-sm" name="size_max" 
                           placeholder="Max size" min="0" value="<?= htmlspecialchars($size_max ?? '') ?>">
                </div>
            </div>

            <!-- Price Range Filter -->
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-tag text-muted"></i></span>
                    <input type="number" class="form-control form-control-sm" name="price_min" 
                            placeholder="Min price" min="0" value="<?= htmlspecialchars($price_min ?? '') ?>">
                    <span class="input-group-text bg-white px-1">-</span>
                    <input type="number" class="form-control form-control-sm" name="price_max" 
                            placeholder="Max price" min="0" value="<?= htmlspecialchars($price_max ?? '') ?>">
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="col-md-3">
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <?php if (!empty($location_filter) || !empty($size_min) || !empty($size_max)): ?>
                        <button type="button" onclick="location.href='available_lots.php'" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-undo me-1"></i> Reset
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Active Filters Badges - Only show when filters are active -->
        <?php if (!empty($location_filter) || !empty($size_min) || !empty($size_max)): ?>
        <div class="mt-2">
            <div class="d-flex flex-wrap gap-1 align-items-center">
                <small class="text-muted me-1">Active filters:</small>
                <?php if (!empty($location_filter)): ?>
                        <span class="badge bg-light text-dark border">
                            Location: <?= htmlspecialchars($location_filter) ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['location' => ''])) ?>" class="text-reset ms-1">
                                <i class="fas fa-times small"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($size_min)): ?>
                        <span class="badge bg-light text-dark border">
                            Min: <?= htmlspecialchars($size_min) ?> sqm
                            <a href="?<?= http_build_query(array_merge($_GET, ['size_min' => ''])) ?>" class="text-reset ms-1">
                                <i class="fas fa-times small"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($size_max)): ?>
                        <span class="badge bg-light text-dark border">
                            Max: <?= htmlspecialchars($size_max) ?> sqm
                            <a href="?<?= http_build_query(array_merge($_GET, ['size_max' => ''])) ?>" class="text-reset ms-1">
                                <i class="fas fa-times small"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($price_min)): ?>
                        <span class="badge bg-light text-dark border">
                            Min: ₱<?= number_format(htmlspecialchars($price_min)) ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['price_min' => ''])) ?>" class="text-reset ms-1">
                                <i class="fas fa-times small"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($price_max)): ?>
                        <span class="badge bg-light text-dark border">
                            Max: ₱<?= number_format(htmlspecialchars($price_max)) ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['price_max' => ''])) ?>" class="text-reset ms-1">
                                <i class="fas fa-times small"></i>
                            </a>
                        </span>
                    <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </form>
</div>
    
    <!-- Lot Cards -->
    <div class="row g-4 justify-content-center" id="lots-container">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($lot = mysqli_fetch_assoc($result)): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 lot-card">
                    <div class="card h-100 shadow lot-clickable"
                         data-lot-id="<?= $lot['lot_id'] ?>"
                         style="cursor: pointer;">
                        <div class="position-relative">
                            <img src="../admin/lots/uploads/<?= htmlspecialchars($lot['aerial_image']) ?>"
                                 class="card-img-top" alt="Lot Image">
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-success">Available</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Lot <?= htmlspecialchars($lot['lot_number']) ?></h5>
                            <p class="card-text mb-2">
                                <i class="fas fa-ruler-combined text-muted me-2"></i>
                                <strong>Size:</strong> <?= number_format($lot['size_meter_square']) ?> sqm
                            </p>
                            <p class="card-text mb-2">
                                <i class="fas fa-dollar-sign text-muted me-2"></i>
                                <strong>Price:</strong> ₱<?= number_format($lot['price'], 2) ?>
                            </p>
                            <p class="card-text mb-0">
                                <i class="fas fa-map-pin text-muted me-2"></i>
                                <strong>Location:</strong> <?= htmlspecialchars($lot['location']) ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-top-0 text-end">
                            <small class="text-muted">Click to view details</small>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="alert alert-info py-4">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h4>No lots found matching your criteria</h4>
                    <p class="mb-0">Try adjusting your filters or check back later for new listings</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Lot Details Modal -->
<div class="modal fade" id="lotDetailsModal" tabindex="-1" aria-labelledby="lotDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 650px;">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title fs-5">Lot Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="lotDetailsContent" style="max-height: 400px; overflow-y: auto;">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading lot details...</p>
                </div>
            </div>
            <div class="modal-footer py-2">
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <button type="button" class="btn btn-warning" id="reserveRequestBtn">
                            <i class="fas fa-file-signature me-2"></i>Reserve Request
                        </button>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reserve Request Modal -->
<div class="modal fade" id="reserveRequestModal" tabindex="-1" aria-labelledby="reserveRequestLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title fs-5">Reservation Request Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="ratio ratio-16x9">
                    <!-- PDF Embed Container -->
                    <iframe id="pdfPreview" src="" style="width: 100%; height: 500px;" frameborder="0"></iframe>
                </div>
                <div class="text-center mt-3">
                    <p class="text-muted">Please review the reservation request form before downloading</p>
                </div>
            </div>
            <div class="modal-footer py-3 justify-content-center">
                <a id="downloadPdf" href="#" class="btn btn-primary" download="Reservation_Request_Form.pdf">
                    <i class="fas fa-download me-2"></i> Download Form
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                const lotDetailsModal = new bootstrap.Modal(document.getElementById('lotDetailsModal'));
                lotDetailsModal.show();

                document.getElementById('reserveRequestBtn').addEventListener('click', () => {
                    // Set the PDF source and download link
                    const pdfUrl = 'Lot_Reservation_Request_Agreement.pdf';
                    document.getElementById('pdfPreview').src = pdfUrl;
                    document.getElementById('downloadPdf').href = pdfUrl;

                    // Show the reserve request modal
                    const reserveRequestModal = new bootstrap.Modal(document.getElementById('reserveRequestModal'));
                    reserveRequestModal.show();

                    // Hide the lot details modal
                    lotDetailsModal.hide();
                });
            })
            .catch(error => {
                console.error('Error fetching lot details:', error);
                document.getElementById('lotDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error loading lot details. Please try again.
                    </div>
                `;
            });
    });
});
</script>

</body>
</html>