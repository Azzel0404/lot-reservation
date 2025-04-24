<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2 class="logo">Reservelt</h2>
        <ul class="sidebar-nav">
            <li><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="#"><i class="fas fa-calendar-check"></i> Reservations</a></li>
            <li><a href="#"><i class="fas fa-map"></i> Lots</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="#"><i class="fas fa-cogs"></i> Settings</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <span>Admin</span>
            <i class="fas fa-user-cog"></i>
        </header>

        <section class="dashboard-metrics">
            <div class="card blue"><h3>10</h3><p>Total Reservations</p></div>
            <div class="card green"><h3>5</h3><p>Approved Reservations</p></div>
            <div class="card purple"><h3>15</h3><p>Total Users</p></div>
            <div class="card red"><h3>3</h3><p>Expired Reservations</p></div>
            <div class="card donut">
                <canvas id="lotChart"></canvas>
            </div>
        </section>

        <section class="activity-log">
            <h3>Recent Activity</h3>
            <button class="filter-btn"><i class="fas fa-filter"></i> Filter</button>
            <table>
                <thead>
                    <tr>
                        <th>Action</th><th>User</th><th>Role</th><th>Related Lot</th><th>Date</th>
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
    </main>
</div>

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
