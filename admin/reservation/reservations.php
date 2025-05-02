<!--lot-reservation/admin/reservations.php-->
<?php
session_start();
include('../../config/db.php');

// Fetch clients, lots, and payment methods for dropdowns
$clients_result = mysqli_query($conn, "SELECT * FROM client");
$lots_result = mysqli_query($conn, "SELECT * FROM lot WHERE status = 'Available'");
$payment_methods = ['Cash', 'Credit'];

// Handle form submission for reservation
if (isset($_POST['submit_reservation'])) {
    $client_id = $_POST['client_id'];
    $lot_id = $_POST['lot_id'];
    $payment_method = $_POST['payment_method'];
    $reservation_fee = $_POST['reservation_fee'];

    // Fetch payment_id based on the selected payment method
    $payment_query = "SELECT payment_id FROM payment WHERE payment_method = '$payment_method'";

    $payment_result = mysqli_query($conn, $payment_query);

    if ($payment_result && mysqli_num_rows($payment_result) > 0) {
        // If payment method exists, fetch payment_id
        $payment = mysqli_fetch_assoc($payment_result);
        $payment_id = $payment['payment_id'];

        // Get the current date for reservation_date
        $reservation_date = date('Y-m-d H:i:s'); // This is automatically managed by the system
        
        // Set the date_approved and expiry_date to NULL initially
        $date_approved = NULL;
        $expiry_date = NULL;

        // Now, insert the reservation with the system-managed reservation_date
        $stmt = $conn->prepare("INSERT INTO reservation (client_id, lot_id, payment_id, reservation_fee, status, reservation_date, date_approved, expiry_date) 
                                VALUES (?, ?, ?, ?, 'Approved', ?, ?, ?)");
        $stmt->bind_param("iiidsss", $client_id, $lot_id, $payment_id, $reservation_fee, $reservation_date, $date_approved, $expiry_date);

        if ($stmt->execute()) {
            // Update lot status to "Reserved"
            mysqli_query($conn, "UPDATE lot SET status = 'Reserved' WHERE lot_id = $lot_id");

            $_SESSION['success'] = "Reservation created successfully.";
            header("Location: reservations.php");
            exit();
        } else {
            $_SESSION['error'] = "Error: " . $stmt->error;
            header("Location: reservations.php");
            exit();
        }
    } else {
        // If payment method is not found
        $_SESSION['error'] = "Error: Payment method '$payment_method' not found in the database.";
        header("Location: reservations.php");
        exit();
    }
}

// Fetch existing reservations to display in the list
$reservation_query = "SELECT r.reservation_id, c.firstname, c.lastname, l.lot_number, r.reservation_fee, p.payment_method, r.status, r.reservation_date, r.date_approved, r.expiry_date
                      FROM reservation r
                      JOIN client c ON r.client_id = c.client_id
                      JOIN lot l ON r.lot_id = l.lot_id
                      JOIN payment p ON r.payment_id = p.payment_id
                      ORDER BY r.reservation_date DESC";
$reservations_result = mysqli_query($conn, $reservation_query);

// Handle approval of reservation and setting of expiry date
if (isset($_POST['approve_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $date_approved = date('Y-m-d H:i:s'); // current timestamp
    $expiry_date = date('Y-m-d H:i:s', strtotime('+30 days')); // 30 days from now

    // Update reservation approval and expiry date
    $stmt = $conn->prepare("UPDATE reservation SET date_approved = ?, expiry_date = ?, status = 'Approved' WHERE reservation_id = ?");
    $stmt->bind_param("sss", $date_approved, $expiry_date, $reservation_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Reservation approved successfully.";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }

    header("Location: reservations.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations</title>
    <link rel="stylesheet" href="reservations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    <?php include('../sidebar.php'); ?>

    <main class="main-content" style="margin-left: 500px;">
        <header class="top-bar">
            <span>Admin</span>
            <i class="fas fa-user-cog"></i>
        </header>

        <div class="content-wrapper">
            <div class="container">
                <h1>Create Reservation</h1>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <!-- Reservation Form -->
                <form action="reservations.php" method="POST">
                    <div class="form-group">
                        <label for="client_id">Client</label>
                        <select name="client_id" id="client_id" required>
                            <option value="">Select Client</option>
                            <?php while ($client = mysqli_fetch_assoc($clients_result)): ?>
                                <option value="<?php echo $client['client_id']; ?>"><?php echo $client['firstname'] . " " . $client['lastname']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="lot_id">Lot</label>
                        <select name="lot_id" id="lot_id" required>
                            <option value="">Select Lot</option>
                            <?php while ($lot = mysqli_fetch_assoc($lots_result)): ?>
                                <option value="<?php echo $lot['lot_id']; ?>"><?php echo $lot['lot_number'] . " - " . $lot['location']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select name="payment_method" id="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <?php foreach ($payment_methods as $method): ?>
                                <option value="<?php echo $method; ?>"><?php echo $method; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="reservation_fee">Reservation Fee (₱)</label>
                        <input type="number" name="reservation_fee" id="reservation_fee" step="0.01" required>
                    </div>

                    <button type="submit" name="submit_reservation" class="btn-submit">Create Reservation</button>
                </form>

                <h2>Reservation List</h2>
                <table class="reservation-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Lot</th>
                            <th>Payment Method</th>
                            <th>Reservation Fee</th>
                            <th>Status</th>
                            <th>Reservation Date</th>
                            <th>Date Approved</th>
                            <th>Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($reservation = mysqli_fetch_assoc($reservations_result)): ?>
                            <tr>
                                <td><?php echo $reservation['firstname'] . " " . $reservation['lastname']; ?></td>
                                <td><?php echo $reservation['lot_number']; ?></td>
                                <td><?php echo $reservation['payment_method']; ?></td>
                                <td><?php echo number_format($reservation['reservation_fee'], 2); ?></td>
                                <td><?php echo $reservation['status']; ?></td>
                                <td><?php echo $reservation['reservation_date']; ?></td>
                                <td><?php echo $reservation['date_approved'] ?: 'Not yet approved'; ?></td>
                                <td><?php echo $reservation['expiry_date'] ?: 'Not set'; ?></td>
                                <td>
                                    <?php if ($reservation['status'] == 'Approved' && !$reservation['expiry_date']): ?>
                                        <form action="reservations.php" method="POST">
                                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                            <button type="submit" name="approve_reservation" class="btn-approve">Approve & Set Expiry Date</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>
