<?php
include '../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: lots.php");
    exit;
}

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM lot_batch WHERE batch_id = $id");

if (mysqli_num_rows($result) == 0) {
    echo "Lot batch not found!";
    exit;
}

$batch = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch_number = mysqli_real_escape_string($conn, $_POST['batch_number']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);

    $update_query = "UPDATE lot_batch SET batch_number='$batch_number', location='$location' WHERE batch_id=$id";

    mysqli_query($conn, $update_query);

    echo "<script>alert('Lot Batch updated successfully!'); window.location.href='lots.php';</script>";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Lot Batch</title>
</head>
<body>
<h1>Edit Lot Batch</h1>

<form action="" method="POST">
    <label>Batch Number:</label><br>
    <input type="text" name="batch_number" value="<?= htmlspecialchars($batch['batch_number']) ?>" required><br><br>

    <label>Location:</label><br>
    <input type="text" name="location" value="<?= htmlspecialchars($batch['location']) ?>" required><br><br>

    <input type="submit" value="Update">
</form>

<br>
<a href="lots.php">← Back to Lot Batches</a>
</body>
</html>
