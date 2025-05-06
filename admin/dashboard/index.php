<?php
session_start();
include('../../config/db.php');


// Fetch counts from the database
$total_reservations_query = "SELECT COUNT(*) AS total FROM reservation";
$total_reservations_result = mysqli_query($conn, $total_reservations_query);
$total_reservations = mysqli_fetch_assoc($total_reservations_result)['total'];

$approved_reservations_query = "SELECT COUNT(*) AS approved FROM reservation WHERE status = 'Approved'";
$approved_reservations_result = mysqli_query($conn, $approved_reservations_query);
$approved_reservations = mysqli_fetch_assoc($approved_reservations_result)['approved'];

$expired_reservations_query = "SELECT COUNT(*) AS expired FROM reservation WHERE status = 'Expired'";
$expired_reservations_result = mysqli_query($conn, $expired_reservations_query);
$expired_reservations = mysqli_fetch_assoc($expired_reservations_result)['expired'];

$total_users_query = "SELECT COUNT(*) AS total_users FROM user WHERE role IN ('CLIENT', 'AGENT')";
$total_users_result = mysqli_query($conn, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total_users'];

// --- Fetch data for the Lot Status Chart ---
$available_lots_query = "SELECT COUNT(*) AS available_count FROM lot WHERE status = 'Available'";
$available_lots_result = mysqli_query($conn, $available_lots_query);
$available_count = mysqli_fetch_assoc($available_lots_result)['available_count'];

$reserved_lots_query = "SELECT COUNT(*) AS reserved_count FROM lot WHERE status = 'Reserved'";
$reserved_lots_result = mysqli_query($conn, $reserved_lots_query);
$reserved_count = mysqli_fetch_assoc($reserved_lots_result)['reserved_count'];

// Store the lot status counts in an array to pass to JavaScript
$lot_status_data = [
    'available' => $available_count,
    'reserved' => $reserved_count,
];

// Convert the PHP array to a JSON string so JavaScript can read it
$lot_status_json = json_encode($lot_status_data);



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
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

        <div class="layout-wrapper">
    <div class="content-area">
        <div class="content-wrapper">
            <!-- Dashboard Metrics -->
            <section class="dashboard-metrics">
                <div class="card blue">
                    <div class="icon"><i class="fas fa-calendar"></i></div>
                    <h3><?php echo $total_reservations; ?></h3>
                    <p>Total Reservations</p>
                </div>
                <div class="card green">
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                    <h3><?php echo $approved_reservations; ?></h3>
                    <p>Approved Reservations</p>
                </div>
                <div class="card purple">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                </div>
                <div class="card red">
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <h3><?php echo $expired_reservations; ?></h3>
                    <p>Expired Reservations</p>
                </div>
            </section>

            <!-- Chart Section -->
            <section class="chart-container">
                <div class="chart-card">
                    <h3>Lot Status Overview</h3>
                    <canvas id="lotChart"></canvas>
                </div>
            </section>

            <!-- Activity Log -->
            <section class="activity-log">
                <div class="activity-log-header">
                    <h3>Recent Activity</h3>
                    <button class="filter-btn">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                <table class="activity-table">
                    <tbody>
                        <!-- Activity log entries will go here -->
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('lotChart').getContext('2d');
const lotStatusData = JSON.parse('<?php echo $lot_status_json; ?>'); 
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Available', 'Reserved'],
        datasets: [{
            data: [lotStatusData.available, lotStatusData.reserved],
            backgroundColor: ['#28a745', '#007bff'],
        }]
    },
    options: {
        aspectRatio: 5,
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
