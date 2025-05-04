<?php
session_start();
// Include database connection
include('../config/db.php'); // Ensure this path is correct
include('navbar.php');

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

        // Fetch only the reservations for the logged-in client
        $reservation_query = "SELECT r.reservation_id, c.firstname, c.lastname, l.lot_number, r.reservation_fee, p.payment_method, r.status, r.reservation_date
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

    <link rel="stylesheet" href="client.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

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
                <?php if ($reservations_result): ?>
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
                <?php else: ?>
                    <tr><td colspan="8" class="text-center">No reservations found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


</body>
</html>