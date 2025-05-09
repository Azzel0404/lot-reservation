
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
                     l.lot_number, l.size_meter_square, r.reservation_date, r.status, 
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

    <style>
        .badge-approved {
        background-color: #dbedda;
        color: #155724;
        }
        .modal-body .form-control[readonly] {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            cursor: default;
        }
        .modal-body .form-label {
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }
    </style>
    
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
                    <div class="d-flex align-items-center">
                        <div class="me-2">
                            <input type="text" id="clientListFilter" class="form-control form-control-sm" placeholder="Filter clients...">
                        </div>
                        <button class="btn btn-sm btn-outline-secondary">
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
                                            <button class="action-btn btn btn-sm btn-outline-primary me-1 view-client-btn"
                                                data-fullname="<?php echo htmlspecialchars($fullName); ?>"
                                                data-lot="<?php echo htmlspecialchars($client['lot_number'] ?? 'None'); ?>"
                                                data-size="<?php echo htmlspecialchars($client['size_meter_square'] ?? 'N/A'); ?>"
                                                data-phone="<?php echo htmlspecialchars($client['phone']); ?>"
                                                data-email="<?php echo htmlspecialchars($client['email']); ?>"
                                                data-status="<?php echo htmlspecialchars($client['status'] ?? 'No Reservation'); ?>"
                                                data-date="<?php echo $client['reservation_date'] ? date('Y-m-d', strtotime($client['reservation_date'])) : 'N/A'; ?>"
                                                data-address="<?php echo htmlspecialchars($client['address']); ?>">
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
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- View Client Modal -->
    <div class="modal fade" id="viewClientModal" tabindex="-1" aria-labelledby="viewClientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="viewClientModalLabel">Client Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                <label for="modalFullname" class="form-label fw-bold">Full Name</label>
                <input type="text" class="form-control" id="modalFullname" readonly>
                </div>
                <div class="row mb-3">
                <div class="col-md-6">
                    <label for="modalLotNumber" class="form-label fw-bold">Lot Number</label>
                    <input type="text" class="form-control" id="modalLotNumber" readonly>
                </div>
                <div class="col-md-6">
                    <label for="modalLotSize" class="form-label fw-bold">Size (m²)</label>
                    <input type="text" class="form-control" id="modalLotSize" readonly>
                </div>
                </div>
                <div class="row mb-3">
                <div class="col-md-6">
                    <label for="modalPhone" class="form-label fw-bold">Phone Number</label>
                    <input type="text" class="form-control" id="modalPhone" readonly>
                </div>
                <div class="col-md-6">
                    <label for="modalEmail" class="form-label fw-bold">Email</label>
                    <input type="email" class="form-control" id="modalEmail" readonly>
                </div>
                </div>
                <div class="row mb-3">
                <div class="col-md-6">
                    <label for="modalStatus" class="form-label fw-bold">Reservation Status</label>
                    <input type="text" class="form-control" id="modalStatus" readonly>
                </div>
                <div class="col-md-6">
                    <label for="modalDate" class="form-label fw-bold">Reservation Date</label>
                    <input type="text" class="form-control" id="modalDate" readonly>
                </div>
                </div>
                <div class="mb-3">
                <label for="modalAddress" class="form-label fw-bold">Address</label>
                <textarea class="form-control" id="modalAddress" rows="2" readonly></textarea>
                </div>
            </div>
            </div>
        </div>
    </div>
    
        <script>
            document.addEventListener('DOMContentLoaded', function () {
            const viewButtons = document.querySelectorAll('.view-client-btn');

            viewButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                // Set values to input fields instead of text content
                document.getElementById('modalFullname').value = this.dataset.fullname;
                document.getElementById('modalLotNumber').value = this.dataset.lot;
                document.getElementById('modalLotSize').value = this.dataset.size;
                document.getElementById('modalPhone').value = this.dataset.phone;
                document.getElementById('modalEmail').value = this.dataset.email;
                document.getElementById('modalStatus').value = this.dataset.status;
                document.getElementById('modalDate').value = this.dataset.date;
                document.getElementById('modalAddress').value = this.dataset.address;

                const modal = new bootstrap.Modal(document.getElementById('viewClientModal'));
                modal.show();
                });
            });
            });

            document.addEventListener('DOMContentLoaded', function () {
                const filterInput = document.getElementById('clientListFilter');
                const clientTable = document.querySelector('.table-responsive table tbody');
                const tableRows = clientTable.querySelectorAll('tr');

                filterInput.addEventListener('input', function () {
                    const filterValue = this.value.trim().toLowerCase();

                    tableRows.forEach(row => {
                        const clientName = row.cells[0].textContent.toLowerCase();
                        const lotReserved = row.cells[1].textContent.toLowerCase();
                        const status = row.cells[2].textContent.toLowerCase();

                        if (clientName.includes(filterValue) || lotReserved.includes(filterValue) || status.includes(filterValue)) {
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