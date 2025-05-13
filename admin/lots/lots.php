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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <!-- Sidebar -->
    <aside class="sidebar">
        <h2 class="logo">Reservelt</h2>
        <ul class="sidebar-nav">
            <li><a href="/lot-reservation/admin/dashboard/index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="/lot-reservation/admin/reservation/reservations.php"><i class="fas fa-calendar-check"></i> Reservations</a></li>
            <li><a href="/lot-reservation/admin/lots/lots.php"><i class="fas fa-map"></i> Lots</a></li>
            <li><a href="/lot-reservation/admin/users/users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="/lot-reservation/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>


<div class="dashboard-container">
    <main class="main-content">
        

        <div class="content-wrapper">
            <div class="container">
                <h1>Lot Management</h1>
                <button class="btn-add" onclick="openAddModal()">Add New Lot</button>

                <div class="lot-grid">
                    <?php
                    $sql = "SELECT * FROM lot";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div class='lot-card' onclick='openLotDetails({$row['lot_id']})'>";
                        echo "<img src='uploads/{$row['aerial_image']}' class='lot-image'>";
                        echo "<div class='lot-info'>";
                        echo "<h3>{$row['lot_number']}</h3>";
                        echo "<p>{$row['location']}</p>";
                        echo "<p>₱" . number_format($row['price'], 2) . "</p>";
                        echo "</div></div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddModal()">&times;</span>
        <?php include('create_lot.php'); ?>
    </div>
</div>

<!-- Details Modal -->
<div id="lotDetailsModal" class="modal">
    <div class="modal-content" id="lotDetailsContent">
        <span class="close" onclick="closeLotDetailsModal()">&times;</span>
        <!-- Content will be loaded via JS -->
    </div>
</div>

<script>
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
