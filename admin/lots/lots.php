<!-- admin/lots/lots.php -->
<!-- admin/lots/lots.php -->
<?php
session_start();
include('../../config/db.php'); // Ensure correct path for DB connection

// Fetch data after ensuring the connection is open
$maps = mysqli_query($conn, "SELECT * FROM map ORDER BY created_at DESC");
if (!$maps) {
    die("Error executing query: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lot Map Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../lots/lots.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="layout-wrapper">
    <?php include('../sidebar.php'); ?>

    <div class="content-area">
        <div class="top-bar">
            <span>Admin</span>
            <i class="fas fa-user-cog"></i>
        </div>

        <div class="container py-4">
            <h2 class="mb-4">Lot Map Management</h2>

            <button class="btn btn-add mb-4" onclick="openAddModal()">+ Add Lot Map</button>

            <div class="row g-4">
                <?php while ($map = mysqli_fetch_assoc($maps)): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card-hover h-100">
                        <div class="map-img">
                            <img src="../admin/images/<?= htmlspecialchars(basename($map['map_layout'])) ?>" alt="Map Image">
                        </div>
                        <div class="map-info">
                            <div class="map-title"><?= htmlspecialchars($map['map_number']) ?></div>
                            <div class="map-subtext"><?= htmlspecialchars($map['location']) ?></div>
                            <a href="#" class="btn btn-primary btn-sm mt-3" onclick="openMapModal(event, <?= $map['map_id'] ?>)">View Details</a>
                            <a href="#" class="btn btn-warning btn-sm mt-3" onclick="openEditModal(<?= $map['map_id'] ?>)">Edit</a>
                            <a href="#" class="btn btn-danger btn-sm mt-3" onclick="deleteMap(<?= $map['map_id'] ?>)">Delete</a>
                        </div>
                    </div>
                </div>

                <!-- Modal -->
                <div id="mapModal<?= $map['map_id'] ?>" class="modal">
                    <div class="modal-content">
                        <span class="close" onclick="closeMapModal(<?= $map['map_id'] ?>)">✖</span>
                        <div id="mapDetails<?= $map['map_id'] ?>"></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Lot Map Modal -->
<div id="addLotModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddModal()">✖</span>
        <h2>Add New Lot Map</h2>
        <form action="add_map.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="map_number">Map Number:</label>
                <input type="text" id="map_number" name="map_number" required>
            </div>
            <div class="mb-3">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required>
            </div>
            <div class="mb-3">
                <label for="map_layout">Map Layout:</label>
                <input type="file" id="map_layout" name="map_layout" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Map</button>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openMapModal(event, id) {
    event.preventDefault();  // Prevents the default anchor click behavior
    fetch(`admin/lots/get_map_data.php?map_id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Error fetching map details: ' + data.error);
            } else {
                const mapDetails = `
                    <h2>${data.map_number}</h2>
                    <p><strong>Location:</strong> ${data.location}</p>
                    <p><strong>Map ID:</strong> ${data.map_id}</p>
                    <img src="../admin/images/${data.map_layout}" alt="Map Image" style="width: 100%; height: auto;">
                `;
                document.getElementById(`mapDetails${id}`).innerHTML = mapDetails;
                document.getElementById(`mapModal${id}`).style.display = 'flex';
            }
        })
        .catch(error => {
            alert('Error fetching map details: ' + error.message);
        });
}

function closeMapModal(id) {
    document.getElementById(`mapModal${id}`).style.display = 'none';
}

function openAddModal() {
    document.getElementById('addLotModal').style.display = 'flex';
}

function closeAddModal() {
    document.getElementById('addLotModal').style.display = 'none';
}

function deleteMap(mapId) {
    if (confirm('Are you sure you want to delete this lot map?')) {
        window.location.href = 'delete_map.php?map_id=' + mapId;
    }
}
</script>

</body>
</html>

<?php
mysqli_close($conn);  // Close the connection after all queries are done
?>
