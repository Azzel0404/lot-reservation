<!-- lot-reservation/client/reservations.php -->
<?php
session_start();
// Include database connection
include('../config/db.php'); // Ensure this path is correct

// Check if the user is logged in and has a user ID in the session
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.php");
    exit();
}

// Get the logged-in user's ID from the session
$user_id = $_SESSION['user_id'];

// Fetch the client_id associated with the logged-in user
$client_id_query = "SELECT client_id FROM client WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $client_id_query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $client_id_result = mysqli_stmt_get_result($stmt);

    if ($client_id = mysqli_fetch_assoc($client_id_result)) {
        $current_client_id = $client_id['client_id'];

        // Fetch only the reservations for the logged-in client, removing expiry_date
        $reservation_query = "SELECT r.reservation_id, c.firstname, c.lastname, l.lot_number, r.reservation_fee, p.payment_method, 
                                r.status, r.reservation_date, l.size_meter_square, r.date_approved
                                FROM reservation r
                                JOIN client c ON r.client_id = c.client_id
                                JOIN lot l ON r.lot_id = l.lot_id
                                JOIN payment p ON r.payment_id = p.payment_id
                                WHERE r.client_id = ?
                                ORDER BY r.reservation_date DESC";

        $reservations_stmt = mysqli_prepare($conn, $reservation_query);

        if ($reservations_stmt) {
            mysqli_stmt_bind_param($reservations_stmt, "i", $current_client_id);
            mysqli_stmt_execute($reservations_stmt);
            $reservations_result = mysqli_stmt_get_result($reservations_stmt);

            // Error check
            if (!$reservations_result) {
                die("Error fetching reservations: " . mysqli_error($conn));
            }
        } else {
            die("Error preparing reservation statement: " . mysqli_error($conn));
        }
        mysqli_stmt_close($reservations_stmt);

    } else {
        // Handle the case where the user ID doesn't have a corresponding client ID
        echo "<p class='alert alert-warning'>No client information found for this user.</p>";
        $reservations_result = null; // To prevent errors in the while loop
    }
    mysqli_stmt_close($stmt);

} else {
    die("Error preparing client ID statement: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Reservations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            padding-top: 70px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 1200px;
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            border: none;
            padding: 12px 15px;
        }
        
        .table tbody tr {
            background-color: white;
            transition: background-color 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: #f2f6ff;
        }
        
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
            border-top: 1px solid #eee;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .action-btn {
            padding: 5px 10px;
            font-size: 0.85rem;
        }

        /* Fix for lingering backdrop */
        .modal-backdrop {
            display: none !important;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center profile-left">
            <i class="fas fa-user me-2"></i>
            <span class="profile-text">User</span>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item mx-2">
                    <a class="nav-link" href="../client/index.php">
                        <i class="fas fa-home me-1"></i> <span>Home</span>
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="../client/lots/available_lots.php">
                        <i class="fas fa-th me-1"></i> <span>Lots</span>
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="#">
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

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title mb-0">Your Reservations</h2>
    </div>

    <?php if ($reservations_result && mysqli_num_rows($reservations_result) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Lot Reserved</th>
                        <th>Reservation Fee (₱)</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Date</th>   
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($reservation = mysqli_fetch_assoc($reservations_result)): 
                        // Determine status class
                        $status_class = '';
                        if (strtolower($reservation['status']) == 'approved') {
                            $status_class = 'status-approved';
                        } elseif (strtolower($reservation['status']) == 'pending') {
                            $status_class = 'status-pending';
                        } else {
                            $status_class = 'status-cancelled';
                        }
                    ?>
                    <tr>
                        <td><?php echo $reservation['firstname'] . " " . $reservation['lastname']; ?></td>
                        <td><?php echo $reservation['lot_number']; ?></td>
                        <td>₱<?php echo number_format($reservation['reservation_fee'], 2); ?></td>
                        <td><?php echo $reservation['payment_method']; ?></td>
                        <td><span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($reservation['status']); ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?></td>
                        <td>
                            <button type="button" class="btn btn-info action-btn view-details-btn" data-bs-toggle="modal" data-bs-target="#reservationDetailsModal"
                                    data-lot="<?php echo $reservation['lot_number']; ?>"
                                    data-size="<?php echo $reservation['size_meter_square']; ?>"
                                    data-date="<?php echo $reservation['reservation_date']; ?>"
                                    data-approved="<?php echo $reservation['date_approved']; ?>"
                                    data-fee="<?php echo $reservation['reservation_fee']; ?>"
                                    data-payment="<?php echo $reservation['payment_method']; ?>"
                                    data-status="<?php echo $reservation['status']; ?>">
                                View Details
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h4>No reservations found.</h4>
        </div>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="reservationDetailsModal" tabindex="-1" aria-labelledby="reservationDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reservationDetailsModalLabel">Reservation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="modalLot" class="form-label">Lot Reserved</label>
                        <input type="text" class="form-control" id="modalLot" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="modalSize" class="form-label">Lot Size</label>
                        <input type="text" class="form-control" id="modalSize" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="modalDate" class="form-label">Reservation Date</label>
                        <input type="text" class="form-control" id="modalDate" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="modalApproved" class="form-label">Approval Date</label>
                        <input type="text" class="form-control" id="modalApproved" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="modalFee" class="form-label">Reservation Fee</label>
                        <input type="text" class="form-control" id="modalFee" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="modalPayment" class="form-label">Payment Method</label>
                        <input type="text" class="form-control" id="modalPayment" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="modalStatus" class="form-label">Status</label>
                        <input type="text" class="form-control" id="modalStatus" readonly>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const viewButtons = document.querySelectorAll('.view-details-btn');
        const modal = document.getElementById('reservationDetailsModal');
        
        viewButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                // Set modal input values based on button data attributes
                document.getElementById('modalLot').value = this.dataset.lot;
                document.getElementById('modalSize').value = this.dataset.size;
                document.getElementById('modalDate').value = this.dataset.date;
                document.getElementById('modalApproved').value = this.dataset.approved;
                document.getElementById('modalFee').value = this.dataset.fee;
                document.getElementById('modalPayment').value = this.dataset.payment;
                document.getElementById('modalStatus').value = this.dataset.status;
                
                const reservationDetailsModal = new bootstrap.Modal(modal);
                reservationDetailsModal.show();
            });
        });

        // Ensure modal backdrop is removed on close
        const closeButton = modal.querySelector('.btn-close');
        closeButton.addEventListener('click', function () {
            const reservationDetailsModal = new bootstrap.Modal(modal);
            reservationDetailsModal.hide();

            // Manually dispose of the modal to ensure cleanup
            reservationDetailsModal.dispose();
            
            // Remove any active backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        });
    });
</script>

</body>
</html>
