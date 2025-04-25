<?php
session_start();
include('../config/db.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lot_number = $_POST['lot_number'];
    $location = $_POST['location'];
    $size = $_POST['size'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $aerial_path = '';
    $numbered_path = '';

    // Handle aerial image upload
    if (!empty($_FILES['aerial_image']['name'])) {
        $aerial_filename = time() . '_aerial_' . basename($_FILES['aerial_image']['name']);
        $aerial_path = '../admin/images/' . $aerial_filename; // Updated path
        move_uploaded_file($_FILES['aerial_image']['tmp_name'], $aerial_path);
    }

    // Handle numbered image upload
    if (!empty($_FILES['numbered_image']['name'])) {
        $numbered_filename = time() . '_numbered_' . basename($_FILES['numbered_image']['name']);
        $numbered_path = '../admin/images/' . $numbered_filename; // Updated path
        move_uploaded_file($_FILES['numbered_image']['tmp_name'], $numbered_path);
    }

    // Prepare database paths (store relative paths)
    $aerial_db_path = !empty($aerial_path) ? 'admin/images/' . basename($aerial_path) : null; // Updated path
    $numbered_db_path = !empty($numbered_path) ? 'admin/images/' . basename($numbered_path) : null; // Updated path

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO lot (lot_number, location, size_meter_square, price, status, aerial_image, numbered_image)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddsss", $lot_number, $location, $size, $price, $status, $aerial_db_path, $numbered_db_path);

    if ($stmt->execute()) {
        $_SESSION['lot_success'] = "Lot added successfully!";
        header("Location: lots.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Lots</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .lot-form-section .card {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.07);
        }

        .lot-form label {
            font-weight: bold;
            margin-top: 1rem;
            display: block;
            color: #333;
        }

        .lot-form input[type="text"],
        .lot-form input[type="number"],
        .lot-form select,
        .lot-form input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .lot-form button {
            margin-top: 20px;
            background-color: #007BFF;
            border: none;
            color: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .lot-form button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            margin-top: 2rem;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.75rem;
            border: 1px solid #ddd;
        }

        th {
            background: #eee;
        }

        img.thumbnail {
            width: 100px;
            height: auto;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .success-alert {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>

<script>
    document.getElementById('aerial_image').addEventListener('change', function () {
        const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
        const aerialInput = document.getElementById('aerial_name');
        aerialInput.value = fileName; // Set the file name in the input field
        aerialInput.style.color = fileName === 'No file chosen' ? '#b0b0b0' : 'black';  // Change color based on file name
    });

    document.getElementById('numbered_image').addEventListener('change', function () {
        const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
        const numberedInput = document.getElementById('numbered_name');
        numberedInput.value = fileName; // Set the file name in the input field
        numberedInput.style.color = fileName === 'No file chosen' ? '#b0b0b0' : 'black';  // Change color based on file name
    });
</script>

<body>
<div class="dashboard-container">
    <?php include('sidebar.php'); ?>

    <main class="main-content">
        <?php if (isset($_SESSION['lot_success'])): ?>
            <div class="alert success-alert">
                <?= $_SESSION['lot_success']; ?>
            </div>
            <?php unset($_SESSION['lot_success']); ?>
        <?php endif; ?>

        <section class="lot-form-section">
            <div class="card">
                <h3>Add New Lot</h3>
                <form class="lot-form" method="POST" enctype="multipart/form-data">
                    <label>Lot Number</label>
                    <input type="text" name="lot_number" required>

                    <label>Location</label>
                    <input type="text" name="location" required>

                    <label>Size (m²)</label>
                    <input type="number" step="0.01" name="size" required>

                    <label>Price</label>
                    <input type="number" step="0.01" name="price" required>

                    <label>Status</label>
                    <select name="status">
                        <option value="Available">Available</option>
                        <option value="Reserved">Reserved</option>
                    </select>

                    <label>Aerial Image</label>
                    <div class="file-upload">
                        <input type="file" name="aerial_image" id="aerial_image" accept="image/*">
                    </div>

                    <label>Numbered View Image</label>
                    <div class="file-upload">
                        <input type="file" name="numbered_image" id="numbered_image" accept="image/*">
                    </div>
                    <button type="submit">Add Lot</button>
                </form>
            </div>
        </section>

        <section class="lot-list-section">
            <h3>Existing Lots</h3>
            <table>
                <thead>
                    <tr>
                        <th>Lot Number</th>
                        <th>Location</th>
                        <th>Size (m²)</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Aerial</th>
                        <th>Numbered View</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $result = $conn->query("SELECT * FROM lot ORDER BY lot_id DESC");
                while ($lot = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($lot['lot_number']) ?></td>
                        <td><?= htmlspecialchars($lot['location']) ?></td>
                        <td><?= $lot['size_meter_square'] ?></td>
                        <td>₱<?= number_format($lot['price'], 2) ?></td>
                        <td><?= $lot['status'] ?></td>
                        <td>
                            <?php if (!empty($lot['aerial_image'])): ?>
                                <img src="../<?= htmlspecialchars($lot['aerial_image']); ?>" class="thumbnail" alt="Aerial">
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($lot['numbered_image'])): ?>
                                <img src="../<?= htmlspecialchars($lot['numbered_image']); ?>" class="thumbnail" alt="Numbered View">
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>
