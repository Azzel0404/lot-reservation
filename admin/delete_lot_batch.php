<?php
include '../config/db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Delete lot batch
    mysqli_query($conn, "DELETE FROM lot_batch WHERE batch_id = $id");

    echo "<script>alert('Lot Batch deleted successfully!'); window.location.href='lots.php';</script>";
} else {
    header("Location: lots.php");
}
?>
