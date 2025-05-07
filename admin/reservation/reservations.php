<!-- lot-reservation/admin/reservation/reservations.php -->
<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';

if ($_SESSION['role'] !== 'ADMIN') {
    header("Location: /lot-reservation/login.php");
    exit();
}

$error = '';
$success = '';

function safeFetch($conn, $query) {
    $result = mysqli_query($conn, $query);
    if (!$result) throw new Exception("Query failed: " . mysqli_error($conn));
    return $result;
}

try {
    $clients = [];
    $clients_result = safeFetch($conn, 
        "SELECT c.client_id, c.firstname, c.lastname, a.agent_id, a.firstname AS agent_firstname, a.lastname AS agent_lastname
         FROM client c
         LEFT JOIN agent a ON c.agent_id = a.agent_id");
    while ($row = mysqli_fetch_assoc($clients_result)) {
        $clients[] = $row;
    }

    $lots = [];
    $lots_result = safeFetch($conn, "SELECT * FROM lot WHERE status = 'Available'");
    while ($row = mysqli_fetch_assoc($lots_result)) {
        $lots[] = $row;
    }

    $payment_methods = ['Cash', 'Credit'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reservation'])) {
        $client_id = (int)$_POST['client_id'];
        $lot_id = (int)$_POST['lot_id'];
        $payment_method = $conn->real_escape_string($_POST['payment_method']);

        $conn->begin_transaction();

        try {
            // Get lot price
            $lot_result = safeFetch($conn, "SELECT price FROM lot WHERE lot_id = $lot_id");
            $lot_data = mysqli_fetch_assoc($lot_result);
            $lot_price = (float)$lot_data['price'];

            // Call the stored procedure to calculate reservation fee (10% of lot price)
            $stmt = $conn->prepare("CALL sp_calculate_reservation_fee(?, @reservation_fee)");
            $stmt->bind_param("i", $lot_id);
            $stmt->execute();
            $stmt->close();

            // Get the calculated reservation fee from the variable
            $reservation_fee_result = safeFetch($conn, "SELECT @reservation_fee AS reservation_fee");
            $reservation_fee_data = mysqli_fetch_assoc($reservation_fee_result);
            $reservation_fee = (float)$reservation_fee_data['reservation_fee'];

            // Get the agent_id for the client (if applicable)
            $agent_id = null;
            $agent_result = safeFetch($conn, 
                "SELECT agent_id FROM client WHERE client_id = $client_id");
            if ($agent_data = mysqli_fetch_assoc($agent_result)) {
                $agent_id = $agent_data['agent_id'];
            }

            // Get the payment_id based on payment method
            $payment_result = safeFetch($conn, 
                "SELECT payment_id FROM payment WHERE payment_method = '$payment_method'");
            $payment_data = mysqli_fetch_assoc($payment_result);
            $payment_id = (int)$payment_data['payment_id'];
            
            $reservation_date = date('Y-m-d H:i:s');
            $date_approved = $reservation_date;

            // Insert reservation into the database
            $stmt = $conn->prepare("INSERT INTO reserva tion (client_id, lot_id, payment_id, reservation_fee, status, reservation_date, date_approved)
                                    VALUES (?, ?, ?, ?, 'Approved', ?, ?)");
            $stmt->bind_param("iiidss", $client_id, $lot_id, $payment_id, $reservation_fee, $reservation_date, $date_approved);
            $stmt->execute();
            $reservation_id = $stmt->insert_id;

            // Update lot status to Reserved
            safeFetch($conn, "UPDATE lot SET status = 'Reserved' WHERE lot_id = $lot_id");

            if ($agent_id) {
                // Call stored procedure for agent commission calculation (if applicable)
                safeFetch($conn, "CALL sp_calculate_agent_commission_dynamic($agent_id, $reservation_id, 3.00)");
                while ($conn->more_results() && $conn->next_result()) {
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                }
            }

            $conn->commit();
            $success = "Reservation created successfully." . ($agent_id ? " Commission calculated." : "");

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error creating reservation: " . $e->getMessage();
        }
    }

    $reservations = [];
    $reservations_result = safeFetch($conn, 
        "SELECT r.reservation_id, c.firstname, c.lastname, l.lot_number, l.price AS lot_price, 
                r.reservation_fee, p.payment_method, r.status, r.reservation_date, 
                r.date_approved, a.firstname AS agent_firstname, a.lastname AS agent_lastname, ac.commission_fee
         FROM reservation r
         JOIN client c ON r.client_id = c.client_id
         JOIN lot l ON r.lot_id = l.lot_id
         JOIN payment p ON r.payment_id = p.payment_id
         LEFT JOIN agent a ON c.agent_id = a.agent_id
         LEFT JOIN agent_commission ac ON ac.reservation_id = r.reservation_id AND ac.agent_id = a.agent_id
         ORDER BY r.reservation_date DESC");
    while ($row = mysqli_fetch_assoc($reservations_result)) {
        $reservations[] = $row;
    }

} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reservations</title>
    <link rel="stylesheet" href="reservations.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar p-4" style="width: 250px; height: 100vh;">
        <div class="sidebar-brand mb-4">ReserveIt</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="../dashboard/index.php" class="nav-link text-white">
                    <i class="fas fa-dashboard me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="../reservation/reservations.php" class="nav-link text-white">
                    <i class="fas fa-calendar-check me-2"></i> Reservations
                </a>
            </li>
            <li class="nav-item">
                <a href="../lots/lots.php" class="nav-link text-white">
                    <i class="fas fa-th me-2"></i> Lots
                </a>
            </li>
            <li class="nav-item">
                <a href="../users/users.php" class="nav-link text-white">
                    <i class="fas fa-users me-2"></i> Users
                </a>
            </li>
            <li class="nav-item mt-4">
                <a href="../../logout.php" class="nav-link text-white">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <div class="topbar d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Settings</h5>
            <div class="d-flex align-items-center">
                <span class="fw-medium me-3">Admin</span>
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>

        <main class="main-content">
    <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success-message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Create Reservation</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="client_id">Client</label>
                            <select class="form-control" id="client_id" name="client_id" required>
                                <option value="">Select Client</option>
                                <?php foreach ($clients as $c): ?>
                                    <option value="<?= $c['client_id'] ?>">
                                        <?= htmlspecialchars($c['firstname'] . ' ' . $c['lastname']) ?>
                                        <?php if ($c['agent_id']): ?>
                                            <span class="text-muted">(Agent: <?= htmlspecialchars($c['agent_firstname'] . ' ' . $c['agent_lastname']) ?>)</span>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="lot_id">Lot</label>
                            <select class="form-control" id="lot_id" name="lot_id" required>
                                <option value="">Select Lot</option>
                                <?php foreach ($lots as $l): ?>
                                    <option value="<?= $l['lot_id'] ?>">
                                        <?= htmlspecialchars($l['lot_number'] . ' - ' . $l['location']) ?> (₱<?= number_format($l['price'], 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select class="form-control" id="payment_method" name="payment_method" required>
                                <option value="">Select Method</option>
                                <?php foreach ($payment_methods as $method): ?>
                                    <option value="<?= $method ?>"><?= $method ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="text-end mt-3">
                    <button type="submit" name="submit_reservation" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Create Reservation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title mb-0">Reservation List</h2>
            <div class="d-flex">
                <input type="text" class="form-control me-2" placeholder="Search reservations..." style="max-width: 250px;">
                <button class="btn btn-outline-secondary">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Lot Number</th>
                        <th>Lot Price</th>
                        <th>Payment</th>
                        <th>Reservation Fee</th>
                        <th>Status</th>
                        <th>Commission</th>
                        <th>Reserved On</th>
                        <th>Approved On</th>
                    </tr>
                </thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['firstname'] . ' ' . $r['lastname']) ?></td>
                        <td><?= htmlspecialchars($r['lot_number']) ?></td>
                        <td>₱<?= number_format($r['lot_price'], 2) ?></td>
                        <td><?= htmlspecialchars($r['payment_method']) ?></td>
                        <td>₱<?= number_format($r['reservation_fee'], 2) ?></td>
                        <td><?= htmlspecialchars($r['status']) ?></td>
                        <td>
                            <?php if ($r['commission_fee']): ?>
                                ₱<?= number_format($r['commission_fee'], 2) ?>
                                <br><small><?= htmlspecialchars($r['agent_firstname'] . ' ' . $r['agent_lastname']) ?></small>
                            <?php else: ?>N/A<?php endif; ?>
                        </td>
                        <td><?= $r['reservation_date'] ?></td>
                        <td><?= $r['date_approved'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
