<!-- filepath: c:\xampp\htdocs\lot-reservation\admin\lots.php -->

<?php include('../config/db.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Lots</title>
    <link rel="stylesheet" href="../admin/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include('sidebar.php'); ?>

        <main class="main-content">
            <header class="top-bar">
                <span>Admin</span>
                <i class="fas fa-user-cog"></i>
            </header>

            <!-- ✅ Message Feedback -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert success-alert"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert error-alert"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <!-- Add Lot Button -->
            <button id="addLotBtn">Add Lot</button>

            <!-- Modal -->
            <div id="addLotModal" style="display: none;">
                <div class="modal-content">
                    <span id="closeAddLotModal" class="close-btn">&times;</span>
                    <h3>Add New Lot</h3>
                    <form action="add_lot_action.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">

                        <label for="lot_number">Lot Number</label>
                        <input type="text" id="lot_number" name="lot_number" required>

                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" required>

                        <label for="size">Size (Square Meters)</label>
                        <input type="number" id="size" name="size" step="0.01" required>

                        <label for="price">Price</label>
                        <input type="number" id="price" name="price" step="0.01" required>

                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="Available">Available</option>
                            <option value="Reserved">Reserved</option>
                        </select>

                        <label for="aerial_image">Aerial Image</label>
                        <input type="file" id="aerial_image" name="aerial_image" accept="image/*" required>

                        <label for="numbered_image">Numbered Image</label>
                        <input type="file" id="numbered_image" name="numbered_image" accept="image/*" required>

                        <button type="submit">Add Lot</button>
                    </form>
                </div>
            </div>

            <!-- Lot Table -->
            <section class="lot-list-section">
                <h3>Existing Lots</h3>
                <table id="lotListTable">
                    <thead>
                        <tr>
                            <th>Lot Number</th>
                            <th>Location</th>
                            <th>Size (m²)</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Aerial</th>
                            <th>Numbered View</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM lot");
                        while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['lot_number']) ?></td>
                            <td><?= htmlspecialchars($row['location']) ?></td>
                            <td><?= $row['size_meter_square'] ?></td>
                            <td>₱<?= number_format($row['price'], 2) ?></td>
                            <td><?= $row['status'] ?></td>
                            <td><img src="../<?= $row['aerial_image'] ?>" width="100"></td>
                            <td><img src="../<?= $row['numbered_image'] ?>" width="100"></td>
                            <td><!-- Actions go here --></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- JS to Show/Hide Modal -->
    <script>
        document.getElementById('addLotBtn').onclick = function () {
            document.getElementById('addLotModal').style.display = 'block';
        };
        document.getElementById('closeAddLotModal').onclick = function () {
            document.getElementById('addLotModal').style.display = 'none';
        };
    </script>
</body>
</html>
