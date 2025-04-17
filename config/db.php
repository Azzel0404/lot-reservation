<?php
$host = 'localhost';
$db = 'lot_reservation';
$user = 'root';
$pass = '';

// Establish the connection
$conn = new mysqli($host, $username, $password, $database);

// Check for connection errors
if ($conn->connect_errno) {
    die("Failed to connect to MySQL: " . $conn->connect_error);
}
?>