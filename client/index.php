<?php
session_start();

// Check if user is logged in and has the 'CLIENT' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'CLIENT') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <a href="../logout.php" class="logout-button">Logout</a>
</head>
<body>
    <h1>Welcome, Client</h1>
    <ul>
        <li><a href="available_lots.php">Reserve a Lot</a></li>
        <li><a href="my_reservations.php">My Reservations</a></li>
        <li><a href="profile.php">Profile</a></li>
    </ul>
</body>
</html>
