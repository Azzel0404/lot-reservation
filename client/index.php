<!--client/index.php-->
<?php
session_start();
ini_set('session.cookie_path', '/'); // optional but helps across directories
?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <a href="../logout.php" class="logout-button">Logout</a>

    <h1>Welcome, Client</h1>
    <ul>
        <li><a href="available_lots.php">Reserve a Lot</a></li>
        <li><a href="my_reservations.php">My Reservations</a></li>
        <li><a href="profile.php">Profile</a></li>
    </ul>
</body>
</html>

