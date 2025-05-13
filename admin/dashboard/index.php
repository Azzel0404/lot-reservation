<?php
session_start();
include('../../config/db.php');

// Get filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$agent_filter = isset($_GET['agent_filter']) ? $_GET['agent_filter'] : 'all';
$time_filter = isset($_GET['time_filter']) ? $_GET['time_filter'] : 'all';
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Base WHERE clauses
$reservation_where = "1=1";
$user_where = "1=1";
$lot_where = "1=1";
$agent_commission_where = "1=1";

// Apply time filters
if ($time_filter === 'monthly') {
    $reservation_where .= " AND MONTH(reservation_date) = '$selected_month' AND YEAR(reservation_date) = '$selected_year'";
    $agent_commission_where .= " AND MONTH(commission_date) = '$selected_month' AND YEAR(commission_date) = '$selected_year'";
} elseif ($time_filter === 'yearly') {
    $reservation_where .= " AND YEAR(reservation_date) = '$selected_year'";
    $agent_commission_where .= " AND YEAR(commission_date) = '$selected_year'";
}

// Initialize all variables with default values
$total_reservations = 0;
$approved_reservations = 0;
$total_users = 0;
$total_clients = 0;
$total_agents = 0;
$available_lots = 0;
$reserved_lots = 0;
$total_lots = 0;
$clients_with_reservation = [];
$agents_with_commission = [];

// Fetch reservation counts
$total_reservations_query = "SELECT COUNT(*) AS total FROM reservation WHERE $reservation_where";
$total_reservations_result = mysqli_query($conn, $total_reservations_query);
if ($total_reservations_result) {
    $total_reservations = mysqli_fetch_assoc($total_reservations_result)['total'];
} else {
    echo "Error in total_reservations_query: " . mysqli_error($conn);
}

$approved_reservations_query = "SELECT COUNT(*) AS approved FROM reservation WHERE status = 'Approved' AND $reservation_where";
$approved_reservations_result = mysqli_query($conn, $approved_reservations_query);
if ($approved_reservations_result) {
    $approved_reservations = mysqli_fetch_assoc($approved_reservations_result)['approved'];
} else {
    echo "Error in approved_reservations_query: " . mysqli_error($conn);
}

// Fetch user counts
$total_users_query = "SELECT COUNT(*) AS total_users FROM user WHERE role IN ('CLIENT', 'AGENT') AND $user_where";
$total_users_result = mysqli_query($conn, $total_users_query);
if ($total_users_result) {
    $total_users = mysqli_fetch_assoc($total_users_result)['total_users'];
} else {
    echo "Error in total_users_query: " . mysqli_error($conn);
}

$total_clients_query = "SELECT COUNT(*) AS total_clients FROM user WHERE role = 'CLIENT' AND $user_where";
$total_clients_result = mysqli_query($conn, $total_clients_query);
if ($total_clients_result) {
    $total_clients = mysqli_fetch_assoc($total_clients_result)['total_clients'];
} else {
    echo "Error in total_clients_query: " . mysqli_error($conn);
}

$total_agents_query = "SELECT COUNT(*) AS total_agents FROM user WHERE role = 'AGENT' AND $user_where";
$total_agents_result = mysqli_query($conn, $total_agents_query);
if ($total_agents_result) {
    $total_agents = mysqli_fetch_assoc($total_agents_result)['total_agents'];
} else {
    echo "Error in total_agents_query: " . mysqli_error($conn);
}

// Fetch lot status
$lot_status_query = "SELECT 
                    SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) AS available,
                    SUM(CASE WHEN status = 'Reserved' THEN 1 ELSE 0 END) AS reserved
                    FROM lot WHERE $lot_where";
$lot_status_result = mysqli_query($conn, $lot_status_query);
if ($lot_status_result) {
    $lot_status = mysqli_fetch_assoc($lot_status_result);
    $available_lots = $lot_status['available'] ?? 0;
    $reserved_lots = $lot_status['reserved'] ?? 0;
    $total_lots = $available_lots + $reserved_lots;
} else {
    echo "Error in lot_status_query: " . mysqli_error($conn);
}

// Fetch clients with reservation info with filtering
$clients_with_reservation_query = "
    SELECT 
        c.client_id,
        CONCAT(c.firstname, ' ', c.middlename, ' ', c.lastname) AS client_name,
        COUNT(r.reservation_id) AS total_reservations,
        GROUP_CONCAT(l.lot_number SEPARATOR ', ') AS lot_numbers,
        MAX(r.status) AS status
    FROM reservation r
    JOIN client c ON r.client_id = c.client_id
    JOIN lot l ON r.lot_id = l.lot_id
    WHERE $reservation_where
