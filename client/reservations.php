<!--client/reservations.php-->
<?php
// Include database connection
include('../config/db.php');  // Ensure this path is correct
include('navbar.php');

// Fetch all reservations
$reservation_query = "SELECT r.reservation_id, c.firstname, c.lastname, l.lot_number, r.reservation_fee, p.payment_method, r.status, r.reservation_date
                      FROM reservation r
                      JOIN client c ON r.client_id = c.client_id
                      JOIN lot l ON r.lot_id = l.lot_id
                      JOIN payment p ON r.payment_id = p.payment_id
                      ORDER BY r.reservation_date DESC";

$reservations_result = mysqli_query($conn, $reservation_query);

// Error check
if (!$reservations_result) {
    die("Error fetching reservations: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Reservation Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSS -->
    <link rel="stylesheet" href="client.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Main Content -->
<div class="container mt-5 pt-4">
    <h2 class="mb-4">Your Reservations</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Reservation ID</th>
                    <th>Client Name</th>
                    <th>Lot Number</th>
                    <th>Reservation Fee (₱)</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Reservation Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($reservation = mysqli_fetch_assoc($reservations_result)): ?>
                    <tr>
                        <td><?php echo $reservation['reservation_id']; ?></td>
                        <td><?php echo $reservation['firstname'] . " " . $reservation['lastname']; ?></td>
                        <td><?php echo $reservation['lot_number']; ?></td>
                        <td>₱<?php echo number_format($reservation['reservation_fee'], 2); ?></td>
                        <td><?php echo $reservation['payment_method']; ?></td>
                        <td><?php echo $reservation['status']; ?></td>
                        <td><?php echo date('F j, Y g:i A', strtotime($reservation['reservation_date'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
