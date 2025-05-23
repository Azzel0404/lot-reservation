<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation-system/config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'AGENT') {
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
    $license = trim($_POST['license_number']);

    // Update user table
    $stmtUser = $conn->prepare("UPDATE user SET email = ?, phone = ?, address = ? WHERE user_id = ?");
    $stmtUser->bind_param("sssi", $email, $phone, $address, $user_id);
    $userUpdated = $stmtUser->execute();
    $stmtUser->close();

    // Update agent table
    $stmtAgent = $conn->prepare("UPDATE agent SET firstname = ?, middlename = ?, lastname = ?, license_number = ? WHERE user_id = ?");
    $stmtAgent->bind_param("ssssi", $firstname, $middlename, $lastname, $license, $user_id);
    $agentUpdated = $stmtAgent->execute();
    $stmtAgent->close();

    // Check if anything changed and show the appropriate message
    if ($userUpdated && $agentUpdated) {
        $success = "Profile updated successfully!";
    } else {
        $error = "No changes were made or failed to update profile.";
    }

    // Show profile form again after update attempt
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

    // Show password form again after password change attempt
    $show_edit_profile = false;
    $show_change_password = true;
}

// Fetch agent profile data
$stmt = $conn->prepare("SELECT u.email, u.phone, u.address, a.firstname, a.middlename, a.lastname, a.license_number
                        FROM user u JOIN agent a ON u.user_id = a.user_id WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email, $phone, $address, $firstname, $middlename, $lastname, $license);
$stmt->fetch();
$stmt->close();

// Show the respective forms based on button clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_profile'])) {
        $show_edit_profile = true;
        $show_change_password = false;
    } elseif (isset($_POST['change_password_button'])) {
        $show_edit_profile = false;
        $show_change_password = true;
    } elseif (isset($_POST['back_to_profile'])) {
        // Ensure we show the profile view again without redirecting
        $show_edit_profile = false;
        $show_change_password = false;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Agent Profile</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h2>My Profile (Agent)</h2>
    <a href="index.php">← Back to Dashboard</a>

    <!-- View Profile Section -->
    <?php if (!$show_edit_profile && !$show_change_password) : ?>
        <h3>Profile Details</h3>
        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($phone) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($address) ?></p>
        <p><strong>First Name:</strong> <?= htmlspecialchars($firstname) ?></p>
        <p><strong>Middle Name:</strong> <?= htmlspecialchars($middlename) ?></p>
        <p><strong>Last Name:</strong> <?= htmlspecialchars($lastname) ?></p>
        <p><strong>License Number:</strong> <?= htmlspecialchars($license) ?></p>

        <!-- Buttons to show Edit Profile or Change Password form -->
        <form method="POST">
            <button type="submit" name="edit_profile" style="margin-top: 20px;">Edit Profile</button>
            <button type="submit" name="change_password_button" style="margin-top: 20px;">Change Password</button>
        </form>
    <?php endif; ?>

    <!-- Profile Update Form -->
    <?php if ($show_edit_profile) : ?>
        <h3>Edit Profile</h3>
        <?php if ($success) : ?><p style="color: green;"><?= $success ?></p><?php endif; ?>
        <?php if ($error) : ?><p style="color: red;"><?= $error ?></p><?php endif; ?>

        <form method="POST" style="max-width: 400px;">
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required><br><br>

            <label>Phone:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" required><br><br>

            <label>Address:</label>
            <input type="text" name="address" value="<?= htmlspecialchars($address) ?>"><br><br>

            <label>First Name:</label>
            <input type="text" name="firstname" value="<?= htmlspecialchars($firstname) ?>" required><br><br>

            <label>Middle Name:</label>
            <input type="text" name="middlename" value="<?= htmlspecialchars($middlename) ?>"><br><br>

            <label>Last Name:</label>
            <input type="text" name="lastname" value="<?= htmlspecialchars($lastname) ?>" required><br><br>

            <label>License Number:</label>
            <input type="text" name="license_number" value="<?= htmlspecialchars($license) ?>" required><br><br>

            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <!-- Back Button to View Profile -->
        <form method="POST" style="margin-top: 20px;">
            <button type="submit" name="back_to_profile">Back to Profile</button>
        </form>
    <?php endif; ?>

    <!-- Change Password Form -->
    <?php if ($show_change_password) : ?>
        <h3>Change Password</h3>
        <?php if ($pw_success) : ?><p style="color: green;"><?= $pw_success ?></p><?php endif; ?>
        <?php if ($pw_error) : ?><p style="color: red;"><?= $pw_error ?></p><?php endif; ?>

        <form method="POST" style="max-width: 400px;">
            <label>Old Password:</label>
            <input type="password" name="old_password" required><br><br>

            <label>New Password:</label>
            <input type="password" name="new_password" required><br><br>

            <label>Confirm New Password:</label>
            <input type="password" name="confirm_password" required><br><br>

            <button type="submit" name="change_password">Change Password</button>
        </form>

        <!-- Back Button to View Profile -->
        <form method="POST" style="margin-top: 20px;">
            <button type="submit" name="back_to_profile">Back to Profile</button>
        </form>
    <?php endif; ?>

</body>
</html>