";

if ($filter === 'active') {
    $clients_with_reservation_query .= " AND r.status = 'Approved'";
} elseif ($filter === 'pending') {
    $clients_with_reservation_query .= " AND r.status = 'Pending'";
}

$clients_with_reservation_query .= " GROUP BY c.client_id";
$clients_with_reservation_result = mysqli_query($conn, $clients_with_reservation_query);
if ($clients_with_reservation_result) {
    while ($row = mysqli_fetch_assoc($clients_with_reservation_result)) {
        $clients_with_reservation[] = $row;
    }
} else {
    echo "Error in clients_with_reservation_query: " . mysqli_error($conn);
}

// Fetch number of agents with commissions with filtering
$agents_with_commission_query = "
    SELECT a.firstname, a.lastname, a.license_number, SUM(ac.commission_fee) AS total_commission
    FROM agent_commission ac
    JOIN agent a ON ac.agent_id = a.agent_id
    WHERE $agent_commission_where
";

if ($agent_filter === 'high') {
    $agents_with_commission_query .= " GROUP BY a.agent_id HAVING total_commission > 1000";
} elseif ($agent_filter === 'low') {
    $agents_with_commission_query .= " GROUP BY a.agent_id HAVING total_commission <= 1000";
} else {
    $agents_with_commission_query .= " GROUP BY a.agent_id";
}

$agents_with_commission_result = mysqli_query($conn, $agents_with_commission_query);
if ($agents_with_commission_result) {
    while ($row = mysqli_fetch_assoc($agents_with_commission_result)) {
        $agents_with_commission[] = $row;
    }
} else {
    echo "Error in agents_with_commission_query: " . mysqli_error($conn);
}

