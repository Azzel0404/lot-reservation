<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Agent Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body class="d-flex">
    
    <!-- Sidebar -->
    <div class="sidebar p-4" style="width: 220px; background: linear-gradient(to bottom, #12254c, #1c3b70); color: white; height: 100vh;">
        <h4 class="mb-4">Reservelt</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="#" class="nav-link text-white">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="my_clients.php" class="nav-link text-white">
                    <i class="fas fa-users me-2"></i> Clients
                </a>
            </li>
            <li class="nav-item">
                <a href="commissions.php" class="nav-link text-white">
                    <i class="fas fa-money-bill-wave me-2"></i> Commissions
                </a>
            </li>
            <li class="nav-item">
                <a href="../logout.php" class="nav-link text-white">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
            
        </ul>

    </div>

    <!-- Main content -->
    <div class="flex-grow-1">
        <!-- Topbar -->
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
            <h5 class="mb-0">Dashboard</h5>
            <span class="fw-bold">Agent</span>
        </div>

        <!-- Stats -->
        <div class="p-4">
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="p-3 text-white bg-success rounded">15<br><small>Total Assisted Clients</small></div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 text-white bg-warning rounded">15<br><small>Total Commission Earned</small></div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 text-white bg-primary rounded">8<br><small>Approved Reservations</small></div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 text-white bg-danger rounded">3<br><small>Reserved Reservations</small></div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white p-4 rounded shadow-sm">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="mb-0">Recent Activity</h5>
                    <button class="btn btn-outline-secondary btn-sm">Filter</button>
                </div>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Action Taken</th>
                            <th>Lot Reserved</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>John Doe</td><td>Submitted Reservation</td><td>Lot 5</td><td>2025-03-15 10:45 AM</td></tr>
                        <tr><td>Jane Smith</td><td>Reservation Approved</td><td>Lot 10</td><td>2025-03-15 09:30 AM</td></tr>
                        <tr><td>Robert Lee</td><td>Reservation Approved</td><td>Lot 22</td><td>2025-03-13 03:10 PM</td></tr>
                        <tr><td>Maria Garcia</td><td>Submitted Reservation</td><td>Lot 8</td><td>2025-03-13 05:20 PM</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
