<!--/config/db.php-->
<?php
$host = 'localhost';
$db = 'lot_reservation';
$user = 'root';
$pass = '';

// Establish the connection
$conn = new mysqli($host, $user, $pass, $db);

// Check for connection errors
if ($conn->connect_errno) {
    die("Failed to connect to MySQL: " . $conn->connect_error);
}
?>
