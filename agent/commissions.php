<?php
session_start();

// Check if the user is logged in as an agent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'AGENT') {
    header("Location: ../login.php"); // Redirect to login if not an agent
    exit();
}

// Include database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';

// Get the current agent's ID
$agent_user_id = $_SESSION['user_id'];

try {
    // Fetch the agent's information to display the name
    $agent_query = "SELECT firstname, lastname FROM agent WHERE user_id = ?";
    $stmt = $conn->prepare($agent_query);
    $stmt->bind_param("i", $agent_user_id);
    $stmt->execute();
    $agent_result = $stmt->get_result();
    if ($agent_row = $agent_result->fetch_assoc()) {
        $agent_firstname = htmlspecialchars($agent_row['firstname']);
        $agent_lastname = htmlspecialchars($agent_row['lastname']);
    } else {
        // Handle the case where agent info isn't found (shouldn't happen if logged in as agent)
        $agent_firstname = "Agent";
        $agent_lastname = "";
    }
    $stmt->close();

    // Fetch commission details for the current agent
    $commission_query = "SELECT
                            c.firstname AS client_firstname,
                            c.lastname AS client_lastname,
                            l.lot_number,
                            r.reservation_fee,
                            ac.commission_fee
                        FROM agent_commission ac
                        JOIN reservation r ON ac.reservation_id = r.reservation_id
                        JOIN client c ON r.client_id = c.client_id
                        JOIN lot l ON r.lot_id = l.lot_id
                        JOIN agent a ON ac.agent_id = a.agent_id
                        WHERE a.user_id = ?";
    $stmt = $conn->prepare($commission_query);
    $stmt->bind_param("i", $agent_user_id);
    $stmt->execute();
    $commissions_result = $stmt->get_result();
    $commissions = $commissions_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Calculate total commission
    $total_commission = 0;
    foreach ($commissions as $commission) {
        $total_commission += $commission['commission_fee'];
    }

    // For simplicity in this example, we'll consider all calculated commissions as "pending"
    $pending_commission = $total_commission; // You might have a specific status in a real application

} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
    $commissions = [];
    $total_commission = 0;
    $pending_commission = 0;
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>ReserveIt - Commissions</title>
    <link rel="stylesheet" href="../agent/agent.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="d-flex">

    <div class="sidebar p-4" style="width: 250px; height: 100vh; background-color: #343a40;">
        <div class="sidebar-brand text-white">ReserveIt</div>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a href="index.php" class="nav-link text-white">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="clients.php" class="nav-link text-white">
                    <i class="fas fa-user-friends me-2"></i> Clients
                </a>
            </li>
            <li class="nav-item">
                <a href="commissions.php" class="nav-link text-white active" style="background-color: rgba(255, 255, 255, 0.1);">
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

    <div class="flex-grow-1 bg-light">
        <div class="topbar d-flex justify-content-between align-items-center p-3 bg-white shadow-sm">
            <h5 class="mb-0 fw-bold">Commissions</h5>
            <div class="d-flex align-items-center">
                <!-- <span class="fw-medium me-3">Welcome, <?php echo $agent_firstname . ' ' . $agent_lastname; ?></span>-->
                <span class="fw-medium me-3">Welcome, <?php echo htmlspecialchars($_SESSION['firstname'] ?? 'Agent'); ?></span>
                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>

        <div class="p-4">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="summary-card total-commission">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Total Commission</h6>
                                <h3 class="mb-0">₱<?php echo number_format($total_commission, 2); ?></h3>
                            </div>
                            <i class="fas fa-coins fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="summary-card pending-commission">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Pending Commission</h6>
                                <h3 class="mb-0">₱<?php echo number_format($pending_commission, 2); ?></h3>
                            </div>
                            <i class="fas fa-clock fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-bold">Commission Details</h5>
                    <div>
                        <div class="me-2 d-inline-block">
                            <input type="text" id="commissionListFilter" class="form-control form-control-sm" placeholder="Filter commissions...">
                        </div>
                        <button class="btn btn-sm btn-outline-secondary ms-2">
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
                                <th>Reservation Fee</th>
                                <th>Earned Commission</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($commissions)): ?>
                                <tr><td colspan="4" class="text-center">No commissions found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($commissions as $commission): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($commission['client_firstname'] . ' ' . $commission['client_lastname']); ?></td>
                                        <td><?php echo htmlspecialchars($commission['lot_number']); ?></td>
                                        <td class="commission-value">₱<?php echo number_format($commission['reservation_fee'], 2); ?></td>
                                        <td class="commission-value">₱<?php echo number_format($commission['commission_fee'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">Showing <?php echo count($commissions); ?> commissions</div>
                    </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterInput = document.getElementById('commissionListFilter');
        const commissionTableBody = document.querySelector('.table-responsive table tbody');
        const tableRows = commissionTableBody.querySelectorAll('tr');

        filterInput.addEventListener('input', function () {
            const filterValue = this.value.trim().toLowerCase();

            tableRows.forEach(row => {
                const clientName = row.cells[0].textContent.toLowerCase();
                const lotReserved = row.cells[1].textContent.toLowerCase();

                if (clientName.includes(filterValue) || lotReserved.includes(filterValue)) {
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