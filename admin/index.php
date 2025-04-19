<!--../admin/index.php-->
<?php
session_start();
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
