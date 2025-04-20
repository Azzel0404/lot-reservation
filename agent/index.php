<?php
session_start();
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
        <li><a href="profile.php">My Profile</a></li> <!-- Link to the profile page -->
    </ul>
</body>
</html>
