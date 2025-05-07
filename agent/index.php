<!-- agent/index.php -->
<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in as agent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'AGENT') {
    header("Location: ../login.php");
    exit();
}

// Get agent ID from session
// Assuming your agent's user_id is stored in the session
$agent_user_id = $_SESSION['user_id'];

// Initialize variables
$total_clients = 0;
$total_commission = 0;
$approved_reservations = 0;
$recent_activities = [];

try {
    // Check database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // 1. Get total assisted clients
    $clients_query = "SELECT COUNT(DISTINCT c.client_id) as total
                      FROM client c
                      WHERE c.agent_id = (SELECT a.agent_id FROM agent a WHERE a.user_id = ?)";
    $stmt = $conn->prepare($clients_query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $agent_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_clients = $row['total'] ?? 0;
    $stmt->close();

    // 2. Get total commission earned
    $commission_query = "SELECT SUM(ac.commission_fee) as total
                           FROM agent_commission ac
                           WHERE ac.agent_id = (SELECT a.agent_id FROM agent a WHERE a.user_id = ?)";
    $stmt = $conn->prepare($commission_query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $agent_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_commission = $row['total'] ?? 0;
    $stmt->close();

    // 3. Get approved reservations count
    $approved_query = "SELECT COUNT(*) as total
                       FROM reservation r
                       JOIN client c ON r.client_id = c.client_id
                       WHERE c.agent_id = (SELECT a.agent_id FROM agent a WHERE a.user_id = ?)
                         AND r.status = 'Approved'";
    $stmt = $conn->prepare($approved_query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $agent_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $approved_reservations = $row['total'] ?? 0;
    $stmt->close();

    // 4. Get pending reservations count
    $pending_query = "SELECT COUNT(*) as total
                      FROM reservation r
                      JOIN client c ON r.client_id = c.client_id
                      WHERE c.agent_id = (SELECT a.agent_id FROM agent a WHERE a.user_id = ?)
                        AND r.status = 'Expired'"; // Assuming 'Expired' means pending in your context
    $stmt = $conn->prepare($pending_query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $agent_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    // 5. Get recent activities
    $activities_query = "SELECT
                             c.firstname, c.lastname,
                             r.status,
                             l.lot_number,
                             r.reservation_date,
                             CASE
                                 WHEN r.status = 'Approved' THEN 'Reservation Approved'
                                 WHEN r.status = 'Expired' THEN 'Reservation Expired'
                                 ELSE r.status
                             END as action_taken
                         FROM reservation r
                         JOIN client c ON r.client_id = c.client_id
                         JOIN lot l ON r.lot_id = l.lot_id
                         WHERE c.agent_id = (SELECT a.agent_id FROM agent a WHERE a.user_id = ?)
                         ORDER BY r.reservation_date DESC
                         LIMIT 5";
    $stmt = $conn->prepare($activities_query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $agent_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recent_activities[] = $row;
    }
    $stmt->close();

} catch (Exception $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Agent Dashboard</title>
    <link rel="stylesheet" href="../agent/agent.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="d-flex">

    <style>
        .badge-approved {
        background-color: #dbedda;
        color: #155724;
        }
    </style>

    <div class="sidebar p-4" style="width: 250px; height: 100vh;">
        <div class="sidebar-brand">ReserveIt</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="#" class="nav-link text-white active">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="clients.php" class="nav-link text-white">
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

    <div class="flex-grow-1">
        <div class="topbar d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Dashboard Overview</h5>
            <div class="d-flex align-items-center">
                <span class="fw-medium me-3">Welcome, <?php echo htmlspecialchars($_SESSION['firstname'] ?? 'Agent'); ?></span>
                
                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>

        <div class="p-4">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, var(--success-color), #66bb6a);">
                        <i class="fas fa-users"></i>
                        <div class="count"><?php echo $total_clients; ?></div>
                        <div class="label">Total Assisted Clients</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, var(--warning-color), #ffa726);">
                        <i class="fas fa-coins"></i>
                        <div class="count">₱<?php echo number_format($total_commission, 2); ?></div>
                        <div class="label">Total Commission Earned</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, var(--info-color), #42a5f5);">
                        <i class="fas fa-check-circle"></i>
                        <div class="count"><?php echo $approved_reservations; ?></div>
                        <div class="label">Approved Reservations</div>
                    </div>
                </div>
             
            </div>

            <div class="content-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-bold">Recent Activity</h5>
                    <div>
                        <div class="me-2 d-inline-block">
                            <label for="activityStartDate" class="form-label form-label-sm">Start Date:</label>
                            <input type="date" id="activityStartDate" class="form-control form-control-sm">
                        </div>
                        <div class="me-2 d-inline-block">
                            <label for="activityEndDate" class="form-label form-label-sm">End Date:</label>
                            <input type="date" id="activityEndDate" class="form-control form-control-sm">
                        </div>
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Action Taken</th>
                                <th>Lot Reserved</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_activities)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No recent activities found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_activities as $activity): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($activity['firstname'] . ' ' . $activity['lastname']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['action_taken']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['lot_number']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['reservation_date']); ?></td>
                                        <td>
                                            <span class="badge badge-action <?php echo $activity['status'] === 'Approved' ? 'badge-approved' : 'badge-submitted'; ?>">
                                                <?php echo htmlspecialchars($activity['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">Showing <?php echo count($recent_activities); ?> of <?php echo count($recent_activities); ?> activities</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const startDateInput = document.getElementById('activityStartDate');
        const endDateInput = document.getElementById('activityEndDate');
        const filterButton = document.querySelector('.content-card.mb-4 .d-flex button.btn-outline-secondary');
        const activityTableBody = document.querySelector('.table-responsive table tbody');
        const tableRows = activityTableBody.querySelectorAll('tr');

        filterButton.addEventListener('click', function () {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;

            tableRows.forEach(row => {
                const activityDate = row.cells[3].textContent; // Get the 'Date' column

                if ((!startDate || activityDate >= startDate) && (!endDate || activityDate <= endDate)) {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
            });
        });
    });
</script>
</body>
</html>