// Handle Excel export for summary report only
if (isset($_GET['export_summary'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="summary_report_'.date('Y-m-d').'.xls"');
    
    $output = fopen('php://output', 'w');
    
    // Export summary data
    $time_period = "All Time";
    if ($time_filter === 'monthly') {
        $time_period = date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year));
    } elseif ($time_filter === 'yearly') {
        $time_period = "Year $selected_year";
    }
    
    fputcsv($output, ['Summary Report - '.$time_period], "\t");
    fputcsv($output, [], "\t");
    fputcsv($output, ['Metric', 'Value'], "\t");
    fputcsv($output, ['Total Reservations', $total_reservations], "\t");
    fputcsv($output, ['Approved Reservations', $approved_reservations], "\t");
    fputcsv($output, ['Pending Reservations', $total_reservations - $approved_reservations], "\t");
    fputcsv($output, ['Total Users', $total_users], "\t");
    fputcsv($output, ['Total Agents', $total_agents], "\t");
    fputcsv($output, ['Total Clients', $total_clients], "\t");
    fputcsv($output, ['Total Lots', $total_lots], "\t");
    fputcsv($output, ['Available Lots', $available_lots], "\t");
    fputcsv($output, ['Reserved Lots', $reserved_lots], "\t");
    
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Reporting System</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <style>
        @media print {
            .sidebar, .top-bar, .client-reservation-details, .agent-commission-details, .donut-chart, .print-hide {
                display: none !important;
            }
            
            .layout-wrapper {
                padding-left: 0 !important;
                margin-top: 0 !important;
            }
            
            .summary-reports {
                display: block !important;
                page-break-inside: avoid;
            }
            
            .content-area {
                padding: 20px !important;
            }
            
            body {
                background: white !important;
                color: black !important;
            }
            
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
        }
        
        /* Additional styles for time filter */
        .time-filter {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 20px;
            background: #f8f9fc;
            padding: 15px;
            border-radius: 8px;
        }
        
        .time-filter label {
            font-weight: 600;
            color: #5a5c69;
        }
        
        .time-filter select, .time-filter input {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #d1d3e2;
            background-color: #fff;
            font-size: 14px;
            color: #5a5c69;
        }
        
        .time-filter .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .time-filter button {
            background-color: #4e73df;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .time-filter button:hover {
            background-color: #2e59d9;
        }
        
        .hidden-fields {
            display: none;
        }
    </style>
</head>
<body>

<div class="layout-wrapper">
    <?php include('../sidebar.php'); ?>

    <div class="content-area">
        <header class="top-bar">
            <span>Admin Dashboard</span>
            <div class="header-actions">
                <i class="fas fa-user-cog"></i>
            </div>
        </header>

        <div class="content-wrapper">
            <!-- Time Filter Section -->
            <form method="get" class="time-filter print-hide">
                <div class="filter-group">
                    <label for="time_filter">Time Period:</label>
                    <select name="time_filter" id="time_filter" onchange="toggleTimeFields()">
                        <option value="all" <?php echo $time_filter === 'all' ? 'selected' : ''; ?>>All Time</option>
                        <option value="monthly" <?php echo $time_filter === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                        <option value="yearly" <?php echo $time_filter === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                    </select>
                </div>
                
                <div class="filter-group" id="month_field" style="<?php echo $time_filter === 'monthly' ? '' : 'display: none;' ?>">
                    <label for="month">Month:</label>
                    <select name="month" id="month">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo sprintf('%02d', $i); ?>" <?php echo $selected_month == sprintf('%02d', $i) ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="filter-group" id="year_field" style="<?php echo in_array($time_filter, ['monthly', 'yearly']) ? '' : 'display: none;' ?>">
                    <label for="year">Year:</label>
                    <select name="year" id="year">
                        <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo $selected_year == $i ? 'selected' : ''; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <button type="submit">Apply Filter</button>
                
                <!-- Hidden fields to preserve other filters -->
                <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                <input type="hidden" name="agent_filter" value="<?php echo $agent_filter; ?>">
            </form>

            <section class="dashboard-metrics print-hide">
                <div class="card blue">
                    <h3><?php echo $total_reservations; ?></h3>
                    <p>Total Reservations</p>
                    <div class="card-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="card green">
                    <h3><?php echo $approved_reservations; ?></h3>
                    <p>Approved Reservations</p>
                    <div class="card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="card purple">
                    <h3><?php echo $total_lots; ?></h3>
                    <p>Total Lots</p>
                    <div class="card-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                </div>
                <div class="card yellow">
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="card orange">
                    <h3><?php echo $total_agents; ?></h3>
                    <p>Total Agents</p>
                    <div class="card-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
                <div class="card red">
                    <h3><?php echo $total_clients; ?></h3>
                    <p>Total Clients</p>
                    <div class="card-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                </div>
            </section>

            <section class="summary-reports">
                <div class="report-header">
                    <h3>Summary Reports - 
                        <?php 
                            if ($time_filter === 'monthly') {
                                echo date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year));
                            } elseif ($time_filter === 'yearly') {
                                echo "Year $selected_year";
                            } else {
                                echo "All Time";
                            }
                        ?>
                    </h3>
                    <div class="report-actions">
                        <a href="?export_summary=1&time_filter=<?php echo $time_filter; ?>&month=<?php echo $selected_month; ?>&year=<?php echo $selected_year; ?>" class="export-btn">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </a>
                        <button class="print-btn" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </div>
                <div class="card">
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Total</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Reservations</td>
                                <td><?php echo $total_reservations; ?></td>
                                <td>
                                    Approved: <?php echo $approved_reservations; ?>,
                                    Pending: <?php echo $total_reservations - $approved_reservations; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Users</td>
                                <td><?php echo $total_users; ?></td>
                                <td>
                                    Agents: <?php echo $total_agents; ?>,
                                    Clients: <?php echo $total_clients; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Lots</td>
                                <td><?php echo $total_lots; ?></td>
                                <td>
                                    Available: <?php echo $available_lots; ?>,
                                    Reserved: <?php echo $reserved_lots; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="donut-chart print-hide">
                <div class="card donut">
                    <div class="chart-container">
                        <canvas id="lotChart" height="300"></canvas>
                    </div>
                    <p class="chart-description">Lot Status Distribution</p>
                </div>
                <div class="card donut">
                    <div class="chart-container">
                        <canvas id="userChart" height="300"></canvas>
                    </div>
                    <p class="chart-description">User Type Distribution</p>
                </div>
            </section>

            <section class="client-reservation-details print-hide">
                <div class="section-header">
                    <h3>Clients with Reservations</h3>
                    <div class="filter-controls">
                        <form method="get" class="filter-form">
                            <label for="filter">Filter:</label>
                            <select name="filter" id="filter" onchange="this.form.submit()">
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Reservations</option>
                                <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>>Approved Only</option>
                                <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pending Only</option>
                            </select>
                            <!-- Hidden fields to preserve time filter -->
                            <input type="hidden" name="time_filter" value="<?php echo $time_filter; ?>">
                            <input type="hidden" name="month" value="<?php echo $selected_month; ?>">
                            <input type="hidden" name="year" value="<?php echo $selected_year; ?>">
                            <input type="hidden" name="agent_filter" value="<?php echo $agent_filter; ?>">
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table id="clientTable" class="display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Client Name</th>
                                    <th>Total Reservations</th>
                                    <th>Status</th>
                                    <th>Reserved Lot Numbers</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($clients_with_reservation)): ?>
                                    <?php foreach ($clients_with_reservation as $client): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($client['client_name']); ?></td>
                                            <td><?php echo $client['total_reservations']; ?></td>
                                            <td><?php echo $client['status']; ?></td>
                                            <td><?php echo htmlspecialchars($client['lot_numbers']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">No reservation data available.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="agent-commission-details print-hide">
                <div class="section-header">
                    <h3>Agents with Commission</h3>
                    <div class="filter-controls">
                        <form method="get" class="filter-form">
                            <label for="agent_filter">Filter:</label>
                            <select name="agent_filter" id="agent_filter" onchange="this.form.submit()">
                                <option value="all" <?php echo $agent_filter === 'all' ? 'selected' : ''; ?>>All Agents</option>
                                <option value="high" <?php echo $agent_filter === 'high' ? 'selected' : ''; ?>>High Commission (>1000)</option>
                                <option value="low" <?php echo $agent_filter === 'low' ? 'selected' : ''; ?>>Low Commission (≤1000)</option>
                            </select>
                            <!-- Hidden fields to preserve time filter -->
                            <input type="hidden" name="time_filter" value="<?php echo $time_filter; ?>">
                            <input type="hidden" name="month" value="<?php echo $selected_month; ?>">
                            <input type="hidden" name="year" value="<?php echo $selected_year; ?>">
                            <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table id="agentTable" class="display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Agent Name</th>
                                    <th>License Number</th>
                                    <th>Total Commission</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agents_with_commission as $agent): ?>
                                    <tr>
                                        <td><?php echo $agent['firstname'] . ' ' . $agent['lastname']; ?></td>
                                        <td><?php echo $agent['license_number']; ?></td>
                                        <td><?php echo number_format($agent['total_commission'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script>
// Initialize DataTables with export buttons
$(document).ready(function() {
    $('#clientTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        responsive: true,
        scrollX: true,
        fixedHeader: true
    });
    
    $('#agentTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        responsive: true,
        scrollX: true,
        order: [[2, 'desc']],
        fixedHeader: true
    });
});

