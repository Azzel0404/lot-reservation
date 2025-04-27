<!--admin/index.php-->
<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../admin/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="layout-wrapper">
    <!-- Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Content Area -->
    <div class="content-area">
        <!-- Top Bar -->
        <header class="top-bar">
            <span>Admin</span>
            <i class="fas fa-user-cog"></i>
        </header>

        <!-- Page Content -->
        <div class="content-wrapper">
            <!-- Metrics Section -->
            <section class="dashboard-metrics">
                <div class="card blue">
                    <h3>10</h3>
                    <p>Total Reservations</p>
                </div>
                <div class="card green">
                    <h3>5</h3>
                    <p>Approved Reservations</p>
                </div>
                <div class="card purple">
                    <h3>15</h3>
                    <p>Total Users</p>
                </div>
                <div class="card red">
                    <h3>3</h3>
                    <p>Expired Reservations</p>
                </div>
                <div class="card donut">
                    <canvas id="lotChart"></canvas>
                </div>
            </section>

            <!-- Recent Activity Section -->
            <section class="activity-log">
                <h3>Recent Activity</h3>
                <button class="filter-btn"><i class="fas fa-filter"></i> Filter</button>
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
                        <tr><td>Reservation Approved</td><td>Maria Dela Cruz</td><td>Client</td><td>Lot 11</td><td>2025-03-15 10:45 AM</td></tr>
                        <tr><td>User Login</td><td>Jane Smith</td><td>Admin</td><td>Lot 31</td><td>2025-03-15 09:30 AM</td></tr>
                        <tr><td>Reservation Approved</td><td>Robert Lee</td><td>Client</td><td>Lot 13</td><td>2025-03-13 03:10 PM</td></tr>
                        <tr><td>Reservation Approved</td><td>Maria</td><td>Client</td><td>Lot 41</td><td>2025-03-13 05:20 PM</td></tr>
                    </tbody>
                </table>
            </section>
        </div> <!-- END content-wrapper -->
    </div> <!-- END content-area -->
</div> <!-- END layout-wrapper -->

<!-- Chart Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('lotChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Available', 'Reserved'],
        datasets: [{
            data: [27, 73],
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
