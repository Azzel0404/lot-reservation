<!--client/index.php-->

<?php
session_start();
require_once '../config/db.php';

$recommended_lots = []; // Initialize an empty array to store the fetched lots

try {
    // Check database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT lot_id, lot_number, size_meter_square, location, aerial_image
            FROM lot
            WHERE status = 'Available'
            ORDER BY RAND()
            LIMIT 4";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $recommended_lots[] = $row;
        }
    }

    $conn->close(); // Close the database connection
} catch (Exception $e) {
    // Handle any database errors
    echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link rel="stylesheet" href="../client/clients2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
        }
        
        body {
            background-color: #f8f9fc;
            padding-top: 60px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .hero {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 6rem 1rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .hero h1 {
            font-weight: 600;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--primary-color);
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .card-img-top {
            height: 180px;
            object-fit: cover;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .status-available {
            color: #28a745;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: var(--accent-color);
        }
    </style>
</head>
<body class="body">

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
                    <a class="nav-link" href="#">
                        <i class="fas fa-home me-1"></i> <span>Home</span>
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="../client/lots/available_lots.php">
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

<div class="hero">
    <div class="container">
        <h1>Welcome to Lot Reservation System</h1>
        <p class="lead">Find and reserve your perfect lot with ease</p>
    </div>
</div>

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

<div class="modal fade" id="reserveRequestModal" tabindex="-1" aria-labelledby="reserveRequestLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title fs-5">Reservation Request Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="ratio ratio-16x9">
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

<div class="container my-5">
    <div class="bg-white p-4 rounded-3 shadow-sm mb-5">
    <h2 class="text-center section-title">Recommended For You</h2>
    <div class="row g-4">
        <?php if (empty($recommended_lots)): ?>
            <div class="col-12 text-center">No recommended lots available at the moment.</div>
        <?php else: ?>
            <?php foreach ($recommended_lots as $lot): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 lot-clickable" data-lot-id="<?= htmlspecialchars($lot['lot_id'] ?? '') ?>" style="cursor: pointer;">
                        <img src="../admin/lots/uploads/<?= htmlspecialchars($lot['aerial_image'] ?? '../img/default_lot.jpg') ?>" class="card-img-top" alt="Lot Image">
                        <div class="card-body">
                            <h5 class="card-title">Lot #<?= htmlspecialchars($lot['lot_number'] ?? 'N/A') ?></h5>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-ruler-combined text-muted me-2"></i> <?= htmlspecialchars($lot['size_meter_square'] ?? 'N/A') ?> sqm</li>
                                <li class="mb-2"><i class="fas fa-map-marker-alt text-muted me-2"></i> <?= htmlspecialchars($lot['location'] ?? 'N/A') ?></li>
                                <li><span class="status-available"><i class="fas fa-check-circle me-2"></i> Available</span></li>
                            </ul>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 text-center">
                            <small class="text-muted">Click to view details</small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.lot-clickable').forEach(card => {
    card.addEventListener('click', () => {
        const lotId = card.getAttribute('data-lot-id');
        fetch(`fetch_lot_details.php?lot_id=${lotId}`) // Adjust the path here
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