// Chart for Lots - Fixed to prevent movement
let lotChart;
function initLotChart() {
    const ctx = document.getElementById('lotChart').getContext('2d');
    lotChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Available (<?php echo $available_lots; ?>)', 'Reserved (<?php echo $reserved_lots; ?>)'],
            datasets: [{
                data: [<?php echo $available_lots; ?>, <?php echo $reserved_lots; ?>],
                backgroundColor: ['#4e73df', '#1cc88a'],
                hoverBackgroundColor: ['#2e59d9', '#17a673'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 14
                        },
                        boxWidth: 20,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                    callbacks: {
                        label: function(context) {
                            var label = context.label || '';
                            var value = context.raw || 0;
                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                            var percentage = Math.round((value / total) * 100);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '70%',
        }
    });
}

// Chart for Users - Fixed to prevent movement
let userChart;
function initUserChart() {
    const userCtx = document.getElementById('userChart').getContext('2d');
    userChart = new Chart(userCtx, {
        type: 'doughnut',
        data: {
            labels: ['Clients (<?php echo $total_clients; ?>)', 'Agents (<?php echo $total_agents; ?>)'],
            datasets: [{
                data: [<?php echo $total_clients; ?>, <?php echo $total_agents; ?>],
                backgroundColor: ['#f6c23e', '#e74a3b'],
                hoverBackgroundColor: ['#dda20a', '#be2617'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 14
                        },
                        boxWidth: 20,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                    callbacks: {
                        label: function(context) {
                            var label = context.label || '';
                            var value = context.raw || 0;
                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                            var percentage = Math.round((value / total) * 100);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '70%',
        }
    });
}

// Initialize charts when page loads
$(document).ready(function() {
    initLotChart();
    initUserChart();
});

// Redraw charts when window is resized
$(window).resize(function() {
    if (lotChart) {
        lotChart.destroy();
        initLotChart();
    }
    if (userChart) {
        userChart.destroy();
        initUserChart();
    }
});

// Toggle time filter fields based on selection
function toggleTimeFields() {
    const timeFilter = document.getElementById('time_filter').value;
    const monthField = document.getElementById('month_field');
    const yearField = document.getElementById('year_field');
    
    if (timeFilter === 'monthly') {
        monthField.style.display = 'flex';
        yearField.style.display = 'flex';
    } else if (timeFilter === 'yearly') {
        monthField.style.display = 'none';
        yearField.style.display = 'flex';
    } else {
        monthField.style.display = 'none';
        yearField.style.display = 'none';
    }
}
</script>

</body>
</html>
