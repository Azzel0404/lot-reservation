<!--admin/lots/delete_lot.php-->
<?php
include('../../config/db.php');

if (isset($_POST['delete'])) {
    $lot_id = $_POST['lot_id'];
    $sql = "DELETE FROM lot WHERE lot_id = $lot_id";

    if (mysqli_query($conn, $sql)) {
        header("Location: lots.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
