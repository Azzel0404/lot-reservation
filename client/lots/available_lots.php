<?php
session_start();
include('../../config/db.php');

// Initialize filter variables
$location_filter = $_GET['location'] ?? '';
$size_min = $_GET['size_min'] ?? '';
$size_max = $_GET['size_max'] ?? '';

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
    <link rel="stylesheet" href="../lots/available_lots.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <!-- Welcome message on the left -->
        <span class="text-white d-none d-md-block">Welcome, <?= $_SESSION['username'] ?? 'Guest' ?></span>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Navigation links moved to the right -->
            <ul class="navbar-nav ms-auto me-3">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="available_lots.php">
                        <i class="fas fa-th me-1"></i> Lots
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../reservations.php">
                        <i class="fas fa-calendar-check me-1"></i>Reservations
                    </a>
                </li>
            </ul>
            
            <!-- User dropdown on the far right -->
            <div class="dropdown">
                <div class="avatar" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="../profile/profile.php"><i class="fas fa-user-cog me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="../../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container my-4 pt-3">
    <h2 class="text-center section-title mb-4">Available Lots</h2>
    
    <!-- Minimalistic Filter -->
    <div class="mini-filter">
        <form method="GET" action="">
            <div class="row g-3 filter-row">
                <!-- Location -->
                <div class="col-md-4 col-sm-6">
                    <label for="location">Location</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-map-marker-alt text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="location" name="location" 
                               placeholder="Search location" value="<?= htmlspecialchars($location_filter ?? '') ?>">
                    </div>
                </div>
                
                <!-- Size Range -->
                <div class="col-md-5 col-sm-6">
                    <label for="size_min">Size (sqm)</label>
                    <div class="size-range">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-ruler text-muted"></i>
                            </span>
                            <input type="number" class="form-control border-start-0" id="size_min" name="size_min" 
                                   placeholder="Min" value="<?= htmlspecialchars($size_min ?? '') ?>">
                        </div>
                        <span class="size-divider">-</span>
                        <input type="number" class="form-control" id="size_max" name="size_max" 
                               placeholder="Max" value="<?= htmlspecialchars($size_max ?? '') ?>">
                    </div>
                </div>
                
                <!-- Buttons -->
                <div class="col-md-3 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-mini-filter flex-grow-1">
                            <i class="fas fa-filter me-2"></i> Filter
                        </button>
                        <a href="available_lots.php" class="btn btn-mini-reset flex-grow-1">
                            <i class="fas fa-times me-2"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
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
        fetch(`../fetch_lot_details.php?lot_id=${lotId}`)
            .then(res => res.text())
            .then(data => {
                document.getElementById('lotDetailsContent').innerHTML = data;
                const lotDetailsModal = new bootstrap.Modal(document.getElementById('lotDetailsModal'));
                lotDetailsModal.show();

                document.getElementById('reserveRequestBtn').addEventListener('click', () => {
                    // Set the PDF source and download link
                    const pdfUrl = '../Lot_Reservation_Request_Agreement.pdf';
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