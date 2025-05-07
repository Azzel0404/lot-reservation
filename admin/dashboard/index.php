<!--lot-reservation/admin/dashboard/index.php-->
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
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                </div>
                <div class="card red">
                    <h3><?php echo $expired_reservations; ?></h3>
                    <p>Expired Reservations</p>
                </div>
            </section>

            <section class="donut-chart">
                <div class="card donut">
                    <canvas id="lotChart"></canvas>
                </div>
            </section>

            <section class="activity-log">
                <h3>Recent Activity</h3>
                <button class="filter-btn"><i class="fas fa-filter"></i> Filter</button>
                <div class="card activity-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Related Lot</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Activity log entries will go here -->
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('lotChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Available', 'Reserved'],
        datasets: [{
            data: [27, 73], // Replace with dynamic data if needed
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
</script>

</body>
</html>
