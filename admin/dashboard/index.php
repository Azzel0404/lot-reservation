<!--lot-reservation/admin/dashboard/index.php-->
<!--lot-reservation/admin/dashboard/index.php-->
<?php
session_start();
include('../../config/db.php');

// Fetch the total number of reservations
$total_reservations_query = "SELECT COUNT(*) AS total FROM reservation";
$total_reservations_result = mysqli_query($conn, $total_reservations_query);
$total_reservations = mysqli_fetch_assoc($total_reservations_result)['total'];

// Fetch the total number of approved reservations
$approved_reservations_query = "SELECT COUNT(*) AS approved FROM reservation WHERE status = 'Approved'";
$approved_reservations_result = mysqli_query($conn, $approved_reservations_query);
$approved_reservations = mysqli_fetch_assoc($approved_reservations_result)['approved'];

// Fetch the total number of users (CLIENT and AGENT)
$total_users_query = "SELECT COUNT(*) AS total_users FROM user WHERE role IN ('CLIENT', 'AGENT')";
$total_users_result = mysqli_query($conn, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total_users'];

// Fetch the total number of clients
$total_clients_query = "SELECT COUNT(*) AS total_clients FROM user WHERE role = 'CLIENT'";
$total_clients_result = mysqli_query($conn, $total_clients_query);
$total_clients = mysqli_fetch_assoc($total_clients_result)['total_clients'];

// Fetch the total number of agents
$total_agents_query = "SELECT COUNT(*) AS total_agents FROM user WHERE role = 'AGENT'";
$total_agents_result = mysqli_query($conn, $total_agents_query);
$total_agents = mysqli_fetch_assoc($total_agents_result)['total_agents'];

// Query to get the count of available and reserved lots
$lot_status_query = "SELECT 
                           SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) AS available,
                           SUM(CASE WHEN status = 'Reserved' THEN 1 ELSE 0 END) AS reserved
                        FROM lot";
$lot_status_result = mysqli_query($conn, $lot_status_query);
$lot_status = mysqli_fetch_assoc($lot_status_result);
$available_lots = $lot_status['available'];
$reserved_lots = $lot_status['reserved'];

// Fetch total number of lots (available + reserved)
$total_lots = $available_lots + $reserved_lots;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .donut-chart {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .card.donut {
            width: 350px;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .chart-description {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
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
                    <p class="chart-description">Lots </p>
                </div>
                <div class="card donut">
                    <canvas id="userChart"></canvas>
                    <p class="chart-description">Users </p>
                </div>
            </section>

            <section class="summary-reports">
                <h3>Summary Reports</h3>
                <div class="card report-summary">
                    <p><strong>Total Reservations:</strong> <?php echo $total_reservations; ?></p>
                    <p><strong>Approved Reservations:</strong> <?php echo $approved_reservations; ?></p>
                    <p><strong>Total Users:</strong> <?php echo $total_users; ?></p>
                    <p><strong>Total Clients:</strong> <?php echo $total_clients; ?></p>
                    <p><strong>Total Agents:</strong> <?php echo $total_agents; ?></p>
                    <p><strong>Total Lots:</strong> <?php echo $total_lots; ?> (Available: <?php echo $available_lots; ?>, Reserved: <?php echo $reserved_lots; ?>)</p>
                </div>
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
