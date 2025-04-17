<?php
session_start();

// Check if user is logged in and has the 'AGENT' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'AGENT') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Agent Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <a href="../logout.php" class="logout-button">Logout</a>
</head>
<body>
    <h1>Welcome, Agent</h1>
    <ul>
        <li><a href="my_clients.php">My Clients</a></li>
        <li><a href="lot_list.php">Available Lots</a></li>
        <li><a href="commissions.php">View Commission</a></li>
    </ul>
</body>
</html>
