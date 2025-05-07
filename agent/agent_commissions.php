<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';

// Check if user is logged in and is an agent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'AGENT') {
    header("Location: /lot-reservation/login.php");
    exit();
}

// Get agent ID
$agent_id = null;
$agent_query = "SELECT agent_id FROM agent WHERE user_id = " . $_SESSION['user_id'];
$agent_result = mysqli_query($conn, $agent_query);
if ($agent_result && mysqli_num_rows($agent_result) > 0) {
    $agent_data = mysqli_fetch_assoc($agent_result);
    $agent_id = $agent_data['agent_id'];
} else {
    die("Agent profile not found");
}

// Initialize variables
$error = '';
$success = '';

try {
    // Fetch agent's commission summary
    $summary_query = "SELECT 
                     COUNT(ac.reservation_id) AS total_sales,
                     SUM(ac.commission_fee) AS total_commission,
                     SUM(CASE WHEN ac.status = 'Approved' THEN ac.commission_fee ELSE 0 END) AS approved_commission,
                     SUM(CASE WHEN ac.status = 'Paid' THEN ac.commission_fee ELSE 0 END) AS paid_commission
                     FROM agent_commission ac
                     WHERE ac.agent_id = $agent_id";
    $summary_result = mysqli_query($conn, $summary_query);
    $summary_data = mysqli_fetch_assoc($summary_result);

    // Fetch agent's commission details
    $commissions = [];
    $details_query = "SELECT r.reservation_id, 
                     CONCAT(c.firstname, ' ', c.lastname) AS client_name,
                     l.lot_number, l.price, r.reservation_date,
                     ac.commission_fee, ac.status AS commission_status
                     FROM agent_commission ac
                     JOIN reservation r ON ac.reservation_id = r.reservation_id
                     JOIN client c ON r.client_id = c.client_id
                     JOIN lot l ON r.lot_id = l.lot_id
                     WHERE ac.agent_id = $agent_id
                     ORDER BY r.reservation_date DESC";
    $details_result = mysqli_query($conn, $details_query);
    while ($row = mysqli_fetch_assoc($details_result)) {
        $commissions[] = $row;
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
    <title>Agent Dashboard</title>
    <link rel="stylesheet" href="/lot-reservation/assets/css/agent.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .commission-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .summary-card h3 {
            margin-top: 0;
            color: #555;
            font-size: 16px;
        }
        .summary-card p {
            margin-bottom: 0;
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .commission-table {
            width: 100%;
            border-collapse: collapse;
        }
        .commission-table th, .commission-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .commission-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .status-approved { color: #27ae60; }
        .status-paid { color: #3498db; }
        .top-bar {
            background: #34495e;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include('../sidebar.php'); ?>

    <main class="main-content">
        <header class="top-bar">
            <span>Agent Dashboard</span>
            <i class="fas fa-user-tie"></i>
        </header>

        <div class="content-wrapper">
            <div class="container">
                <h1>My Commission Report</h1>

                <?php if ($error): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="success-message"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <div class="commission-summary">
                    <div class="summary-card">
                        <h3>Total Sales</h3>
                        <p><?= $summary_data['total_sales'] ?? 0 ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Total Commission</h3>
                        <p>₱<?= number_format($summary_data['total_commission'] ?? 0, 2) ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Approved</h3>
                        <p>₱<?= number_format($summary_data['approved_commission'] ?? 0, 2) ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Paid</h3>
                        <p>₱<?= number_format($summary_data['paid_commission'] ?? 0, 2) ?></p>
                    </div>
                </div>

                <h2>Commission Details</h2>
                <div class="table-responsive">
                    <table class="commission-table">
                        <thead>
                            <tr>
                                <th>Reservation ID</th>
                                <th>Client</th>
                                <th>Lot</th>
                                <th>Lot Price</th>
                                <th>Commission</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commissions as $commission): ?>
                                <tr>
                                    <td><?= $commission['reservation_id'] ?></td>
                                    <td><?= htmlspecialchars($commission['client_name']) ?></td>
                                    <td><?= htmlspecialchars($commission['lot_number']) ?></td>
                                    <td>₱<?= number_format($commission['price'], 2) ?></td>
                                    <td>₱<?= number_format($commission['commission_fee'], 2) ?></td>
                                    <td class="status-<?= strtolower($commission['commission_status']) ?>">
                                        <?= $commission['commission_status'] ?>
                                    </td>
                                    <td><?= $commission['reservation_date'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($commissions)): ?>
                                <tr>
                                    <td colspan="7">No commission records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>