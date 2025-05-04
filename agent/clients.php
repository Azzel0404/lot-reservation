<?php
session_start();
include('../config/db.php');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get agent information
$agent_id = null;
$agent_name = "Agent";
if ($_SESSION['role'] === 'AGENT') {
    $query = "SELECT a.agent_id, a.firstname, a.lastname 
              FROM agent a 
              JOIN user u ON a.user_id = u.user_id 
              WHERE u.user_id = " . $_SESSION['user_id'];
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $agent = $result->fetch_assoc();
        $agent_id = $agent['agent_id'];
        $agent_name = $agent['firstname'] . ' ' . $agent['lastname'];
    }
    $result->free();
}

// Fetch clients data
$clients_data = [];
if ($agent_id) {
    $query = "SELECT c.client_id, c.firstname, c.lastname, c.middlename, 
                     u.email, u.phone, u.address,
                     l.lot_number, r.reservation_date, r.status, 
                     ac.commission_fee
              FROM client c
              JOIN user u ON c.user_id = u.user_id
              LEFT JOIN reservation r ON c.client_id = r.client_id
              LEFT JOIN lot l ON r.lot_id = l.lot_id
              LEFT JOIN agent_commission ac ON r.reservation_id = ac.reservation_id
              WHERE c.agent_id = $agent_id
              ORDER BY r.reservation_date DESC";
    
    $result = $conn->query($query);
    
    if ($result === false) {
        die("Error in clients query: " . $conn->error);
    } else {
        while ($row = $result->fetch_assoc()) {
            $clients_data[] = $row;
        }
        $result->free();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ReserveIt - Clients</title>
    <link rel="stylesheet" href="../agent/agent.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="d-flex">
    
    <!-- Sidebar -->
    <div class="sidebar p-4" style="width: 250px; height: 100vh;">
        <div class="sidebar-brand">ReserveIt</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="index.php" class="nav-link text-white">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="clients.php" class="nav-link text-white active">
                    <i class="fas fa-user-friends me-2"></i> Clients
                </a>
            </li>
            <li class="nav-item">
                <a href="commissions.php" class="nav-link text-white">
                    <i class="fas fa-hand-holding-usd me-2"></i> Commissions
                </a>
            </li>
            <li class="nav-item mt-4">
                <a href="../logout.php" class="nav-link text-white">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main content -->
    <div class="flex-grow-1">
        <!-- Topbar -->
        <div class="topbar d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Clients Management</h5>
            <div class="d-flex align-items-center">
                <span class="fw-medium me-3">Welcome, Agent</span>
                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>

        <!-- Clients Table -->
        <div class="p-4">
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-bold">Client List</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Lot Reserved</th>
                                <th>Status</th>
                                <th>Date Reserved</th>
                                <th>Commission</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($clients_data)): ?>
                                <?php foreach ($clients_data as $client): 
                                    $fullName = $client['firstname'] . ' ' . 
                                               ($client['middlename'] ? $client['middlename'] . ' ' : '') . 
                                               $client['lastname'];
                                    
                                    $status_class = '';
                                    switch ($client['status']) {
                                        case 'Approved':
                                            $status_class = 'badge-approved';
                                            break;
                                        case 'Expired':
                                            $status_class = 'badge-expired';
                                            break;
                                        default:
                                            $status_class = 'badge-pending';
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($fullName); ?></td>
                                        <td><?php echo $client['lot_number'] ? htmlspecialchars($client['lot_number']) : 'None'; ?></td>
                                        <td>
                                            <span class="badge badge-status <?php echo $status_class; ?>">
                                                <?php echo $client['status'] ? htmlspecialchars($client['status']) : 'No Reservation'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $client['reservation_date'] ? date('Y-m-d', strtotime($client['reservation_date'])) : 'N/A'; ?></td>
                                        <td class="commission-value">
                                            <?php echo $client['commission_fee'] ? '₱' . number_format($client['commission_fee'], 2) : 'N/A'; ?>
                                        </td>          
                                        <td>
                                            <button class="action-btn btn btn-sm btn-outline-primary me-1">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">No clients found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">Showing <?php echo count($clients_data); ?> clients</div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <!--
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                            -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>