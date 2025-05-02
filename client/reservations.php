    <!--client/reservations.php-->
    <?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';
include('navbar.php');

// Make sure the user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'CLIENT') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the client_id for this user
$client_query = $conn->prepare("SELECT client_id FROM client WHERE user_id = ?");
$client_query->bind_param("i", $user_id);
$client_query->execute();
$client_query->bind_result($client_id);
$client_query->fetch();
$client_query->close();

// Fetch only the reservations for this client
$reservation_query = $conn->prepare("
    SELECT r.reservation_id, c.firstname, c.lastname, l.lot_number, r.reservation_fee, 
           p.payment_method, r.status, r.reservation_date
    FROM reservation r
    JOIN client c ON r.client_id = c.client_id
    JOIN lot l ON r.lot_id = l.lot_id
    JOIN payment p ON r.payment_id = p.payment_id
    WHERE r.client_id = ?
    ORDER BY r.reservation_date DESC
");

$reservation_query->bind_param("i", $client_id);
$reservation_query->execute();
$reservations_result = $reservation_query->get_result();
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
                <?php while ($reservation = $reservations_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($reservation['reservation_id']) ?></td>
                        <td><?= htmlspecialchars($reservation['firstname'] . " " . $reservation['lastname']) ?></td>
                        <td><?= htmlspecialchars($reservation['lot_number']) ?></td>
                        <td>₱<?= number_format($reservation['reservation_fee'], 2) ?></td>
                        <td><?= htmlspecialchars($reservation['payment_method']) ?></td>
                        <td><?= htmlspecialchars($reservation['status']) ?></td>
                        <td><?= date('F j, Y g:i A', strtotime($reservation['reservation_date'])) ?></td>
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

