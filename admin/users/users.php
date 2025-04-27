<!-- admin/users/users.php -->
<?php
session_start();

// Ensure db.php exists and is included
include('../../config/db.php');

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <!-- Link to CSS for users page -->
    <link rel="stylesheet" href="../users/users.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    <?php include('../sidebar.php'); ?>

    <main class="main-content">
        <!-- Top Bar -->
        <header class="top-bar">
            <span>Admin</span>
            <i class="fas fa-user-cog"></i>
        </header>

        <!-- Wrapped Content -->
        <div class="content-wrapper">
            <section class="user-management">
                <!-- CLIENTS TABLE (First) -->
                <div class="table-section">
                    <h3 class="section-heading">Clients</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Query to get client data
                        $query = "
                            SELECT u.email, c.firstname, c.lastname, c.middlename 
                            FROM user u
                            JOIN client c ON u.user_id = c.user_id
                        ";
                        $result = $conn->query($query);

                        if ($result) {
                            while ($client = $result->fetch_assoc()):
                                $fullName = $client['firstname'] . ' ' . ($client['middlename'] ? $client['middlename'] . ' ' : '') . $client['lastname'];
                        ?>
                                <tr>
                                    <td><?= htmlspecialchars($fullName) ?></td>
                                    <td><?= htmlspecialchars($client['email']) ?></td>
                                </tr>
                        <?php
                            endwhile;
                        } else {
                            echo "<tr><td colspan='2'>No clients found.</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>

                <!-- AGENTS TABLE (Below Clients) -->
                <div class="table-section">
                    <h3 class="section-heading">Agents</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>License #</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Query to get agent data
                        $query = "
                            SELECT u.email, a.firstname, a.lastname, a.middlename, a.license_number 
                            FROM user u
                            JOIN agent a ON u.user_id = a.user_id
                        ";
                        $result = $conn->query($query);

                        if ($result) {
                            while ($agent = $result->fetch_assoc()):
                                $fullName = $agent['firstname'] . ' ' . ($agent['middlename'] ? $agent['middlename'] . ' ' : '') . $agent['lastname'];
                        ?>
                                <tr>
                                    <td><?= htmlspecialchars($fullName) ?></td>
                                    <td><?= htmlspecialchars($agent['email']) ?></td>
                                    <td><?= htmlspecialchars($agent['license_number']) ?></td>
                                </tr>
                        <?php
                            endwhile;
                        } else {
                            echo "<tr><td colspan='3'>No agents found.</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>

</body>
</html>
