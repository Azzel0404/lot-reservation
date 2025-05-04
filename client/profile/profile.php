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

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $firstname = trim($_POST['firstname']);
    $middlename = trim($_POST['middlename']);
    $lastname = trim($_POST['lastname']);

    $stmtUser = $conn->prepare("UPDATE user SET email = ?, phone = ?, address = ? WHERE user_id = ?");
    $stmtUser->bind_param("sssi", $email, $phone, $address, $user_id);
    $userUpdated = $stmtUser->execute();
    $stmtUser->close();

    $stmtClient = $conn->prepare("UPDATE client SET firstname = ?, middlename = ?, lastname = ? WHERE user_id = ?");
    $stmtClient->bind_param("sssi", $firstname, $middlename, $lastname, $user_id);
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

// Fetch current profile
$stmt = $conn->prepare("SELECT u.email, u.phone, u.address, c.firstname, c.middlename, c.lastname
                        FROM user u JOIN client c ON u.user_id = c.user_id WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email, $phone, $address, $firstname, $middlename, $lastname);
$stmt->fetch();
$stmt->close();

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .profile-details {
            background: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .profile-details p {
            margin-bottom: 10px;
            font-size: 16px;
        }
        .profile-details strong {
            display: inline-block;
            width: 120px;
            color: #555;
        }
        .form-container {
            background: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
            outline: none;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background-color: #4a90e2;
            color: white;
        }
        .btn-primary:hover {
            background-color: #3a7bc8;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .alert {
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #4a90e2;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container profile-container">
        <div class="profile-header">
            <h2 class="mb-0">My Profile</h2>
            <a href="../index.php" class="back-link">← Back to Dashboard</a>
        </div>

        <!-- View Profile -->
        <?php if (!$show_edit_profile && !$show_change_password) : ?>
            <div class="profile-details">
                <h3 class="mb-4">Profile Details</h3>
                <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($phone) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($address) ?></p>
                <p><strong>First Name:</strong> <?= htmlspecialchars($firstname) ?></p>
                <p><strong>Middle Name:</strong> <?= htmlspecialchars($middlename) ?></p>
                <p><strong>Last Name:</strong> <?= htmlspecialchars($lastname) ?></p>
            </div>

            <form method="POST" class="btn-group">
                <button type="submit" name="edit_profile" class="btn btn-primary">Edit Profile</button>
                <button type="submit" name="change_password_button" class="btn btn-secondary">Change Password</button>
            </form>
        <?php endif; ?>

        <!-- Edit Profile Form -->
        <?php if ($show_edit_profile) : ?>
            <div class="form-container">
                <h3 class="mb-4">Edit Profile</h3>
                <?php if ($success) : ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                <?php if ($error) : ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address:</label>
                        <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($address) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="firstname">First Name:</label>
                        <input type="text" class="form-control" id="firstname" name="firstname" value="<?= htmlspecialchars($firstname) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="middlename">Middle Name:</label>
                        <input type="text" class="form-control" id="middlename" name="middlename" value="<?= htmlspecialchars($middlename) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="lastname">Last Name:</label>
                        <input type="text" class="form-control" id="lastname" name="lastname" value="<?= htmlspecialchars($lastname) ?>" required>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        <button type="submit" name="back_to_profile" class="btn btn-secondary">Cancel</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Change Password Form -->
        <?php if ($show_change_password) : ?>
            <div class="form-container">
                <h3 class="mb-4">Change Password</h3>
                <?php if ($pw_success) : ?><div class="alert alert-success"><?= $pw_success ?></div><?php endif; ?>
                <?php if ($pw_error) : ?><div class="alert alert-danger"><?= $pw_error ?></div><?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="old_password">Old Password:</label>
                        <input type="password" class="form-control" id="old_password" name="old_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                        <button type="submit" name="back_to_profile" class="btn btn-secondary">Cancel</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
