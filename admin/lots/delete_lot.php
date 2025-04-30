<!--admin/lots/delete_lot.php-->
<?php
include('../../config/db.php');
session_start();

if (isset($_POST['delete']) && is_numeric($_POST['lot_id'])) {
    $lot_id = $_POST['lot_id'];

    // Step 1: Fetch image filenames
    $stmt = $conn->prepare("SELECT aerial_image, numbered_image FROM lot WHERE lot_id = ?");
    $stmt->bind_param("i", $lot_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        $aerialImage = $row['aerial_image'];
        $numberedImage = $row['numbered_image'];

        // Step 2: Delete image files
        $uploadDir = __DIR__ . '/uploads/';
        $aerialPath = $uploadDir . $aerialImage;
        $numberedPath = $uploadDir . $numberedImage;

        if (file_exists($aerialPath)) {
            unlink($aerialPath);
        }

        if (file_exists($numberedPath)) {
            unlink($numberedPath);
        }

        // Step 3: Delete the database record
        $stmt = $conn->prepare("DELETE FROM lot WHERE lot_id = ?");
        $stmt->bind_param("i", $lot_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Lot and associated images deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting lot from the database.";
        }

    } else {
        $_SESSION['error'] = "Lot not found.";
    }

    header("Location: lots.php");
    exit();
}
?>
