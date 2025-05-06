<!-- admin/lots/lots.php -->
<?php session_start(); ?>
<?php if (isset($_SESSION['success'])): ?>
    <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php include('../../config/db.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lot Management</title>
    <link rel="stylesheet" href="lots.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar p-4" style="width: 250px; height: 100vh;">
        <div class="sidebar-brand mb-4">ReserveIt</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="../dashboard/index.php" class="nav-link text-white">
                    <i class="fas fa-dashboard me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="../reservation/reservations.php" class="nav-link text-white">
                    <i class="fas fa-calendar-check me-2"></i> Reservations
                </a>
            </li>
            <li class="nav-item">
                <a href="../lots/lots.php" class="nav-link text-white">
                    <i class="fas fa-th me-2"></i> Lots
                </a>
            </li>
            <li class="nav-item">
                <a href="../users/users.php" class="nav-link text-white">
                    <i class="fas fa-users me-2"></i> Users
                </a>
            </li>
            <li class="nav-item mt-4">
                <a href="../../logout.php" class="nav-link text-white">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <div class="topbar d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Settings</h5>
            <div class="d-flex align-items-center">
                <span class="fw-medium me-3">Admin</span>
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>

        <div class="dashboard-container">
    <main class="main-content">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="content-wrapper">
            <div class="page-header">
                <h1 class="page-title">Lot Management</h1>
                <button class="btn-add" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add New Lot
                </button>
            </div>

            <div class="lot-grid">
                <?php
                $sql = "SELECT * FROM lot";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='lot-card' onclick='openLotDetails({$row['lot_id']})'>";
                    echo "<img src='uploads/{$row['aerial_image']}' class='lot-image' alt='Aerial view of lot {$row['lot_number']}'>";
                    echo "<div class='lot-info'>";
                    echo "<h3>{$row['lot_number']}</h3>";
                    echo "<p>{$row['location']}</p>";
                    echo "<p>₱" . number_format($row['price'], 2) . "</p>";
                    echo "</div></div>";
                }
                ?>
            </div>
        </div>
    </main>
</div>

<!-- Add Modal (unchanged) -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddModal()">&times;</span>
        <?php include('create_lot.php'); ?>
    </div>
</div>

<!-- Details Modal (unchanged) -->
<div id="lotDetailsModal" class="modal">
    <div class="modal-content" id="lotDetailsContent">
        <span class="close" onclick="closeLotDetailsModal()">&times;</span>
        <!-- Content will be loaded via JS -->
    </div>
</div>

<script>
// All JavaScript functions remain exactly the same
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}
function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}
function closeLotDetailsModal() {
    document.getElementById('lotDetailsModal').style.display = 'none';
}

function openLotDetails(id) {
    fetch('view_lot_form.php?id=' + id)
        .then(response => response.text())
        .then(html => {
            document.getElementById('lotDetailsContent').innerHTML = html;
            document.getElementById('lotDetailsModal').style.display = 'block';
        });
}

function deleteLot(lotId) {
    if (confirm("Are you sure you want to delete this lot? This action cannot be undone.")) {
        const formData = new FormData();
        formData.append('delete', '1');
        formData.append('lot_id', lotId);

        fetch('delete_lot.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(result => {
            alert(result);
            window.location.reload(); // Refresh to update the list
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Error deleting lot.");
        });
    }
}
</script>

</body>
</html>
