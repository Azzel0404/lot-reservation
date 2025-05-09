<!--client/profile.php-->
<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'CLIENT') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';
$pw_success = '';
$pw_error = '';
$show_edit_profile = false;
$show_change_password = false;

// Fetch list of agents (assuming you have an 'agents' table)
$agent = [];
$stmt = $conn->prepare("SELECT agent_id, firstname, lastname FROM agent");
$stmt->execute();
$stmt->bind_result($agent_id, $firstname, $lastname);
while ($stmt->fetch()) {
    $agent[] = ['id' => $agent_id, 'name' => $firstname . ' ' . $lastname];
}
$stmt->close();

// Fetch current profile data
$stmt = $conn->prepare("SELECT u.email, u.phone, u.address, c.firstname, c.middlename, c.lastname, c.agent_id
                        FROM user u 
                        JOIN client c ON u.user_id = c.user_id WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email, $phone, $address, $firstname, $middlename, $lastname, $current_agent_id);
$stmt->fetch();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $firstname = trim($_POST['firstname']);
    $middlename = trim($_POST['middlename']);
    $lastname = trim($_POST['lastname']);
    $agent_id = $_POST['agent_id'] ? $_POST['agent_id'] : NULL;

    // Update user info
    $stmtUser = $conn->prepare("UPDATE user SET email = ?, phone = ?, address = ? WHERE user_id = ?");
    $stmtUser->bind_param("sssi", $email, $phone, $address, $user_id);
    $userUpdated = $stmtUser->execute();
    $stmtUser->close();

    // Update client info with agent_id
    $stmtClient = $conn->prepare("UPDATE client SET firstname = ?, middlename = ?, lastname = ?, agent_id = ? WHERE user_id = ?");
    $stmtClient->bind_param("ssssi", $firstname, $middlename, $lastname, $agent_id, $user_id);
    $clientUpdated = $stmtClient->execute();
    $stmtClient->close();

    if ($userUpdated && $clientUpdated) {
        $success = "Profile updated successfully!";
    } else {
        $error = "No changes were made or failed to update profile.";
    }

    $show_edit_profile = true;
    $show_change_password = false;
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM user WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($old_password, $hashed_password)) {
        if ($new_password === $confirm_password) {
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $new_hashed, $user_id);
            if ($stmt->execute()) {
                $pw_success = "Password changed successfully!";
            } else {
                $pw_error = "Failed to change password.";
            }
            $stmt->close();
        } else {
            $pw_error = "New passwords do not match.";
        }
    } else {
        $pw_error = "Old password is incorrect.";
    }

    $show_edit_profile = false;
    $show_change_password = true;
}

