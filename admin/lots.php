<?php
session_start();
include '../config/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lot Batch Management</title>
    <link rel="stylesheet" href="../admin/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    <?php include('sidebar.php'); ?>

    <main class="main-content">
        <!-- Top Bar -->
        <header class="top-bar">
            <span>Admin</span>
            <i class="fas fa-user-cog"></i>
        </header>

        <div class="content-wrapper">
            <h1>Lot Batch Management</h1>

            <button class="btn btn-add" onclick="openAddModal()">+ Add Lot Batch</button>

            <br><br>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Batch Number</th>
                        <th>Location</th>
                        <th>Aerial Image</th>
                        <th>Numbered Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $lot_batches = mysqli_query($conn, "SELECT * FROM lot_batch ORDER BY created_at DESC");
                while ($batch = mysqli_fetch_assoc($lot_batches)):
                ?>
                    <tr>
                        <td><?= $batch['batch_id'] ?></td>
                        <td><?= htmlspecialchars($batch['batch_number']) ?></td>
                        <td><?= htmlspecialchars($batch['location']) ?></td>
                        <td>
                            <?php if ($batch['aerial_image']): ?>
                                <img src="../uploads/<?= $batch['aerial_image'] ?>" width="100">
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($batch['numbered_image']): ?>
                                <img src="../uploads/<?= $batch['numbered_image'] ?>" width="100">
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_lot_batch.php?id=<?= $batch['batch_id'] ?>" class="btn btn-edit">Edit</a>
                            <a href="delete_lot_batch.php?id=<?= $batch['batch_id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this batch?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Modal for Add Lot Batch -->
            <div id="addLotModal" class="modal" style="display:none;">
                <div class="modal-content">
                    <span style="float:right; cursor:pointer; font-size: 20px;" onclick="closeAddModal()">✖</span>
                    <h2>Add Lot Batch</h2>

                    <form action="add_lot_batch.php" method="POST" enctype="multipart/form-data">
                        <label for="batch_number">Batch Number:</label><br>
                        <input type="text" id="batch_number" name="batch_number" required><br><br>

                        <label for="location">Location:</label><br>
                        <input type="text" id="location" name="location" required><br><br>

                        <label for="aerial_image">Aerial Image:</label><br>
                        <input type="file" id="aerial_image" name="aerial_image" accept="image/*"><br><br>

                        <label for="numbered_image">Numbered Image:</label><br>
                        <input type="file" id="numbered_image" name="numbered_image" accept="image/*"><br><br>

                        <button type="submit" class="btn btn-save">Save</button>
                        <button type="button" class="btn btn-cancel" onclick="closeAddModal()">Cancel</button>
                    </form>
                </div>
            </div>
        </div> <!-- END Content Wrapper -->
    </main> <!-- END Main Content -->
</div> <!-- END Dashboard Container -->

<script>
function openAddModal() {
    document.getElementById('addLotModal').style.display = 'block';
}
function closeAddModal() {
    document.getElementById('addLotModal').style.display = 'none';
}
</script>

</body>
</html>
