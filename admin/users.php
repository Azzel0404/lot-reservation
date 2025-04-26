<?php
session_start();
include('../config/db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
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

        <section class="user-management">
            <!-- CLIENTS TABLE -->
            <h3 class="section-heading">Clients</h3>
            <table>
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = "
                    SELECT u.email, c.firstname, c.lastname, c.middlename 
                    FROM user u
                    JOIN client c ON u.user_id = c.user_id
                ";
                $result = $conn->query($query);
                while ($client = $result->fetch_assoc()):
                    $fullName = $client['firstname'] . ' ' . ($client['middlename'] ? $client['middlename'] . ' ' : '') . $client['lastname'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($fullName) ?></td>
                        <td><?= htmlspecialchars($client['email']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

            <!-- AGENTS TABLE -->
            <h3 class="section-heading">Agents</h3>
            <table>
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>License #</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = "
                    SELECT u.email, a.firstname, a.lastname, a.middlename, a.license_number 
                    FROM user u
                    JOIN agent a ON u.user_id = a.user_id
                ";
                $result = $conn->query($query);
                while ($agent = $result->fetch_assoc()):
                    $fullName = $agent['firstname'] . ' ' . ($agent['middlename'] ? $agent['middlename'] . ' ' : '') . $agent['lastname'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($fullName) ?></td>
                        <td><?= htmlspecialchars($agent['email']) ?></td>
                        <td><?= htmlspecialchars($agent['license_number']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>
