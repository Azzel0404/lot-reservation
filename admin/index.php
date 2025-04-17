<?php
session_start();

// Check if user is logged in and has the 'ADMIN' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
    <a href="../logout.php" class="logout-button">Logout</a>
</head>
<body>
    <h1>Welcome, Admin</h1>
    <ul>
        <li><a href="manage_users.php">Manage Users</a></li>
        <li><a href="manage_lots.php">Manage Lots</a></li>
        <li><a href="view_reservations.php">View Reservations</a></li>
    </ul>
</body>
</html>