// Handle view switching
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_profile'])) {
        $show_edit_profile = true;
        $show_change_password = false;
    } elseif (isset($_POST['change_password_button'])) {
        $show_edit_profile = false;
        $show_change_password = true;
    } elseif (isset($_POST['back_to_profile'])) {
        $show_edit_profile = false;
        $show_change_password = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Profile</title>
    <link rel="stylesheet" href="../clients2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
        }
        
        body {
            background-color: #f8f9fc;
            padding-top: 70px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .profile-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .profile-detail {
            display: flex;
            margin-bottom: 10px;
        }
        
        .profile-label {
            font-weight: 500;
            color: #495057;
            width: 150px;
        }
        
        .profile-value {
            color: #212529;
            flex-grow: 1;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #495057;
        }
        
        .form-control {
            border-radius: 5px;
            padding: 8px 12px;
            margin-bottom: 15px;
        }
        
        .btn-action {
            padding: 8px 16px;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
        }
        
        .btn-primary:hover {
            background-color: var(--accent-color);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid px-4">
        <!-- Profile on the left -->
        <div class="d-flex align-items-center profile-left">
            <i class="fas fa-user me-2"></i>
            <span class="profile-text">User</span>
        </div>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Navigation links on the right -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item mx-2">
                    <a class="nav-link" href="../index.php">
                        <i class="fas fa-home me-1"></i> <span>Home</span>
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="available_lots.php">
                        <i class="fas fa-th me-1"></i> <span>Lots</span>
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="../reservations.php">
                        <i class="fas fa-calendar-check me-1"></i> <span>Reservations</span>
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="../profile/profile.php">
                        <i class="fas fa-user-circle"></i> <span>Profile</span>
                    </a>
                </li>
                <li class="nav-item ms-2">
                    <a href="../../logout.php" class="btn btn-sm btn-outline-light logout-btn">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </li>
                
            </ul>
        </div>
    </div>
</nav>

<div class="container profile-container">
    <h3 class="profile-header">Client Profile</h3>

    <!-- Success or error messages -->
    <?php if ($success) : ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error) : ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>
    
    <!-- Password change success -->
    <?php if ($pw_success) : ?>
        <div class="alert alert-success"><?= $pw_success; ?></div>
    <?php endif; ?>

    <!-- Password change error -->
    <?php if ($pw_error) : ?>
        <div class="alert alert-danger"><?= $pw_error; ?></div>
    <?php endif; ?>

    <!-- Profile section -->
    <div class="profile-section">
        <h4>Profile Information</h4>
        <div class="profile-detail">
            <div class="profile-label">Email:</div>
            <div class="profile-value"><?= htmlspecialchars($email); ?></div>
        </div>
        <div class="profile-detail">
            <div class="profile-label">Phone:</div>
            <div class="profile-value"><?= htmlspecialchars($phone); ?></div>
        </div>
        <div class="profile-detail">
            <div class="profile-label">Address:</div>
            <div class="profile-value"><?= htmlspecialchars($address); ?></div>
        </div>
        <div class="profile-detail">
            <div class="profile-label">Full Name:</div>
            <div class="profile-value"><?= htmlspecialchars($firstname . ' ' . $middlename . ' ' . $lastname); ?></div>
        </div>
        <div class="profile-detail">
            <div class="profile-label">Agent:</div>
            <div class="profile-value"><?= $current_agent_id ? 'Assigned' : 'No agent assigned'; ?></div>
        </div>
        <div class="btn-group">
            <form action="" method="post">
                <button type="submit" class="btn btn-primary" name="edit_profile">Edit Profile</button>
                <button type="submit" class="btn btn-secondary" name="change_password_button">Change Password</button>
            </form>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <?php if ($show_edit_profile): ?>
    <div class="profile-section">
        <h4>Edit Profile</h4>
        <form action="" method="post">
            <div class="form-group">
                <label for="firstname" class="form-label">First Name</label>
                <input type="text" name="firstname" id="firstname" class="form-control" value="<?= htmlspecialchars($firstname); ?>" required>
            </div>
            <div class="form-group">
                <label for="middlename" class="form-label">Middle Name</label>
                <input type="text" name="middlename" id="middlename" class="form-control" value="<?= htmlspecialchars($middlename); ?>">
            </div>
            <div class="form-group">
                <label for="lastname" class="form-label">Last Name</label>
                <input type="text" name="lastname" id="lastname" class="form-control" value="<?= htmlspecialchars($lastname); ?>" required>
            </div>
            <div class="form-group">
                <label for="agent_id" class="form-label">Agent</label>
                <select name="agent_id" id="agent_id" class="form-control">
                    <option value="">No Agent</option>
                    <?php foreach ($agent as $ag) : ?>
                        <option value="<?= $ag['id']; ?>" <?= $current_agent_id == $ag['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($ag['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" name="phone" id="phone" class="form-control" value="<?= htmlspecialchars($phone); ?>" required>
            </div>
            <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <textarea name="address" id="address" class="form-control" required><?= htmlspecialchars($address); ?></textarea>
            </div>
            <div class="btn-group">
                <button type="submit" class="btn btn-primary" name="update_profile">Save Changes</button>
                <button type="submit" class="btn btn-secondary" name="back_to_profile">Back to Profile</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Change Password Form -->
    <?php if ($show_change_password): ?>
    <div class="profile-section">
        <h4>Change Password</h4>
        <form action="" method="post">
            <div class="form-group">
                <label for="old_password" class="form-label">Old Password</label>
                <input type="password" name="old_password" id="old_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
            <div class="btn-group">
                <button type="submit" class="btn btn-primary" name="change_password">Change Password</button>
                <button type="submit" class="btn btn-secondary" name="back_to_profile">Back to Profile</button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
