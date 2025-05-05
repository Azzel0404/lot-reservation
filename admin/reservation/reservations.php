<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';

// Check admin authentication
if ($_SESSION['role'] !== 'ADMIN') {
    header("Location: /lot-reservation/login.php");
    exit();
}

// Initialize variables
$error = '';
$success = '';

// Function to safely fetch query results
function safeFetch($conn, $query) {
    $result = mysqli_query($conn, $query);
    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($conn) . " - Query: " . $query);
    }
    return $result;
}

try {
    // Fetch clients with their agent information
    $clients = [];
    $clients_result = safeFetch($conn, 
        "SELECT c.client_id, c.firstname, c.lastname, 
         a.agent_id, a.firstname AS agent_firstname, a.lastname AS agent_lastname
         FROM client c
         LEFT JOIN agent a ON c.agent_id = a.agent_id");
    while ($row = mysqli_fetch_assoc($clients_result)) {
        $clients[] = $row;
    }

    // Fetch available lots with their prices
    $lots = [];
    $lots_result = safeFetch($conn, 
        "SELECT * FROM lot WHERE status = 'Available'");
    while ($row = mysqli_fetch_assoc($lots_result)) {
        $lots[] = $row;
    }

    // Payment methods
    $payment_methods = ['Cash', 'Credit'];

    // Handle new reservation creation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reservation'])) {
        $client_id = (int)$_POST['client_id'];
        $lot_id = (int)$_POST['lot_id'];
        $payment_method = $conn->real_escape_string($_POST['payment_method']);

        // Start transaction
        $conn->begin_transaction();

        try {
            // Get the lot price
            $lot_result = safeFetch($conn, "SELECT price FROM lot WHERE lot_id = $lot_id");
            $lot_data = mysqli_fetch_assoc($lot_result);
            $lot_price = (float)$lot_data['price'];
            $reservation_fee = $lot_price * 0.03; // Constant 3% of lot price

            // Get client's agent if exists
            $agent_id = null;
            $agent_result = safeFetch($conn, 
                "SELECT agent_id FROM client WHERE client_id = $client_id LIMIT 1");
            if (mysqli_num_rows($agent_result) > 0) {
                $agent_data = mysqli_fetch_assoc($agent_result);
                $agent_id = (int)$agent_data['agent_id'];
            }

            // Get payment_id
            $payment_result = safeFetch($conn, 
                "SELECT payment_id FROM payment WHERE payment_method = '$payment_method' LIMIT 1");
            if (mysqli_num_rows($payment_result) === 0) {
                throw new Exception("Payment method '$payment_method' not found");
            }
            $payment_data = mysqli_fetch_assoc($payment_result);
            $payment_id = (int)$payment_data['payment_id'];

            // Create reservation with 'Approved' status immediately
            $reservation_date = date('Y-m-d H:i:s');
            $date_approved = date('Y-m-d H:i:s');
            $expiry_date = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            $stmt = $conn->prepare("INSERT INTO reservation 
                                  (client_id, lot_id, payment_id, reservation_fee, status, reservation_date, date_approved, expiry_date) 
                                  VALUES (?, ?, ?, ?, 'Approved', ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("iiidsss", $client_id, $lot_id, $payment_id, $reservation_fee, $reservation_date, $date_approved, $expiry_date);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $reservation_id = $stmt->insert_id;

            // Update lot status
            $update_lot = safeFetch($conn, 
                "UPDATE lot SET status = 'Reserved' WHERE lot_id = $lot_id");

            // Calculate commission using stored procedure if agent exists
            if ($agent_id) {
                $commission_result = safeFetch($conn, 
                    "CALL sp_calculate_agent_commission_dynamic($agent_id, $reservation_id, 3.00)");
                
                // Clear any additional result sets from the procedure
                while ($conn->more_results()) {
                    $conn->next_result();
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                }
            }

            $conn->commit();
            $success = "Reservation created and approved successfully. " . 
                       ($agent_id ? "Commission calculated and approved for agent." : "No agent assigned.");
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error creating reservation: " . $e->getMessage();
        }
    }

    // Fetch reservations with commission data
    $reservations = [];
    $reservations_result = safeFetch($conn, 
        "SELECT r.reservation_id, c.firstname, c.lastname, l.lot_number, 
         r.reservation_fee, p.payment_method, r.status, r.reservation_date, 
         r.date_approved, r.expiry_date, a.agent_id, a.firstname AS agent_firstname, 
         a.lastname AS agent_lastname, ac.commission_fee
         FROM reservation r
         JOIN client c ON r.client_id = c.client_id
         JOIN lot l ON r.lot_id = l.lot_id
         JOIN payment p ON r.payment_id = p.payment_id
         LEFT JOIN agent a ON c.agent_id = a.agent_id
         LEFT JOIN agent_commission ac ON (ac.reservation_id = r.reservation_id AND ac.agent_id = a.agent_id)
         ORDER BY r.reservation_date DESC");
    while ($row = mysqli_fetch_assoc($reservations_result)) {
        $reservations[] = $row;
    }

} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
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
    <style>
        .error-message { color: #e74c3c; padding: 10px; margin-bottom: 20px; background: #fde8e8; border-radius: 4px; }
        .success-message { color: #27ae60; padding: 10px; margin-bottom: 20px; background: #e8f8f0; border-radius: 4px; }
        .reservation-table { width: 100%; border-collapse: collapse; }
        .reservation-table th, .reservation-table td { padding: 12px 15px; border-bottom: 1px solid #e0e0e0; }
        .reservation-table th { background-color: #f8f9fa; text-align: left; }
        .status-approved { color: #2ecc71; }
        .status-expired { color: #e74c3c; }
        .status-paid { color: #3498db; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include('../sidebar.php'); ?>

    <main class="main-content">
        <header class="top-bar">
            <span>Admin Dashboard</span>
            <i class="fas fa-user-cog"></i>
        </header>

        <div class="content-wrapper">
            <div class="container">
                <h1>Create Reservation</h1>

                <?php if ($error): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="success-message"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" class="reservation-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="client_id">Client</label>
                            <select name="client_id" id="client_id" required>
                                <option value="">Select Client</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= $client['client_id'] ?>">
                                        <?= htmlspecialchars($client['firstname'] . ' ' . $client['lastname']) ?>
                                        <?php if ($client['agent_id']): ?>
                                            (Agent: <?= htmlspecialchars($client['agent_firstname'] . ' ' . $client['agent_lastname']) ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="lot_id">Lot</label>
                            <select name="lot_id" id="lot_id" required>
                                <option value="">Select Lot</option>
                                <?php foreach ($lots as $lot): ?>
                                    <option value="<?= $lot['lot_id'] ?>">
                                        <?= htmlspecialchars($lot['lot_number'] . ' - ' . $lot['location']) ?>
                                        (₱<?= number_format($lot['price'], 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" required>
                                <option value="">Select Method</option>
                                <?php foreach ($payment_methods as $method): ?>
                                    <option value="<?= $method ?>"><?= $method ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="submit_reservation" class="btn-submit">Create Reservation</button>
                </form>

                <h2>Reservation List</h2>
                <div class="table-responsive">
                    <table class="reservation-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Lot</th>
                                <th>Payment</th>
                                <th>Fee (3%)</th>
                                <th>Status</th>
                                <th>Commission</th>
                                <th>Commission Status</th> <th>Date Reserved</th>
                                <th>Date Approved</th>
                                <th>Expiry Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                                <tr>
                                    <td><?= htmlspecialchars($reservation['firstname'] . ' ' . $reservation['lastname']) ?></td>
                                    <td><?= htmlspecialchars($reservation['lot_number']) ?></td>
                                    <td><?= htmlspecialchars($reservation['payment_method']) ?></td>
                                    <td>₱<?= number_format($reservation['reservation_fee'], 2) ?></td>
                                    <td class="status-<?= strtolower($reservation['status']) ?>">
                                        <?= htmlspecialchars($reservation['status']) ?>
                                    </td>
                                    <td>
                                        <?php if ($reservation['commission_fee']): ?>
                                            ₱<?= number_format($reservation['commission_fee'], 2) ?>
                                            <?php if ($reservation['agent_firstname']): ?>
                                                <br><small>(<?= htmlspecialchars($reservation['agent_firstname'] . ' ' . $reservation['agent_lastname']) ?>)</small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($reservation['commission_fee']): ?>
                                            Calculated
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $reservation['reservation_date'] ?></td>
                                    <td><?= $reservation['date_approved'] ?></td>
                                    <td><?= $reservation['expiry_date'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>