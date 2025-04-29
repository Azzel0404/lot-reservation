<?php
include('../../config/db.php');

$id = $_GET['id'];

$sql = "DELETE FROM lot WHERE lot_id = '$id'";

if (mysqli_query($conn, $sql)) {
    header("Location: lots.php");
    exit();
} else {
    echo "Error deleting record: " . mysqli_error($conn);
}
?>
