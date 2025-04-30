<!--admin/lots/edit_lot.php-->
<?php
include('../../config/db.php');

$id = $_GET['id'];

if (isset($_POST['update'])) {
    $lot_number = $_POST['lot_number'];
    $location = $_POST['location'];
    $size = $_POST['size_meter_square'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $aerial_image = $_FILES['aerial_image']['name'];
    $numbered_image = $_FILES['numbered_image']['name'];

    if ($aerial_image) {
        move_uploaded_file($_FILES['aerial_image']['tmp_name'], 'uploads/' . $aerial_image);
        $aerial_update = ", aerial_image='$aerial_image'";
    } else {
        $aerial_update = "";
    }

    if ($numbered_image) {
        move_uploaded_file($_FILES['numbered_image']['tmp_name'], 'uploads/' . $numbered_image);
        $numbered_update = ", numbered_image='$numbered_image'";
    } else {
        $numbered_update = "";
    }

    $sql = "UPDATE lot SET 
                lot_number='$lot_number', 
                location='$location', 
                size_meter_square='$size', 
                price='$price', 
                status='$status'
                $aerial_update
                $numbered_update
            WHERE lot_id='$id'";

    if (mysqli_query($conn, $sql)) {
        header("Location: lots.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}

// Fetch lot details
$sql = "SELECT * FROM lot WHERE lot_id = '$id'";
$result = mysqli_query($conn, $sql);
$lot = mysqli_fetch_assoc($result);
?>

<form action="" method="POST" enctype="multipart/form-data">
    <h2>Edit Lot</h2>
    <input type="text" name="lot_number" value="<?php echo $lot['lot_number']; ?>" required>
    <input type="text" name="location" value="<?php echo $lot['location']; ?>" required>
    <input type="number" step="0.01" name="size_meter_square" value="<?php echo $lot['size_meter_square']; ?>" required>
    <input type="number" step="0.01" name="price" value="<?php echo $lot['price']; ?>" required>
    <select name="status" required>
        <option value="Available" <?php if ($lot['status'] == 'Available') echo 'selected'; ?>>Available</option>
        <option value="Reserved" <?php if ($lot['status'] == 'Reserved') echo 'selected'; ?>>Reserved</option>
    </select>
    <label>Change Aerial Image</label>
    <input type="file" name="aerial_image">
    <label>Change Numbered Image</label>
    <input type="file" name="numbered_image">
    <button type="submit" name="update">Update Lot</button>
</form>
