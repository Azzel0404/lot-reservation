<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Resenwelt - Commissions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --dark-color: #1a1b41;
            --light-color: #f8f9fa;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --info-color: #2196f3;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--dark-color), var(--secondary-color));
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .sidebar-brand {
            font-weight: 700;
            color: white;
            letter-spacing: 1px;
            font-size: 1.3rem;
            padding: 1rem 0;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .nav-link {
            border-radius: 6px;
            margin-bottom: 4px;
            padding: 10px 12px;
            transition: all 0.2s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            transform: translateX(3px);
        }
        
        .topbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 1rem 1.5rem;
        }
        
        .content-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            background-color: white;
            padding: 1.5rem;
        }
        
        .table th {
            font-weight: 600;
            color: var(--dark-color);
            border-top: none;
            background-color: #f8f9fa;
        }
        
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-expired {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .commission-value {
            font-weight: 600;
            color: var(--success-color);
        }
        
        .received-badge {
            background-color: #e6f7ee;
            color: #0d6832;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .summary-card {
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        
        .total-commission {
            background: linear-gradient(135deg, #4caf50, #66bb6a);
            color: white;
        }
        
        .pending-commission {
            background: linear-gradient(135deg, #ff9800, #ffa726);
            color: white;
        }
    </style>
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
                <a href="clients.php" class="nav-link text-white">
                    <i class="fas fa-user-friends me-2"></i> Clients
                </a>
            </li>
            <li class="nav-item">
                <a href="commissions.php" class="nav-link text-white active">
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
            <h5 class="mb-0 fw-bold">Commissions</h5>
            <div class="d-flex align-items-center">
                <span class="fw-medium me-3">Welcome, Agent</span>
                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>

        <!-- Commissions Content -->
        <div class="p-4">
            <!-- Commission Summary -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="summary-card total-commission">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Total Commission</h6>
                                <h3 class="mb-0">₱45,000</h3>
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
                                <h3 class="mb-0">₱12,000</h3>
                            </div>
                            <i class="fas fa-clock fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commissions Table -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-bold">Commission Details</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-download me-1"></i> Export
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
                                <th>Commission Rate</th>
                                <th>Earned Commission</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>John Doe</td>
                                <td>Lot 5</td>
                                <td class="commission-value">₱30,000</td>
                                <td>5%</td>
                                <td class="commission-value">₱1,500</td>
                            </tr>
                            <tr>
                                <td>Jane Smith</td>
                                <td>Lot 8</td>
                                <td class="commission-value">₱25,000</td>
                                <td>5%</td>
                                <td class="commission-value">₱1,250</td>
                            </tr>
                            <tr>
                                <td>Robert Lee</td>
                                <td>Lot 2</td>
                                <td class="commission-value">₱15,000</td>
                                <td>5%</td>
                                <td class="commission-value">₱750</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">Showing 3 of 15 commissions</div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>