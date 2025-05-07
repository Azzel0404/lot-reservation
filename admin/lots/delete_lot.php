<?php
include('../../config/db.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only process POST requests with valid lot_id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && is_numeric($_POST['lot_id'])) {
    $lot_id = (int)$_POST['lot_id'];
    
    try {
        // Step 1: Fetch all data needed for deletion
        $stmt = $conn->prepare("SELECT status, aerial_image, numbered_image, pdf_file FROM lot WHERE lot_id = ?");
        $stmt->bind_param("i", $lot_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $status = $row['status'];
            $aerialImage = $row['aerial_image'];
            $numberedImage = $row['numbered_image'];
            $pdfFile = $row['pdf_file'];

            // Step 2: Check if the lot can be deleted
            if ($status === 'Reserved' || $status === 'Sold') {
                throw new Exception("Cannot delete this lot because it is currently $status.");
            }

            // Step 3: Prepare upload directory path
            $uploadDir = __DIR__ . '/uploads/';
            
            // Verify the upload directory exists and is writable
            if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
                throw new Exception("Upload directory is not accessible.");
            }

            // Step 4: Delete associated files if they exist
            $filesDeleted = 0;
            $filesToDelete = [
                $uploadDir . $aerialImage,
                $uploadDir . $numberedImage,
                $uploadDir . $pdfFile
            ];

            foreach ($filesToDelete as $filePath) {
                if ($filePath && file_exists($filePath) && is_file($filePath)) {
                    if (unlink($filePath)) {
                        $filesDeleted++;
                    }
                }
            }

            // Step 5: Delete the database record
            $stmt = $conn->prepare("DELETE FROM lot WHERE lot_id = ?");
            $stmt->bind_param("i", $lot_id);

            if ($stmt->execute()) {
                $response = [
                    'success' => true,
                    'message' => "Lot deleted successfully.",
                    'files_deleted' => $filesDeleted
                ];
                $_SESSION['success'] = $response['message'];
            } else {
                throw new Exception("Error deleting lot from database: " . $stmt->error);
            }

        } else {
            throw new Exception("Lot not found.");
        }

    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        $_SESSION['error'] = $response['message'];
    }

    // Return JSON response for AJAX requests
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Invalid request response
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => "Invalid request method or missing parameters."
]);
exit();
?>