<?php
// settings.php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReserveIt - Settings</title>
    <link rel="stylesheet" href="../agent/agent.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background-color: #2c3e50;
            color: white;
            min-height: 100vh;
            position: fixed;
            left: 0;
        }
        
        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.7);
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            font-weight: 500;
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        /* Topbar Styles */
        .topbar {
            height: 70px;
            padding: 0 30px;
            background-color: white;
            border-bottom: 1px solid #e9ecef;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            background-color: #3498db;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Content Area Styles */
        .content-area {
            padding-top: 20px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            border-bottom: 1px solid #e9ecef;
            background-color: white;
            padding: 15px 20px;
        }
        
        .card-body {
            padding: 25px;
        }
        
        /* Form Styles */
        .form-control {
            border-radius: 5px;
            padding: 10px 15px;
            border: 1px solid #ced4da;
        }
        
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        /* Tab Styles */
        .nav-tabs .nav-link {
            color: #495057;
            border: none;
            padding: 10px 20px;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            color: #3498db;
            border-bottom: 2px solid #3498db;
            background-color: transparent;
        }
        
        .nav-tabs .nav-link:hover:not(.active) {
            border-bottom: 2px solid #e9ecef;
        }
    </style>
</head>
<body class="d-flex">
    
<!-- Sidebar -->
<div class="sidebar p-4" style="width: 250px; height: 100vh;">
        <div class="sidebar-brand mb-4">ReserveIt</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="../dashboard/index.php" class="nav-link text-white">
                    <i class="fas fa-dashboard me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="../reservation/reservations.php" class="nav-link text-white">
                    <i class="fas fa-calendar-check me-2"></i> Reservations
                </a>
            </li>
            <li class="nav-item">
                <a href="../lots/lots.php" class="nav-link text-white">
                    <i class="fas fa-th me-2"></i> Lots
                </a>
            </li>
            <li class="nav-item">
                <a href="../users/users.php" class="nav-link text-white">
                    <i class="fas fa-users me-2"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link text-white">
                    <i class="fas fa-cog me-2"></i> Settings
                </a>
            </li>
            <li class="nav-item mt-4">
                <a href="../../logout.php" class="nav-link text-white">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main content -->
    <div class="flex-grow-1 ms-250" style="margin-left: 250px;">
        <!-- Topbar -->
        <div class="topbar d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Settings</h5>
            <div class="d-flex align-items-center">
                <span class="fw-medium me-3">Admin</span>
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="content-area p-4">
            <!-- Settings Navigation Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link active" href="#account-settings">
                        <i class="fas fa-user-cog me-2"></i>Account Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#system-settings">
                        <i class="fas fa-sliders-h me-2"></i>System Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#security-settings">
                        <i class="fas fa-shield-alt me-2"></i>Security Settings
                    </a>
                </li>
            </ul>

            <!-- Account Settings Panel -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Account Information</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" >
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" >
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" >
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" >
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="mb-3">
                            <label for="avatar" class="form-label">Profile Picture</label>
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 64px; height: 64px;">
                                    <i class="fas fa-user fa-2x"></i>
                                </div>
                                <button type="button" class="btn btn-outline-primary">
                                    <i class="fas fa-upload me-2"></i>Upload New Image
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-outline-secondary me-md-2">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>