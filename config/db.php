<!--lot-reservation/config/db.php-->
<?php
$host = 'localhost';
$db = 'lot-reservation-system';
$user = 'root';
$pass = "";
$port = 3306;

// Establish the connection
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check for connection errors
if ($conn->connect_errno) {
    die("Failed to connect to MySQL: " . $conn->connect_error);
}
?>
