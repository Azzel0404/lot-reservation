<!--lot-reservation/admin/dashboard/index.php-->
<?php
session_start();
include('../../config/db.php');

// Fetch reservation counts
$total_reservations_query = "SELECT COUNT(*) AS total FROM reservation";
$total_reservations_result = mysqli_query($conn, $total_reservations_query);
$total_reservations = mysqli_fetch_assoc($total_reservations_result)['total'];

$approved_reservations_query = "SELECT COUNT(*) AS approved FROM reservation WHERE status = 'Approved'";
$approved_reservations_result = mysqli_query($conn, $approved_reservations_query);
$approved_reservations = mysqli_fetch_assoc($approved_reservations_result)['approved'];

// Fetch user counts
$total_users_query = "SELECT COUNT(*) AS total_users FROM user WHERE role IN ('CLIENT', 'AGENT')";
$total_users_result = mysqli_query($conn, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total_users'];

$total_clients_query = "SELECT COUNT(*) AS total_clients FROM user WHERE role = 'CLIENT'";
$total_clients_result = mysqli_query($conn, $total_clients_query);
$total_clients = mysqli_fetch_assoc($total_clients_result)['total_clients'];

$total_agents_query = "SELECT COUNT(*) AS total_agents FROM user WHERE role = 'AGENT'";
$total_agents_result = mysqli_query($conn, $total_agents_query);
$total_agents = mysqli_fetch_assoc($total_agents_result)['total_agents'];

// Fetch lot status
$lot_status_query = "SELECT 
                           SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) AS available,
                           SUM(CASE WHEN status = 'Reserved' THEN 1 ELSE 0 END) AS reserved
                        FROM lot";
$lot_status_result = mysqli_query($conn, $lot_status_query);
$lot_status = mysqli_fetch_assoc($lot_status_result);
$available_lots = $lot_status['available'];
$reserved_lots = $lot_status['reserved'];
$total_lots = $available_lots + $reserved_lots;

// Fetch clients with reservation info
$clients_with_reservation_query = "
    SELECT 
        c.client_id,
        CONCAT(c.firstname, ' ', c.middlename, ' ', c.lastname) AS client_name,
        COUNT(r.reservation_id) AS total_reservations,
        GROUP_CONCAT(l.lot_number SEPARATOR ', ') AS lot_numbers
    FROM reservation r
    JOIN client c ON r.client_id = c.client_id
    JOIN lot l ON r.lot_id = l.lot_id
    GROUP BY c.client_id
";
$clients_with_reservation_result = mysqli_query($conn, $clients_with_reservation_query);
$clients_with_reservation = [];
while ($row = mysqli_fetch_assoc($clients_with_reservation_result)) {
    $clients_with_reservation[] = $row;
}

// Fetch number of agents with commissions
$agents_with_commission_query = "
    SELECT a.firstname, a.lastname, a.license_number, SUM(ac.commission_fee) AS total_commission
    FROM agent_commission ac
    JOIN agent a ON ac.agent_id = a.agent_id
    GROUP BY a.agent_id
";
$agents_with_commission_result = mysqli_query($conn, $agents_with_commission_query);
$agents_with_commission = [];
while ($row = mysqli_fetch_assoc($agents_with_commission_result)) {
    $agents_with_commission[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="layout-wrapper">
    <?php include('../sidebar.php'); ?>

    <div class="content-area">
        <header class="top-bar">
            <span>Admin</span>
            <i class="fas fa-user-cog"></i>
        </header>

        <div class="content-wrapper">
            <section class="dashboard-metrics">
                <div class="card blue">
                    <h3><?php echo $total_reservations; ?></h3>
                    <p>Total Reservations</p>
                </div>
                <div class="card green">
                    <h3><?php echo $approved_reservations; ?></h3>
                    <p>Approved Reservations</p>
                </div>
                <div class="card purple">
                    <h3><?php echo $total_lots; ?></h3>
                    <p>Total Lots (Available + Reserved)</p>
                </div>
                <div class="card yellow">
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                </div>
                <div class="card orange">
                    <h3><?php echo $total_agents; ?></h3>
                    <p>Total Agents</p>
                </div>
                <div class="card red">
                    <h3><?php echo $total_clients; ?></h3>
                    <p>Total Clients</p>
                </div>
            </section>

            <section class="donut-chart">
                <div class="card donut">
                    <canvas id="lotChart"></canvas>
                    <p class="chart-description">Lots</p>
                </div>
                <div class="card donut">
                    <canvas id="userChart"></canvas>
                    <p class="chart-description">Users</p>
                </div>
            </section>

            <section class="summary-reports">
                <h3>Summary Reports</h3>
                <div class="card report-summary">
                    <p><strong>Total Reservations:</strong> <?php echo $total_reservations; ?></p>
                    <p><strong>Approved Reservations:</strong> <?php echo $approved_reservations; ?></p>
                    <p><strong>Total Users:</strong> <?php echo $total_users; ?> (Agents: <?php echo $total_agents; ?>, Clients: <?php echo $total_clients; ?>)</p>
                    <p><strong>Total Lots:</strong> <?php echo $total_lots; ?> (Available: <?php echo $available_lots; ?>, Reserved: <?php echo $reserved_lots; ?>)</p>
                    <p><strong>Agents with Commission:</strong> <?php echo count($agents_with_commission); ?></p>
                </div>
            </section>

            <!-- New Section to display client reservation details -->
            <section class="client-reservation-details">
                <h3>Clients with Reservation</h3>
                <table class="commission-table">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Total Reservations</th>
                            <th>Reserved Lot Numbers</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($clients_with_reservation)): ?>
                            <?php foreach ($clients_with_reservation as $client): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($client['client_name']); ?></td>
                                    <td><?php echo $client['total_reservations']; ?></td>
                                    <td><?php echo htmlspecialchars($client['lot_numbers']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No reservation data available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <!-- Existing Agent Commission Table -->
            <section class="agent-commission-details">
                <h3>Agents with Commission</h3>
                <table class="commission-table">
                    <thead>
                        <tr>
                            <th>Agent Name</th>
                            <th>License Number</th>
                            <th>Total Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agents_with_commission as $agent): ?>
                            <tr>
                                <td><?php echo $agent['firstname'] . ' ' . $agent['lastname']; ?></td>
                                <td><?php echo $agent['license_number']; ?></td>
                                <td><?php echo number_format($agent['total_commission'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart for Lots
const ctx = document.getElementById('lotChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Available', 'Reserved'],
        datasets: [{
            data: [<?php echo $available_lots; ?>, <?php echo $reserved_lots; ?>],
            backgroundColor: ['#28a745', '#007bff'],
        }]
    },
    options: {
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Chart for Users
const userCtx = document.getElementById('userChart').getContext('2d');
new Chart(userCtx, {
    type: 'doughnut',
    data: {
        labels: ['Clients', 'Agents'],
        datasets: [{
            data: [<?php echo $total_clients; ?>, <?php echo $total_agents; ?>],
            backgroundColor: ['#ffc107', '#fd7e14'],
        }]
    },
    options: {
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

</body>
</html>
