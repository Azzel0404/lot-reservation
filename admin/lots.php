<!-- filepath: c:\xampp\htdocs\lot-reservation\admin\lots.php -->

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

            <!-- Alerts -->
            <div id="addLotSuccess" class="alert success-alert" style="display: none;">Lot added successfully!</div>
            <div id="addLotError" class="alert error-alert" style="display: none;">Error adding lot.</div>

            <!-- Button -->
            <button id="addLotBtn">Add Lot</button>

            <!-- Add Lot Modal -->
            <div id="addLotModal" style="display: none;">
                <div class="modal-content">
                    <span id="closeAddLotModal" class="close-btn">&times;</span>

                    <h3>Add New Lot</h3>

                    <form id="addLotForm" enctype="multipart/form-data" method="POST">
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
                        <!-- Will be loaded dynamically -->
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Show the Add Lot Modal
        $('#addLotBtn').on('click', function() {
            $('#addLotModal').show();
        });

        // Close the Add Lot Modal
        $('#closeAddLotModal').on('click', function() {
            $('#addLotModal').hide();
        });

        // Load the list of lots dynamically
        function loadLotList() {
            $.ajax({
                url: 'load_lots.php',
                type: 'GET',
                success: function(response) {
                    $('#lotListTable tbody').html(response);
                }
            });
        }

        // Handle Add Lot Form Submission
        $('#addLotForm').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            $.ajax({
                url: 'add_lot_action.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log("Raw Response:", response); // Debug the raw response
                    try {
                        response = JSON.parse(response); // Parse the JSON response
                        if (response.status === 'success') {
                            $('#addLotSuccess').fadeIn().delay(2000).fadeOut();
                            $('#addLotModal').hide();
                            $('#addLotForm')[0].reset();
                            loadLotList();
                        } else {
                            $('#addLotError').text(response.message).fadeIn().delay(3000).fadeOut();
                        }
                    } catch (e) {
                        console.error("Error parsing response:", e);
                        $('#addLotError').text("An unexpected error occurred.").fadeIn().delay(3000).fadeOut();
                    }
                },
                error: function() {
                    $('#addLotError').text("AJAX request failed.").fadeIn().delay(3000).fadeOut();
                }
            });
        });

        // Load the lot list on page load
        $(document).ready(function() {
            loadLotList();
        });
    </script>
</body>
</html>
