<!--admin/lots/update_lot.php-->
<?php
include('../../config/db.php');

if (isset($_POST['update'])) {
    $lot_id = $_POST['lot_id'];
    $lot_number = $_POST['lot_number'];
    $location = $_POST['location'];
    $size = $_POST['size_meter_square'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    // Handle optional image uploads
    $aerial_image = $_FILES['aerial_image']['name'];
    $numbered_image = $_FILES['numbered_image']['name'];

    $update = "UPDATE lot SET 
        lot_number='$lot_number',
        location='$location',
        size_meter_square='$size',
        price='$price',
        status='$status'";

    if ($aerial_image) {
        move_uploaded_file($_FILES['aerial_image']['tmp_name'], 'uploads/' . $aerial_image);
        $update .= ", aerial_image='$aerial_image'";
    }
    if ($numbered_image) {
        move_uploaded_file($_FILES['numbered_image']['tmp_name'], 'uploads/' . $numbered_image);
        $update .= ", numbered_image='$numbered_image'";
    }

    $update .= " WHERE lot_id=$lot_id";

    if (mysqli_query($conn, $update)) {
        header("Location: lots.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
